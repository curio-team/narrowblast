# SD NarrowBlast

This system allows students to create (interactive) slides for narrowcasting. Students can (weekly) earn credits with good attendance and use these to enhance their slides. Teachers moderate the slides and can add their own slides to the rotation.

## Getting Started

### Local development
* Clone this repository
* Run the following commands in the root of that repo:
    * `composer install`
    * `npm install`
    * Create and configure the `.env` file:
        * Fill `AMO_CLIENT_ID` and `AMO_CLIENT_SECRET` with the correct (secret) app secrets for the [amoclient OpenID auth](https://github.com/StudioKaa/amoclient)
        * Fill `SLIDE_SHOW_SECRET_TICK_KEY` with a random secret string. This is used to prevent spamming of the slideshow tick endpoint. When setting up a narrowcasting screen you will have to enter this.
    * `php artisan storage:link`
    * `php artisan migrate --seed` (The seeder automatically adds the shop items and 1 screen)
    * `npm run watch`
    * `php artisan serve`

The website is now available for
* Narrowcasting: `https://narrowblast.curio.codes/screen/1`
* Students and teachers can login to add slides and spend credits: `https://narrowblast.curio.codes/`
* Teachers can manage through the filament admin panel: `https://narrowblast.curio.codes/admin`

### Production
* `sudo chown -R www-data:www-data /path/to/this/repo/root`
* To run the queue that removes preview slides:
    * Locally for development use: `php artisan queue:work --stop-when-empty` to run the queue manually (wait 5 minutes after adding test slide)
    * On production set this CRON task: `* * * * * cd /path-to-your-project && php artisan queue:work --stop-when-empty >> /dev/null 2>&1`
* Configure uploaded sites and previews to go live on a different origin, to protect from XSS attacks.
* Configure your apache vhosts file to allow access to uploaded files from inside the sandboxed iframe (some students use JS to include content from their slide):
```
<VirtualHost _default_:443>
    ...
    # Ensure this Directory points to the symlink path apache follows (and not the actual storage directory)
    <Directory /var/www/html/narrowblast/public/storage/>
            <IfModule mod_headers.c>
                Header set Access-Control-Allow-Origin "*"
            </IfModule>
    </Directory>
    ...
</VirtualHost>
```

## Notes:

### cURL error 60: SSL certificate expired
To test locally it can be useful to change line `28` in `/vendor/studiokaa/amoclient/src/AmoclientController.php` to `$http = new \GuzzleHttp\Client(['curl' => [CURLOPT_SSL_VERIFYPEER => false]]);`. On production you should just enable HTTPS.
