#!bin/bash

## Move to the plugin folder of the wordpress test environment.
cd "/tmp/wordpress/src/wp-content/plugins/$PLUGIN_SLUG" &&

## Run the php unit tests.
phpunit -c "$REPO_BASE/tests/phpunit.xml.dist" &&

## Change to the plug-in folder that contains the tests.
cd "$REPO_BASE/tests" &&

## Run the Front-End JavaScript tests (need the WebServer).
grunt casper
