#!/bin/bash

echo "Executing $0"

[ -f "$WP_TESTS_DIR/wp-tests-config.php" ] && exit 0

cd /wordpress/wp-content/plugins/woocommerce-jadlog
SKIP_DB_CREATE=$(test "$(echo 'show databases;' | mysql -h $WORDPRESS_DB_HOST -u $WORDPRESS_DB_USER --password=$WORDPRESS_DB_PASSWORD | grep $WORDPRESS_DB_NAME)" == "$WORDPRESS_DB_NAME" && echo true)
WP_TESTS_DIR="/tmp/wordpress-tests-lib" ./bin/install-wp-tests.sh $WORDPRESS_DB_NAME $WORDPRESS_DB_USER $WORDPRESS_DB_PASSWORD $WORDPRESS_DB_HOST latest $SKIP_DB_CREATE

[ $? != 0 ] && exit $?

cp -R /tmp/wordpress-tests-lib/* $WP_TESTS_DIR/
[ $? != 0 ] && exit 1

exit 0

# cd /wordpress && wp scaffold plugin-tests woocommerce-jadlog --ci gitlab --allow-root
