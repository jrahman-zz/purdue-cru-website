#!/bin/bash

echo "Uploading stylesheet..."
scp -r ~/Dropbox/CRU_Website/wp-theme/style.css rahmanj@ftp.ics.purdue.edu:www/wp-content/themes/PurdueCRU
sudo cp -r ~/Dropbox/CRU_Website/wp-theme/style.css /var/www/wp-content/themes/PurdueCRU

