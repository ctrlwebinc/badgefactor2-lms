# Testing

This project includes tests which can be run using phpunit.

## Requirements

Phpunit and WP-CLI are required for these tests.

Optionally, you may wish to setup a test badgr server to keep your main instance clean.

The WP testing environment requires subversion and php.7.(x).xml. These packages might not be installed by default in your distribution and can be added with apt-get install or equivalent.

## Setup

The testing environment will create a whole new instance of WordPress.

Before you start testing, you must setup the testing environment. These tests are _destructive_ in nature: don't use your functionning WordPress DB and instead use a separate test DB.

To setup your tests, run from the site's root folder:

```bash
wp-content/plugins/badgefactor2/bin/install-wp-tests.sh db_name db_user db_password db_host version
```

The database parameters are self-explanatory. Remember to use a different DB than your main one otherwise you'll loose your site.

The _version_ parameter is to specify the version of WP under which to run the tests. You can use _latest_.

The last step to setup your tests is to copy **phpunit.xml.sample** to **phpunit.xml** and fill-in the env parameters to match your installation.

Some of these parameters are normally setup during configuration, others are obtained during the original authorization handshake with the badgr server. Since the tests are run without user interaction, you must provide these parameters to the testing setup yourself. Use fresh values for the access and refresh tokens as well as for the token expiration.

Some of the tests interact with the badgr server and will create entities for the purposes of testing. As such, you could consider that the badgr server you use for testing will be left _unclean_. The names of the entities created for testing purposes are meant to be obvious to facilitate manual cleanup should you use your main badgr server. It's best, all in all, to use a separate badgr server for testing.

## Running tests

To run the tests, simply execute phpunit from the badge factor 2's root folder.
