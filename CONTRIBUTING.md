# Contributing to UAMSWP-shortcodes-ui

<!-- uams-claude-skills:begin -->
## Branches and environments

Every change starts as an issue, is built on a branch linked to that issue, and reaches production through a pull request to `master`. This section covers that path and what each pull request's test plan has to cover.

| Branch | Role | Where its test plan runs |
| --- | --- | --- |
| `master` | Default branch, production | The local environment, before the pull request merges |

## From issue to merge

1. **File or pick up an issue.** The work is described there, with acceptance criteria.
2. **Create a branch from `master`, linked to the issue.** Use the issue's **Create a branch** link, or:

   ```sh
   gh issue develop <number> --base master --name <number>-<short-slug> --checkout
   ```

   The link shows the branch, and later its pull requests, on the issue.
3. **Open a pull request from the branch to `master`.** It carries the full description: what changed, why, and how it was verified. Its test plan is run on the local environment, and its boxes are checked before review.

## Test plans

Every pull request ends with a `## Test plan` checklist. Its first line names the environment it was run on, and every item is something a reviewer can check there:

- **Pull request to `master`:** "Run on the local environment." Items are things you checked yourself there: a page, a role, a save, a command. Check a box only for a check you actually ran.

The [pull request template](.github/pull_request_template.md) fills in the skeleton when a pull request is opened on GitHub.

## Changing code that has no tests

Before changing code that has no tests, add a test that captures what the code does now, whether or not that is what it should do, and see it pass. Commit that test on its own, then make the change. A test that now fails shows a behavior that changed: update the test if the change was intended, and fix the code if it was not.

The [`tests` skill](.claude/skills/tests/SKILL.md#pinning-legacy-behavior-before-a-change) has the detail: what to capture, how to show a test catches a change, and how to name it.

## Agents

Coding agents follow the same path. Their instructions are in [`AGENTS.md`](AGENTS.md) and the shared skills and rules under `.claude/`, which are synced from `UAMS-Web/uams-claude-skills`.
<!-- uams-claude-skills:end -->

## Running the tests

The PHP tests use Pest 5 with Brain Monkey, which mocks WordPress functions, so no WordPress install or database is needed. The supported PHP floor is 8.2; development and tests run on the latest 8.4 patch.

```sh
composer install
composer test
```

`composer.json` lists development dependencies only.

### PHP coverage

Coverage needs a driver: PCOV (`pecl install pcov`, then enable `pcov.enabled=1`) or Xdebug in coverage mode.

```sh
vendor/bin/pest --coverage          # line coverage per file under inc/
vendor/bin/pest --coverage --min=95 # fail below a floor
```

`phpunit.xml` sets `inc/` as the measured source. Every shortcode class under `inc/shortcodes/` is at 100%, except `class-alert.php`. The uncovered remainder is recorded on UAMS-Web/UAMSWP-shortcodes-ui#30.

## Checks

```sh
composer test:pint       # formatting (Pint, `laravel` preset)
composer test:refactor   # Rector, dry run
composer stan            # PHPStan with the WordPress extension
composer test            # Pest
```

`composer pint` and `composer refactor` apply their fixes.

The remote gate is [`.github/workflows/ci.yml`](.github/workflows/ci.yml), which calls the shared chain in [UAMS-Web/uams-ci-workflows](https://github.com/UAMS-Web/uams-ci-workflows) on every pull request to `master`. The chain runs Pint, Rector, PHPStan and Pest, a PHP 8.2 syntax check of the files the download ships, and the download archive check. The file's final `CI` job fails unless the chain succeeded, so a ruleset requires `CI` alone, with the two pull-request body checks, `Attribution footer` and `Closing keywords`.
