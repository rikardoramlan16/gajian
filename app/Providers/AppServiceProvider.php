<?php

namespace App\Providers;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Connection;
use Illuminate\Database\MariaDbConnection;
use Illuminate\Database\MySqlConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use PDO;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Connection::resolverFor('mysql', function (PDO|Closure $connection, string $database, string $prefix, array $config) {
            $pdo = $connection instanceof Closure ? $connection() : $connection;

            return str_contains($pdo->getAttribute(PDO::ATTR_SERVER_VERSION), 'MariaDB')
                ? new MariaDbConnection($pdo, $database, $prefix, $config)
                : new MySqlConnection($pdo, $database, $prefix, $config);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'local' && config('app.url')) {
            URL::forceRootUrl(config('app.url'));
        }

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(
                strtolower((string) $request->input('email', '')).'|'.$request->ip()
            );
        });
    }
}
