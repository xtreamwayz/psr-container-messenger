[![No Maintenance Intended](http://unmaintained.tech/badge.svg)](http://unmaintained.tech/)

Thank you so much for being interested in this project! Open Source is rewarding, but it can also be exhausting. Therefor this code is provided as-is, and is currently not actively maintained. We invite you to peruse the code and even use it in your next project, provided you follow the included license!

No guarantee of support for the code is provided, and there is no promise that pull requests will be reviewed or merged. It’s open source, so forking is allowed; just be sure to give credit where it’s due!

---

_PSR-11 Container compatible Symfony Messenger message bus and queue._

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
