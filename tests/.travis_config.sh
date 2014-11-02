#!bin/bash

## Create a test DB
mysql -e 'create database wp_tests;'

## Restart apache2 (for sanity).
sudo /usr/sbin/apache2ctl restart

## Download Project dependencies
wget https://wordpress.org/latest.tar.gz -O wordpress.tar.gz
wget http://downloads.wordpress.org/plugin/woocommerce.zip

## Unpack dependencies in the respected folders.
# The `cd /path &&` command will fail if apache wasn't installed correctly.
cd /var/www/ && sudo tar zxf "$REPO_BASE/wordpress.tar.gz"
cd /var/www/wordpress/wp-content/plugins
sudo unzip "$REPO_BASE/woocommerce.zip"
ls /var/www/

## Config Wordpress
cd /var/www/wordpress
sudo cp wp-config-sample.php wp-config.php
sudo sed -i "s/database_name_here/wp_tests/" wp-config.php
sudo sed -i "s/username_here/travis/" wp-config.php
sudo sed -i "s/password_here//" wp-config.php

# Invoke the install page of wordpress.
wget http://127.0.0.1/wordpress/wp-admin/install.php -O /dev/null
ls /var/www/wordpress

# Copy our plugin into the plugins folder.
cd /var/www/wordpress/wp-content/plugins
sudo cp $REPO_BASE -r  ./
ls -la

# If something went wrong, the `&&` command will exit with non-zero
# status and the build will fail.
cd $PLUGIN_SLUG && ls -la

###############################-NOTE-#########################################
#                                                                            #
# The below curl command calls the wordpress install script.                 #
# The `--data` flag and the `URL` are the ones of importance.                #
# If you change the installation path of wordpress and/or the credentials,   #
# update those values as well. Otherwise the wordpress won't be installed.   #
#                                                                            #
##############################################################################
curl -v 'http://127.0.0.1/wordpress/wp-admin/install.php?step=2' \
-H 'Host: 127.0.0.1' \
-H 'User-Agent: SomeAgent' \
-H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' \
-H 'Accept-Language: en-gb,en;q=0.5' \
-H 'Accept-Encoding: gzip, deflate' \
-H 'DNT: 1' \
-H 'Referer: http://127.0.0.1/wordpress/wp-admin/install.php' \
-H 'Connection: keep-alive' \
-H 'Cache-Control: max-age=0' \
-H 'Content-Type: application/x-www-form-urlencoded' \
--data 'weblog_title=wp_tests&user_name=wp_tester&admin_password=1234567&admin_password2=1234567&admin_email=tests%40tests.dev&Submit=Install+WordPress'
