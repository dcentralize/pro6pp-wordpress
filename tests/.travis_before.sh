git clone https://github.com/tierra/wordpress.git /tmp/wordpress
git clone . "/tmp/wordpress/src/wp-content/plugins/$PLUGIN_SLUG"
cd /tmp/wordpress
git checkout $WP_VERSION
mysql -e "CREATE DATABASE wordpress_tests;" -uroot
cp wp-tests-config-sample.php wp-tests-config.php
sed -i "s/youremptytestdbnamehere/wordpress_tests/" wp-tests-config.php
sed -i "s/yourusernamehere/travis/" wp-tests-config.php
sed -i "s/yourpasswordhere//" wp-tests-config.php
