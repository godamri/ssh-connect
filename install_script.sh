#!/bin/bash

git clone https://github.com/godamri/ssh-connect.git 
cd ./ssh-connect
rm -f install_script.sh
rm -rf .git
composer install
chmod +x ./minicli
sudo ln -sf $(pwd)/minicli /usr/local/bin/connect

