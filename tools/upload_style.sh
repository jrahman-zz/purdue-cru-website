#!/bin/bash

echo "Uploading stylesheet..."
scp ~/Dropbox/CRU_Website/wp-theme/PurdueCruTheme/style.css rahmanj@mace.itap.purdue.edu:www/wp-content/themes/PurdueCruTheme

scp ~/Dropbox/CRU_Website/wp-theme/PurdueCruTheme/js/site.js rahmanj@mace.itap.purdue.edu:www/wp-content/themes/PurdueCruTheme/js

sudo cp -r ~/Dropbox/CRU_Website/wp-theme/PurdueCruTheme/style.css /var/www/wp-content/themes/PurdueCruTheme
sudo cp -r ~/Dropbox/CRU_Website/wp-theme/PurdueCruTheme/js/site.js /var/www/wp-content/themes/PurdueCruTheme/js

