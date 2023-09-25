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
        * Fill `USER_CONTENT_PATH` with the path to the directory where uploaded files should be stored. This directory should be accessible by the webserver.
        * Fill `USER_CONTENT_URL` with the URL to the directory where uploaded files should be accessible from. This domain should be different from the domain where the website is hosted. This is to prevent XSS attacks.
    * `php artisan storage:link` (needed for shop images)
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
* Configure uploaded sites and previews to go live on a different origin, to protect from XSS attacks. See `USER_CONTENT_PATH` and `USER_CONTENT_URL` in the `.env` file.
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

#### Setting up the deployment tool

1. Set up a webhook in GitHub:
    * Content Type: `application/json`
    * URL: `https://narrowblast.curio.codes/update-deployment.php`.
    * Secret: must match `WEBHOOK_SECRET` in the `.env` file.

The deployment tool has two operating modes:

- `npm run update-ci`: An incoming GitHub webhook will cause the `update-ci` npm script to run:
    - The application is immediately put into maintenance mode
    - The local repo is updated, migrated and optimized
    - Access the site by appending `letmeenteranywayplz` to the end of any URL.
    - When testing is complete and you are satisfied run the following command through SSH to bring the site back up: `php artisan up`

- `npm run notify-takedown <url> <time>`: A developer sends a notify-takedown to warn users the site will go offline at a certain date and time:
    - Run `npm run notify-takedown <url> <time>` on your own device
        - `<url>` is the url to either `https://narrowblast.curio.codes` or `http://localhost:8000` for testing locally
        - `<time>` can be given as either a [UNIX time](https://www.unixtimestamp.com/) or datetime as Y-m-d\TH:i (in our Amsterdam timezone)
    - This npm script will make a request to the deployment tool for you and ensure the downtime indicator is set
    - E.g: `npm run notify-takedown http://localhost:8000 2022-10-31T15:30`

## Notes:

### cURL error 60: SSL certificate expired
To test locally it can be useful to change line `28` in `/vendor/studiokaa/amoclient/src/AmoclientController.php` to `$http = new \GuzzleHttp\Client(['curl' => [CURLOPT_SSL_VERIFYPEER => false]]);`. On production you should just enable HTTPS.
