# Bright Nucleus PHP Composter

### Git Hooks Management through Composer.

[![Latest Stable Version](https://poser.pugx.org/brightnucleus/php-composter/v/stable)](https://packagist.org/packages/brightnucleus/php-composter)
[![Total Downloads](https://poser.pugx.org/brightnucleus/php-composter/downloads)](https://packagist.org/packages/brightnucleus/php-composter)
[![Latest Unstable Version](https://poser.pugx.org/brightnucleus/php-composter/v/unstable)](https://packagist.org/packages/brightnucleus/php-composter)
[![License](https://poser.pugx.org/brightnucleus/php-composter/license)](https://packagist.org/packages/brightnucleus/php-composter)

This is a Composer plugin that manages Git pre- & post-hooks through Composer dependencies. Actions you want to be executed on Git hooks can simply be `require`d as `--dev` dependencies, and will immediately become active on `composer install`.

## Table Of Contents

* [Installation](#installation)
* [Creating a New PHP Composter Action](#creating-a-new-php-composter-action)
* [Using Existing PHP Composter Actions in Your Projects](#using-existing-php-composter-actions-in-your-projects)
* [Skipping Installation of PHP Composter Actions](#skipping-installation-of-php-composter-actions)
* [Contributing](#contributing)

## Installation

You should not need to install this package directly. It should come as a dependency of a package that is of type `php-composter-action`.

## Creating a New PHP Composter Action

To build a new PHP Composter action, you need to proceed as follows:

1. [Create a Composer Package with a Valid Name](#create-a-composer-package-with-a-valid-name)
2. [Extend `BaseAction` class](#extend-baseaction-class)
2. [Add Public Methods](#add-public-methods)
3. [Add the Class to Composer Autoloader](#add-the-class-to-composer-autoloader)
4. [Set the Composer Package Type to `php-composter-action`](#set-the-composer-package-type-to-php-composter-action)
5. [Add `php-composter/php-composter` as a dependency](#add-php-composter-php-composter-as-a-dependency)
6. [Configure Git Hooks through Composer Extra key](#configure-git-hooks-through-composer-extra-key)

### Create a Composer Package with a Valid Name

Create a new Composer package with the following naming requirements:
* The vendor must be `php-composter`
* The package name be `php-composter-<action intent>`

**Example:**

```BASH
composer init --name php-composter/php-composter-example
```

### Extend `BaseAction` class

Create a new class that `extends PHPComposter\PHPComposter\BaseAction`.

**Example:**

```PHP
<?php namespace PHPComposter\PHPComposterExample;

use PHPComposter\PHPComposter\BaseAction;

class Example extends BaseAction
{
    // [...]
}
```

### Add Public Methods

PHP Composter allows you to attach PHP methods to Git hooks. These methods need to be publicly accessible, so that they can be called by the PHP-Composter bootstrapping script.

**Example:**

```PHP
<?php
// [...]

class Example extends BaseAction
{

    /**
     * Example pre-commit action method.
     *
     * @var string $hook Name of the hook that was triggered.
     * @var string $root Root folder in which the hook was triggered.
     */
    public function preCommit()
    {
        echo 'Example Pre-Commit Hook ' . $this->hook . ' @ ' . $this->root . PHP_EOL;
    }
}
```

### Set the Composer Package Type to `php-composter-action`

You need to set the type of your Composer package in your `composer.json` file to `php-composter-action`.

**Example:**

```JSON
{
  "name": "php-composter/php-composter-example",
  "description": "PHP Composter Example.",
  "type": "php-composter-action",
  [...]
}
```

### Add the Class to Composer Autoloader

Composer's Autoloader will be initialized for each Git hook, so make sure you've registered your newly created class correctly.

**Example:**

```JSON
{
  [...]
  "autoload": {
    "psr-4": {
      "PHPComposter\\PHPComposterExample\\": "src/"
    }
  },
  [...]
}
```

### Add `php-composter/php-composter` as a dependency

You need to set the type of your Composer package in your `composer.json` file to `php-composter-action`.

**Example:**

```JSON
{
  [...]
  "require": {
    "php-composter/php-composter": "^0.1",
  },
  [...]
}
```

### Configure Git Hooks through Composer Extra key

Finally, use the `extra` key in the package's `composer.json` to attach each of your methods to a specific Git hook.

**Example:**

```JSON
{
  [...]
  "extra": {
    "20.pre-commit": "PHPComposter\\PHPComposterExample\\Example::preCommit"
  }
}
```

Hooks can either be `"<priority>.<git-hook-name>"`, or just `"<git-hook-name>"`.

In the above example, the priority is `20`. It defaults to 10 if omitted. Lower priority numbers get executed before higher ones.

#### Supported Git Hooks:
* `applypatch-msg`
* `pre-applypatch`
* `post-applypatch`
* `pre-commit`
* `prepare-commit-msg`
* `commit-msg`
* `post-commit`
* `pre-rebase`
* `post-checkout`
* `post-merge`
* `post-update`
* `pre-auto-gc`
* `post-rewrite`
* `pre-push`

## Using Existing PHP Composter Actions in Your Projects

To use an existing PHP Composter Action in your projects, simply `require` them as `--dev` dependencies:

```BASH
composer require --dev php-composter/php-composter-example
```

Anyone using Composer to pull in the development dependencies will automatically have your PHP Composter Actions installed into their `.git`.

## Skipping Installation of PHP Composter Actions

In case you want to install your the Composer dependencies of a project without activating the PHP Composter system, you can run Composer with the `--no-plugins` option:

```BASH
composer install --no-plugins
```

## Contributing

All feedback / bug reports / pull requests are welcome.
