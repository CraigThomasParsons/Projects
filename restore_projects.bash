#!/bin/bash
# 1. Update Apache vhosts
sed -i 's/127.0.0.1:8182/127.0.0.1:8083/g' /etc/httpd/conf/extra/httpd-vhosts.conf
systemctl reload httpd

# 2. Create Systemd Service
cat <<EOF > /etc/systemd/system/projects-docker.service
[Unit]
Description=Projects Docker Compose Service
Requires=docker.service
After=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/home/craigpar/Projects
ExecStart=/usr/bin/docker compose up -d
ExecStop=/usr/bin/docker compose down
Restart=on-failure

[Install]
WantedBy=multi-user.target
EOF

# 3. Enable and Start Service
systemctl daemon-reload
systemctl enable projects-docker.service
systemctl start projects-docker.service

echo "Restoration complete! Visit http://projects.elasticgun.com"
