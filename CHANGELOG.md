# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 0.5.0 - 2019-06-10

### Added

- [#10](https://github.com/xtreamwayz/expressive-messenger/pull/10) adds Symfony Messenger version 4.3.x support.

### Changed

- [#10](https://github.com/xtreamwayz/expressive-messenger/pull/10) changed to the original consume messages command.

- [#10](https://github.com/xtreamwayz/expressive-messenger/pull/10) changed the naming of supplied busses and related middleware.

- [#10](https://github.com/xtreamwayz/expressive-messenger/pull/10) changed to the PHP internal serializer.

### Deprecated

- Nothing.

### Removed

- [#10](https://github.com/xtreamwayz/expressive-messenger/pull/10) removed add default middleware config option. Middleware needs to be added manually now. The supplied busses already have the sender and handler middleware setup.

- [#10](https://github.com/xtreamwayz/expressive-messenger/pull/10) removed obsolete symfony serializer factories.

- [#10](https://github.com/xtreamwayz/expressive-messenger/pull/10) removed enqueue transport support as messenger supplies it own.

- [#10](https://github.com/xtreamwayz/expressive-messenger/pull/10) removed deprecated LoggingMiddleware.

### Fixed

- Nothing.

## 0.4.2 - 2019-06-03

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#9](https://github.com/xtreamwayz/expressive-messenger/pull/9) restricts Symfony to version 4.2.x.

## 0.4.1 - 2019-03-06

### Added

- Nothing.

### Changed

- [#6](https://github.com/xtreamwayz/expressive-messenger/pull/6) uses container locators for handlers and senders.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#6](https://github.com/xtreamwayz/expressive-messenger/pull/6) fixes senders being wrapped in callables where SenderInterface objects are expected.

## 0.4.0 - 2018-12-31

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
