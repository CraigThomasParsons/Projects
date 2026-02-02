# Apache reverse proxy configs

These virtual host files terminate TLS in Apache and proxy traffic to nginx on localhost.

## Files

- game.elasticgun.com.conf -> http://127.0.0.1:8687/
- projects.elasticgun.com.conf -> http://127.0.0.1:8182/

## Notes

- Enable Apache modules: proxy, proxy_http, ssl, headers.
- Adjust certificate paths to your local files.
- Ensure nginx is listening on the target ports.
- Restart Apache after adding vhost files.