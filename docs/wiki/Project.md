<!-- cspell:ignore NCSU UAMS UAMSWP -->

Written against v0.7.0.

UAMSWP Shortcodes UI is developed by UAMS Web Services in [UAMS-Web/UAMSWP-shortcodes-ui](https://github.com/UAMS-Web/UAMSWP-shortcodes-ui). It began as NC State's NCSU Shortcodes by OIT Design and borrows several embeds from [Shortcake Bakery](https://github.com/wp-shortcake/shortcake-bakery); the [README](https://github.com/UAMS-Web/UAMSWP-shortcodes-ui#credits) has the credits.

## Reporting a problem

Open an issue in the [tracker](https://github.com/UAMS-Web/UAMSWP-shortcodes-ui/issues/new/choose), using the bug template. Say which shortcode, the attributes it was given (the shortcode as it appears in the Text tab), what the page showed and what was expected.

## Releases

Each release is a tag on `master` with notes on the [releases page](https://github.com/UAMS-Web/UAMSWP-shortcodes-ui/releases). The plugin header's `Version` is the release number. These pages are republished from `docs/wiki/` whenever a release is published.

## Changing the plugin

Work goes through a pull request to `master`, described in [CONTRIBUTING.md](https://github.com/UAMS-Web/UAMSWP-shortcodes-ui/blob/master/CONTRIBUTING.md). Every pull request runs the checks listed in the README's [Continuous integration](https://github.com/UAMS-Web/UAMSWP-shortcodes-ui#continuous-integration) section, and the README says how to run them locally and how to rebuild the CSS and JavaScript.

Decisions that closed, with their reasoning and the alternatives turned down, are recorded in [docs/DECISIONS.md](https://github.com/UAMS-Web/UAMSWP-shortcodes-ui/blob/master/docs/DECISIONS.md).

## Changing these pages

Edit the Markdown in [`docs/wiki/`](https://github.com/UAMS-Web/UAMSWP-shortcodes-ui/tree/master/docs/wiki) through a pull request. Link between pages with relative links that carry the extension (`[Hooks](Hooks.md)`); the publish turns them into wiki links. An edit made on the wiki itself is overwritten by the next publish.
