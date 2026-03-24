#!/usr/bin/env python3
"""
sync_conversations.py
Fetches chronological delta conversations from ChatGPT API.

Usage:
  python3 sync_conversations.py <known_uuids.json> <outbox_dir>
"""

import json
import os
import sys
import time
from pathlib import Path
from curl_cffi import requests

# We share the token with the bridge to ensure it only needs to be bootstrapped once system-wide
BRIDGE_ENV = Path("/home/craigpar/Code/ChatGptToChatProjectsBridge/.env")
from dotenv import load_dotenv
load_dotenv(BRIDGE_ENV)

BASE_URL = "https://chatgpt.com"

def get_session_token() -> str:
    token = os.getenv("CHATGPT_SESSION_TOKEN", "")
    if not token:
        print("ERROR: CHATGPT_SESSION_TOKEN not found in bridge .env", file=sys.stderr)
        sys.exit(1)
    return token

def make_session() -> tuple[requests.Session, str]:
    session = requests.Session(impersonate="chrome")
    session.cookies.set("__Secure-next-auth.session-token", get_session_token(), domain="chatgpt.com")
    resp = session.get(f"{BASE_URL}/api/auth/session", timeout=15)
    resp.raise_for_status()
    jwt = resp.json().get("accessToken")
    if not jwt:
        print("ERROR: Could not get accessToken from /api/auth/session", file=sys.stderr)
        sys.exit(1)
    return session, jwt

def auth_headers(jwt: str) -> dict:
    return {
        "Authorization": f"Bearer {jwt}",
        "oai-device-id": os.getenv("CHATGPT_DEVICE_ID", "default-dev-id"),
        "oai-language": "en-US",
        "User-Agent": "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36",
    }

def main():
    if len(sys.argv) < 3:
        print("Usage: sync_conversations.py <known_uuids.json> <outbox_dir>")
        sys.exit(1)

    known_uuids_file = Path(sys.argv[1])
    outbox_dir = Path(sys.argv[2])

    known_uuids = set(json.loads(known_uuids_file.read_text())) if known_uuids_file.exists() else set()
    outbox_dir.mkdir(parents=True, exist_ok=True)

    session, jwt = make_session()
    print("Authenticated successfully.")

    offset = 0
    limit = 50
    downloaded_count = 0

    while True:
        print(f"Fetching pagination offset {offset}...")
        url = f"{BASE_URL}/backend-api/conversations?offset={offset}&limit={limit}&order=updated"
        resp = session.get(url, headers=auth_headers(jwt), timeout=15)
        resp.raise_for_status()
        
        items = resp.json().get("items", [])
        if not items:
            break

        reached_known = False
        
        for item in items:
            conv_id = item["id"]
            if conv_id in known_uuids:
                print(f"Hit known conversation {conv_id}, stopping delta sync.")
                reached_known = True
                break

            print(f"Downloading new conversation: {conv_id} - {item.get('title', 'Unknown')}")
            
            # Fetch full conversation details
            detail_url = f"{BASE_URL}/backend-api/conversation/{conv_id}"
            detail_resp = session.get(detail_url, headers=auth_headers(jwt), timeout=15)
            detail_resp.raise_for_status()
            full_conv = detail_resp.json()
            
            # Save it
            conv_dir = outbox_dir / conv_id
            conv_dir.mkdir(parents=True, exist_ok=True)
            (conv_dir / "conversation.json").write_text(json.dumps([full_conv], indent=2))
            
            downloaded_count += 1
            time.sleep(1) # rate limit

        if reached_known or len(items) < limit:
            break
            
        offset += limit
        time.sleep(1)

    print(f"Done. Downloaded {downloaded_count} new conversations.")

if __name__ == "__main__":
    main()
