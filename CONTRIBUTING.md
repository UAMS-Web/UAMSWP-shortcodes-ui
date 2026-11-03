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

## Checks

```sh
composer test:pint       # formatting (Pint, `laravel` preset)
composer test:refactor   # Rector, dry run
composer stan            # PHPStan with the WordPress extension
composer test            # Pest
```

`composer pint` and `composer refactor` apply their fixes.

The remote gate is [`.github/workflows/ci.yml`](.github/workflows/ci.yml). It runs those four, the PHP 8.2 floor lint, `composer validate`, the synced-files check and the emoji sweep on every pull request to `master`. Its final `CI` job fails unless every other job in the file succeeded, so a ruleset requires `CI` alone, with the two pull-request body checks, `Attribution footer` and `Closing keywords`.
