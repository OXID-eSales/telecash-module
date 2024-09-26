# OXID eShop TeleCash Module

[![Development](https://github.com/OXID-eSales/osc_telecash/actions/workflows/trigger.yaml/badge.svg?branch=b-7.1.x)](https://github.com/OXID-eSales/osc_telecash/actions/workflows/trigger.yaml)
[![Latest Version](https://img.shields.io/packagist/v/OXID-eSales/osc_telecash?logo=composer&label=latest&include_prereleases&color=orange)](https://packagist.org/packages/oxid-esales/osc_telecash)
[![PHP Version](https://img.shields.io/packagist/php-v/oxid-esales/osc_telecash)](https://github.com/oxid-esales/osc_telecash)

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_osc_telecash&metric=alert_status)](https://sonarcloud.io/dashboard?id=OXID-eSales_osc_telecash)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_osc_telecash&metric=coverage)](https://sonarcloud.io/dashboard?id=OXID-eSales_osc_telecash)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_osc_telecash&metric=sqale_index)](https://sonarcloud.io/dashboard?id=OXID-eSales_osc_telecash)


Payment-Module for Payment-Provider Telecash.

## Branch compatibility

* b-7.1.x branch / v1.x version - compatible with OXID eShop compilation 7.1.x and the respective branch

## Installation

* ...
* ... 

### Install and try it out

This module is in working state and can be directly installed via composer:
```
composer require oxid-solution-catalysts/osc_telecash
./vendor/bin/oe-eshop-doctrine_migration migrations:migrate osc_telecash
```

and [activate the module](https://docs.oxid-esales.com/developer/en/latest/development/modules_components_themes/module/installation_setup/setup.html#setup-activation).


## Running tests and quality tools

Check the ``scripts`` section in the composer.json of the module to get more insight of
preconfigured quality tools available. Example:

```bash
$ composer phpcs
$ composer phpstan
$ composer phpmd
```

### Integration/Acceptance tests

- install this module into a running OXID eShop
- run `composer update` in module root directory

```bash
$ cd vendor/oxid-solution-catalysts/osc_telecash
$ composer update
```

After this done, check the "scripts" section of module `composer.json` file to see how we run tests.

```bash
$ composer tests-unit
$ composer tests-integration
$ composer tests-codeception
```

NOTE: From OXID eShop 7.0.x on database reset needs to be done with this command (please fill in your credentials)

```bash
$ bin/oe-console oe:database:reset --db-host=mysql --db-port=3306 --db-name=example --db-user=root --db-password=root --force
```

### Contact us

* In case of issues / bugs, use "Issues" section on github, to report the problem.
* [Join our community forum](https://forum.oxid-esales.com/)
* [Use the contact form](https://www.oxid-esales.com/en/contact/contact-us.html)

In case you have any complaints, suggestions, business cases you'd like an example for
please contact us. Pull request are also welcome.  Every feedback we get will help us improve.
