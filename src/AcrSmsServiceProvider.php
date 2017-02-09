<?php
namespace Acr\Sms;

use Illuminate\Support\ServiceProvider;


class AcrSmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/Views', 'acr_views');

        $this->publishes([
            __DIR__ . '/../config/AcrSmsConfig.php' => config_path('AcrSmsConfig.php'),
        ]);

        require __DIR__ . '/Routes/routes.php';
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('acr-sms', function () {
            return new SmsController();
        });
        config([
            '/../config/AcrSmsConfig.php',
        ]);
    }

}
