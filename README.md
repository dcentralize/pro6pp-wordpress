Pro6pp Wordpress/WooCommerce Plugin
===================================

The plugin is dependant on WooCommerce since it targets the address related form fields of WooCommerce.

Build Status: [![Build Status](https://travis-ci.org/dcentralize/pro6pp-wordpress.svg?branch=master)](https://travis-ci.org/dcentralize/pro6pp-wordpress)

## Requirements:

- An accessible `WordPress` site that you have administration rights to.
- `WooCommerce` plugin to be installed and activated on that site.

## How to install:

* Clone the repository with either option:
 * `zip file of the repository`, provided by github
     - Extract or Copy the files into a new/empty folder and assign to that folder an appropriate name (for example, `pro6pp`).
 * [zip file with only the required files](https://github.com/dcentralize/pro6pp-wordpress/zipball/stable)
     - Can be installed directly on WordPress via the `Plugin->Add New` section.
* Copy or Upload the folder inside the wordpress' plugin folder (ie: `your_WordPress_site/wp-content/plugins/`).
* Activate the plugin by visiting the administration page of your WordPress site and selecting to `Activate` the plugin.

## How to use:

* Navigate to a WooCommerce's address form (i.e. edit-account).
* Select either `Netherlands` or `Belgium` as your country.
* Fill in your `postcode`.
* Fill in your `streetnumber`.
* Continue with editing the rest of the form fields.
* Address related fields will be automatically filled in with the appropriate values.

## Contributing:

If you would like to contribute to the project, you are more than welcome.
Here are some guidelines to keep in mind.

* Follow the conventions that are used in the files.
  * Spaces are preferred over tabs.
  * Max length of line: __80__ characters
* Document any new code you introduce.
* Before submitting a pull request, test and lint your code.

PS. Tests are mandatory!

### Testing

Tests are important and currently missing.

We use [Travis-CI](https://travis-ci.org) to run our tests remotely and [wp-cli](https://github.com/wp-cli/wp-cli) on the development workstations.

#### Test Structure

Tests are found inside the `tests` folder.

In the root of the test folder:
* `bootstrap.php`: Loads test environment dependencies.
* `test-*.php`: Test files executed by `phpunit`
* `js_tests`: Folder containing javascript tests executed by `casperjs`.

**Note:** The `php` test files should always start with the `test-` prefix, where the `javascript` test files should be __alphabetically ordered__ if the execution order is important.

* `bin`: Folder containing scripts that setup the test environment and Scripts executed by Travis-CI based on test results.

### Plugin Structure

`pro6pp_autocomplete.php`:
The main file that initialises the pro6pp plugin.
In this file plugin functions and variables are defined for internal usage. It's the starting point of the plugin.

`pro6pp.php`:
This is the main class of the plugin.
Hooks into the appropriate WordPress and WooCommerce functionality.
It also implements all functionality necessary to communicate with the `pro6pp` service and the front-end.

`settings.php`: Deals with the user defined settings

`templates`:
This folder contains HTML template files (php and html combined). Any variables used in templates are defined in the settings classbefore `include`-ing the file.
    
#### Useful links
Documentation on how to build a plugin can be found on wordpress's site:
http://codex.wordpress.org/WordPress_APIs

Documentation of WooCommerce can be found on the WooCommerce's site:
http://docs.woothemes.com/wc-apidocs/index.html
