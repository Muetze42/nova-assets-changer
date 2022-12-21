# Nova Assets Changer

This package is for Nova 4 and swaps the resources from the `resources/Nova/Nova` folder with those in
the `vendor/laravel/nova/resources/js` folder or use the `php artisan custom-assets:publish`
command.  
Then the assets are recompiled and published with the Force option.

This package creates a backup of each file and checks for changes

Attention. The original vendor files will be overwritten.

# IMPORTANT

You must run the `php artisan nova:custom-assets` after every composer update!

Tip: You can replace `@php artisan nova:publish` with `@php artisan custom-assets:after-composer-update` in
Your `composer.json`

## Install

```
composer require norman-huth/nova-assets-changer --dev
```

### Running

For the full process run this command:

```
php artisan nova:custom-assets
```

### Optional

#### Run Command Only If A Nova Update Is Detected (Or The Package Has No Version Saved)

```
php aritsan custom-assets:after-composer-update
```

#### Publish Nova Assets Via Command

```
php artisan custom-assets:publish
```

#### Publish Nova Field Assets Via Command (experimental)

```
php artisan custom-assets:publish:field
```

Publish example resources

```
php artisan vendor:publish --provider="NormanHuth\NovaAssetsChanger\PackageServiceProvider"
```

## Notice

After a Nova update, you need to check your resource files to see if they are still compatible.

## Examples

I make not a release for every example. For all example resources take a look in the `resources` folder of the GitHub
repository

## Other Composer Or NPM Command

Create a command:
`php artisan make:command CustomAssetsCommand`

with followingen content:

```php
<?php

namespace App\Console\Commands;

use NormanHuth\NovaAssetsChanger\Console\Commands\CustomAssetsCommand as Command;

class CustomAssetsCommand extends Command
{
    /**
     * CLI Composer Command
     *
     * @var string
     */
    protected string $composerCommand = 'composer';

    /**
     * CLI NPM Command
     *
     * @var string
     */
    protected string $npmCommand = 'npm';
}

```

## Add Custom CSS

Create `resources/Nova/custom.css` with Your custom CSS.

## Register new pages

Example: [Register Page](/docs/register-page.md)

---
[![More Laravel Nova Packages](https://raw.githubusercontent.com/Muetze42/asset-repo/main/svg/more-laravel-nova-packages.svg)](https://huth.it/nova-packages)


[![Stand With Ukraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner2-direct.svg)](https://vshymanskyy.github.io/StandWithUkraine/)

[![Woman. Life. Freedom.](https://raw.githubusercontent.com/Muetze42/Muetze42/2033b219c6cce0cb656c34da5246434c27919bcd/files/iran-banner-big.svg)](https://linktr.ee/CurrentPetitionsFreeIran)
