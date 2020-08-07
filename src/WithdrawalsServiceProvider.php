<?php
namespace Codificar\Withdrawals;
use Illuminate\Support\ServiceProvider;

class WithdrawalsServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        $this->loadViewsFrom(__DIR__.'/resources/views', 'withdrawals');

        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');

        $this->publishes([
            __DIR__.'/../public/js' => public_path('vendor/codificar'),
        ], 'public');
    }

    public function register()
    {

    }
}
?>