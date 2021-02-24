if [ "$1" = "up" ]
then
   echo "No more up!"
#  rm -rf _working/content-backups/remote;
#  rsync -azh -P appolonia@174.138.48.194:/var/www/kirby/content _working/content-backups/remote/;
#  rsync -azhr --delete -P --chmod=D775,F664 --chown=appolonia:www-data content appolonia@174.138.48.194:/var/www/kirby/;
elif [ "$1" = "down" ]
then
  rm -rf _working/content-backups/local;
  rsync -r content/ _working/content-backups/local/;
  rsync -azh --delete -P appolonia@myhipline.com:/var/www/kirby/content/ content;
else
  echo "Specify 'down' or 'up'";
fi
