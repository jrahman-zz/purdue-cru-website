#!/bin/bash

echo "Uploading stylesheet..."
scp -r ~/Dropbox/CRU_Website/wp-theme/PurdueCRU/style.css rahmanj@ftp.ics.purdue.edu:www/wp-content/themes/PurdueCru
sudo cp -r ~/Dropbox/CRU_Website/wp-theme/PurdueCRU /var/www/wp-content/themes

