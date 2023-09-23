<?php

namespace App\Http\Middleware;

use Closure;

// Source: https://medium.com/oceanize-geeks/laravel-middleware-basic-auth-implementation-88b777361b5c
class AuthenticateTest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $auth_user = config('app.site_testing.access_user');
        $auth_pass = config('app.site_testing.access_password');

        header('Cache-Control: no-cache, must-revalidate, max-age=0');

        $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
        $is_not_authenticated = (
            !$has_supplied_credentials ||
            $_SERVER['PHP_AUTH_USER'] != $auth_user ||
            $_SERVER['PHP_AUTH_PW']   != $auth_pass
        );

        if ($is_not_authenticated) {
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');
            exit;
        }

        return $next($request);
    }
}
