# Customize Nova 4 Vue.js Assets

No package. But a working snipped....

## Description
Copy the files you want to change from folder `vendor/laravel/nova/resources/js` to folder `resources/Nova` and adjust them according to your wishes.  

## Command  
The [`php artisan nova:update`](app/Console/Commands/Nova/NovaUpdateCommand.php) command install npm dependencies in the Nova vendor folder, 
replaces the components and run the Nova production command.   
Attention. The files in the Nova vendor folder will be changed!
Next, the newly generated Nova assets will be published with the force option.  
---
You can skip `npm install` with `php artisan nova:update --without-npm-install`

### Important
### Todo after a `laravel/nova` update:
* Align the Nova app files `resources/Nova` with the Nova vendor files `vendor/laravel/nova/resources/js`
* Run the [`nova:update`](app/Console/Commands/Nova/NovaUpdateCommand.php) command
