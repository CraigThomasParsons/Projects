#!/usr/bin/env bash
set -e

mkdir -p ~/.config/systemd/user
ln -sf ~/Code/ChatProjects/systemd/projects.service ~/.config/systemd/user/
ln -sf ~/Code/ChatProjects/systemd/projects-migrate.path ~/.config/systemd/user/
ln -sf ~/Code/ChatProjects/systemd/projects-migrate.service ~/.config/systemd/user/
ln -sf ~/Code/ChatProjects/systemd/projects-composer.path ~/.config/systemd/user/
ln -sf ~/Code/ChatProjects/systemd/projects-composer.service ~/.config/systemd/user/

systemctl --user daemon-reload
systemctl --user enable --now projects.service
systemctl --user enable --now projects-migrate.path
systemctl --user enable --now projects-composer.path
