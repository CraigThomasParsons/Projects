How this should be working:


When I go to localhost:80 I see
Index of /
Name Lastmodified Size Description

## This is apache and this correct.

- a reverse proxy that forwards requests to different backends based on the URL path.
- So if I were to now go to game.elasticgun.com it should forward to http://localhost:8382
- So if I were to go to projects.elasticgun.com it should forward to http://localhost:8182

## After this works I want to see in the index of localhost:80
Name Url Port Description
Game game.elasticgun.com 8382 The game server
Projects projects.elasticgun.com 8182 The project management server

- If I go to game.elasticgun.com I should see the game server
- If I go to projects.elasticgun.com I should see the project management server

## HTTPS setup (local certs)
- Use mkcert to generate local certs for the domains and install the local CA.
- Cert files live in /etc/nginx/certs and are referenced by the Apache 443 vhosts.
- Example (run once):
    - `sudo mkcert -install`
    - `sudo mkcert -cert-file /etc/nginx/certs/game.elasticgun.com.crt -key-file /etc/nginx/certs/game.elasticgun.com.key game.elasticgun.com`
    - `sudo mkcert -cert-file /etc/nginx/certs/projects.elasticgun.com.crt -key-file /etc/nginx/certs/projects.elasticgun.com.key projects.elasticgun.com`

## Troubleshooting
- Make sure apache is running: `sudo systemctl status apache2` (or `httpd` on CentOS)
- Check Apache error logs for issues: `sudo tail -f /var/log/apache2/error.log` (or `/var/log/httpd/error_log` on CentOS)
- Ensure DNS for game.elasticgun.com and projects.elasticgun.com points to your server's IP.
- For local testing, add both hostnames to /etc/hosts pointing at 127.0.0.1.
- Verify that the backend services (nginx on ports 8382 and 8182) are running and accessible.
- Check firewall settings to ensure ports 80, 8382, and 8182 are open.
- Test connectivity to backend services using curl:
    - `curl -I http://localhost:8382`
    - `curl -I http://localhost:8182`

- Ensure Apache modules for proxying are enabled:
    - `sudo a2enmod proxy`
    - `sudo a2enmod proxy_http`
    - `sudo a2enmod ssl`
    - `sudo a2enmod headers`
    - Restart Apache after enabling modules: `sudo systemctl restart apache2`
- Double-check the virtual host configuration files for any syntax errors:
    - Test Apache configuration: `sudo apachectl configtest`
    - Look for typos or misconfigurations in the vhost files.

- If you can, check the Laravel logs (if using Laravel) for any application-level errors.