# Nova Assets Changer
This package is for Nova 4 and swaps the resources from the `resources/Nova` folder with those in the `vendor/laravel/nova/resources/js` folder. 
Then the assets are recompiled and published with the Force option.

Attention. The original vendor files will be overwritten.

## Install
```
composer require norman-huth/nova-assets-changer
```

### Optional
Publish example resources
```
php artisan vendor:publish --provider="NormanHuth\NovaAssetsChanger\PackageServiceProvider"
```

### Running
For the full process run this command:
```
php artisan nova:custom-assets
```

If you want to skip NPM install use this command:
```
php artisan nova:custom-assets --without-npm-install
```

## Notice
After a Nova update, you need to check your resource files to see if they are still compatible.
