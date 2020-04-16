# WordPress Core Installer

[![Build Status](https://travis-ci.org/johnpbloch/wordpress-core-installer.svg?branch=master)](https://travis-ci.org/johnpbloch/wordpress-core-installer)
[![codecov](https://img.shields.io/codecov/c/github/johnpbloch/wordpress-core-installer/master.svg)](https://codecov.io/gh/johnpbloch/wordpress-core-installer)
[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)
[![Packagist](https://img.shields.io/packagist/dt/johnpbloch/wordpress-core-installer.svg)](https://packagist.org/packages/johnpbloch/wordpress-core-installer)
![GitHub tag](https://img.shields.io/github/tag/johnpbloch/wordpress-core-installer.svg)

A custom Composer plugin to install WordPress core outside of `vendor`.

This installer is meant to support a rather specific WordPress installation setup in which WordPress is installed in a subdirectory ([see the WordPress codex on that subject](https://codex.wordpress.org/Giving_WordPress_Its_Own_Directory)) and in which the location of `WP_CONTENT_DIR` and `WP_CONTENT_URL` have been customized to be outside of WordPress core ([see the WordPress codex on that subject](https://codex.wordpress.org/Editing_wp-config.php#Moving_wp-content_folder)). This is because composer will delete your whole wp-content directory every time it updates core if you don't separate the two. If that installation setup isn't what you are looking for, then this installer is probably not something you will want to use.

For more information on this site setup and using Composer to manage a whole WordPress site, [check out @Rarst's informational website](https://composer.rarst.net/) which also includes [a site stack example using this package](https://composer.rarst.net/recipe/site-stack/).

### Usage
To set up a custom WordPress build package to use this as a custom installer, add the following to your package's composer file:

```json
"type": "wordpress-core",
"require": {
	"johnpbloch/wordpress-core-installer": "^2.0"
}
```

If you need to maintain support for PHP versions lower than 5.6 (not recommended!), use `^1.0` as your version constraint in the above.

By default, this package will install a `wordpress-core` type package in the `wordpress` directory. To change this you can add the following to either your custom WordPress core type package or the root composer package:

```json
"extra": {
	"wordpress-install-dir": "custom/path"
}
```

The root composer package can also declare custom paths as an object keyed by package name:

```json
"extra": {
	"wordpress-install-dir": {
		"wordpress/wordpress": "wordpress",
		"johnpbloch/wordpress-core": "jpb-wordpress"
	}
}
```

### License
This is licensed under the GPL version 2 or later.

### Changelog

##### 2.0.0
- Added support for Composer v2. Special thanks to @Ayesh for the original pull request to add this support.
- Bumped minimum required PHP version to 5.6 (same as WP). If you need to stick with an older PHP version, you're probably ok with also sticking with an older version of Composer and can continue to use `^1.0` as your version constraint.
- Other various fixes and improvements to README, tests, etc.

##### 1.0.0
- Initial stable release
- Added tests and CI
- Support added for custom vendor directories
- Added sanity check for overwriting sensitive directories
