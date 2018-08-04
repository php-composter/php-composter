# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [0.3.3] - 2018-08-04
### Changed
- Make bootstrap file more robust.

## [0.3.2] - 2018-07-22
### Changed
- Adapt code to make it compatible with PHP 5.4.

## [0.3.1] - 2018-07-14
### Changed
- Only show file exists warning in very verbose mode.

## [0.3.0] - 2018-07-14
### Added
- Add unit tests.
- Add ability to return a temporary checkout of staged content changes.
- Add `error` output method to BaseAction.
- Add `success` message output method to BaseAction.
- Add `title` display method to BaseAction.
- Add a method to skip an action.
- Add formatting to IO methods.
- Add path to Composer file.
- Introduce `getExtraKey()` method to BaseAction.

### Changed
- Ensure that paths to symlink into actually exist.
- Remove translatable strings, they are not a good fit for a CLI tool.
- Pass additional arguments from shell stub to the bootstrap script.
- Pass arguments from bootstrap file to action.
- Implemented relative symlink with absolute symlink fallback. Also added understandable errors, especially for Windows where privileges could be an issue.
- Added quotes around path of bootstrap.php to make sure paths with spaces are interpreted correctly.
- Remove hard requirement for package name prefix
- Instead of all modified file, get git staged files to current commit.

## [0.2.0] - 2016-03-28
### Added
- List of existing actions in `README.md`.

### Changed
- Hooks in `extra` key are regrouped under a new `php-composter-hooks` key.
- Don't require package name vendor to be `php-composter`.

### Fixes
- Correct `README.md` badges.
- Alternative way of simulating JSON comments in `README.md`.

## [0.1.3] - 2016-03-25
### Added
- Refactor bootstrapping to use an instantiated object.
- i18n all strings.

## Fixed
- Updated `README.md` with refactoring changes.

## [0.1.2] - 2016-03-25
### Added
- Graceful error-handling in bootstrapping process.
- Several comments added.
- Notice about package name requirements added to `README.md`.

## [0.1.1] - 2016-03-24
### Added
- Pass `$hook` and `$root` variables to each action.

## [0.1.0] - 2016-03-24
### Added
- Initial release to GitHub.

[0.3.3]: https://github.com/brightnucleus/php-composter/compare/v0.3.3...v0.3.2
[0.3.2]: https://github.com/brightnucleus/php-composter/compare/v0.3.2...v0.3.1
[0.3.1]: https://github.com/brightnucleus/php-composter/compare/v0.3.1...v0.3.0
[0.3.0]: https://github.com/brightnucleus/php-composter/compare/v0.3.0...v0.2.0
[0.2.0]: https://github.com/brightnucleus/php-composter/compare/v0.1.3...v0.2.0
[0.1.3]: https://github.com/brightnucleus/php-composter/compare/v0.1.2...v0.1.3
[0.1.2]: https://github.com/brightnucleus/php-composter/compare/v0.1.1...v0.1.2
[0.1.1]: https://github.com/brightnucleus/php-composter/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/brightnucleus/php-composter/compare/v0.0.0...v0.1.0
