# UAMS Shortcodes

**Contributors:** Todd McKee, Original work from Chris Deaton, Brian DeConinck, fusionengineering

**License:** GPLv2 or later

**License URI:** http://www.gnu.org/licenses/gpl-2.0.html

## Description

Modelled after [Shortcake's Bakery](https://github.com/wp-shortcake/shortcake-bakery), this plugin is meant to offer extended functionality and content to OIT Design clients, above and beyond what is normally offered by WordPress, while ensuring that the offered functionality is compatible with whatever theme the client happens to be using.

This project is also an attempt to improve the extensibility of the original NCSU Shortcodes plugin, and make it easier for 3rd parties to contribute additional shortcodes while maintaining a standardized approach that preserves the integrity of each individual shortcode.

## Documentation

The [wiki](https://github.com/UAMS-Web/UAMSWP-shortcodes-ui/wiki) has a walkthrough, guides, the reference for every shortcode and hook, and troubleshooting. Its pages are kept in [`docs/wiki/`](docs/wiki/) and published from there.

## Requirements

- WordPress 6.8 or later
- PHP 8.2 or later
- The [Shortcake (Shortcode UI)](https://wordpress.org/plugins/shortcode-ui/) plugin, active, and the classic editor for its forms

## Installation

1. Install and activate Shortcake (Shortcode UI).
2. Install this plugin from a [release](https://github.com/UAMS-Web/UAMSWP-shortcodes-ui/releases) download, or from this repository with a deployment tool that installs a GitHub repository as a plugin.
3. Activate **UAMSWP Shortcodes UI** in **Plugins**.

The elements then appear under **Add Post Element** in the classic editor. The wiki's [first walkthrough](https://github.com/UAMS-Web/UAMSWP-shortcodes-ui/wiki/Adding-your-first-shortcode) inserts one.

## Credits

Based on NCSU Shortcodes by OIT Design, Brian DeConinck and Chris Deaton.

Modified by UAMS Web Services.

Developers:

- Todd McKee
- Josh Daugherty
- Brent Passmore

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for the issue-to-merge path, and the wiki's [Adding a shortcode to a site](https://github.com/UAMS-Web/UAMSWP-shortcodes-ui/wiki/Adding-a-shortcode-to-a-site) for how a shortcode class is built.

[NodeJS](https://nodejs.org/en/) is required for properly preparing source files. Afterward, we use Gulp to do the dirty work. 

```
# install gulp globally
npm install -g gulp
```
```
# install gulp locally
npm install gulp
```
```
# install gulp dependencies
npm install
```

#### CSS and JS changes
Gulp is set to compile CoffeeScript, just be sure to put it in the appropriate location. This project also makes use of Sass, which its own respective location. No direct edits to CSS should be made.

It may be fruitful at some point to also minify/uglify.


```
# lastly, execute the task runner
gulp
```

## Continuous integration

Pull requests and pushes to `master` run `.github/workflows/ci.yml`, which calls the shared chain in [UAMS-Web/uams-ci-workflows](https://github.com/UAMS-Web/uams-ci-workflows) (Pint, Rector, PHPStan, Pest, a PHP 8.2 syntax check of the files the download ships, and the download archive check). The one check a ruleset needs from it is `CI`. To run the same checks locally:

```
composer test:pint
composer test:refactor
composer stan
composer test:unit
LINT_PHP=/path/to/php8.2 composer lint:floor
node .claude/skills/download-archive-check/check-download-archive.mjs
```
