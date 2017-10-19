# NCSU Shortcodes

**Contributors:** Chris Deaton, Brian DeConinck, fusionengineering

**License:** GPLv2 or later

**License URI:** http://www.gnu.org/licenses/gpl-2.0.html

## Description

Modelled after [Shortcake's Bakery](https://github.com/wp-shortcake/shortcake-bakery), this plugin is meant to offer extended functionality and content to OIT Design clients, above and beyond what is normally offered by WordPress, while ensuring that the offered functionality is compatible with whatever theme the client happens to be using.

This project is also an attempt to improve the extensibility of the original NCSU Shortcodes plugin, and make it easier for 3rd parties to contribute additional shortcodes while maintaining a standardized approach that preserves the integrity of each individual shortcode.

## Contributing

See the [wiki](https://github.ncsu.edu/oitdesign/NCSU-Shortcodes/wiki) for guidance on how to contribute proposed shortcodes for inclusion into this project.

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
