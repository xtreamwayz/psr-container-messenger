# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 0.4.0 - TBD

### Added

- [#5](https://github.com/xtreamwayz/expressive-messenger/pull/5) added messenger 4.2 compatibility.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.2 - 2018-12-31

### Added

- Added phpunit support for phpstan.

### Changed

- Locked to Symfony 4.1 packages as 4.2 contains BC.
- Changed dev dependencies to fix a phpcodesniffer-composer-installer issue.

## 0.3.1 - 2018-09-10

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#4](https://github.com/xtreamwayz/expressive-messenger/pull/4) adds the missing LoggingMiddlewareFactory.

## 0.3.0 - 2018-08-22

### Added

- [#3](https://github.com/xtreamwayz/expressive-messenger/pull/3) adds message handling middleware which sends messages 
  to multiple handlers.

- [#3](https://github.com/xtreamwayz/expressive-messenger/pull/3) adds configuration for transports with DNS. This 
  enables support for all available enqueue transports.

### Changed

- [#3](https://github.com/xtreamwayz/expressive-messenger/pull/3) changes how a message bus and enqueue transport is 
  created. This comes with a major configuration overhaul.

### Deprecated

- Nothing.

### Removed

- [#3](https://github.com/xtreamwayz/expressive-messenger/pull/3) removes the redis factory class. The preferred way is 
  to use DNS to configure enqueue transports.

### Fixed

- Nothing.

## 0.2.1 - 2018-08-02

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#2](https://github.com/xtreamwayz/expressive-messenger/pull/2) fixes the console command to be compatible with xtreamwayz/expressive-console.

## 0.2.0 - 2018-08-02

### Added

- [#1](https://github.com/xtreamwayz/expressive-messenger/pull/1) adds support for messenger 4.1.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.0 - 2018-08-02

Initial tagged release.

### Added

Everything.

### Deprecated

Nothing.

### Removed

Nothing.

### Fixed

Nothing.
