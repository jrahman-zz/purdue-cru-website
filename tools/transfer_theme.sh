#!/bin/bash
#
# Transfer theme
#
#
sudo cp -r ~/CRU_Website/wp-theme/* /var/www/html/wp-content/themes/PurdueCru
sftp -b sftp_batch rahmanj@expert.ics.purdue.edu
