# PSR Container Messenger

_PSR-11 Container compatible Symfony Messenger message bus and queue._

[![Continuous Integration](https://github.com/xtreamwayz/psr-container-messenger/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/xtreamwayz/psr-container-messenger/actions/workflows/continuous-integration.yml)

This packages brings message buses to your PSR-11 container based project. It's a bundle of factories to make
life easier for you. The real work is done by [Symfony Messenger](https://github.com/symfony/messenger).

It comes with pre-configured command, event and query buses for your convenience. Or don't use them if you want to
create your own. Transports can be used to queue your messages or send and receive them to/from 3rd parties.

## Installation

```bash
composer require xtreamwayz/psr-container-messenger
```

## Documentation

All project documentation is located in the [./docs](./docs) folder. If you would like to contribute
to the documentation, please submit a pull request. You can read the docs online:
https://xtreamwayz.github.io/psr-container-messenger/

## Contributing

**_BEFORE you start work on a feature or fix_**, please read & follow the
[contributing guidelines](https://github.com/xtreamwayz/.github/blob/master/CONTRIBUTING.md#contributing)
to help avoid any wasted or duplicate effort.

## Copyright and license

Code released under the [MIT License](https://github.com/xtreamwayz/.github/blob/master/LICENSE.md).
Documentation distributed under [CC BY 4.0](https://creativecommons.org/licenses/by/4.0/).
