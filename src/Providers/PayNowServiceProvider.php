<?php
/**
 * Created by PhpStorm.
 * User: Maras
 * Date: 2018/8/2
 * Time: 下午4:57
 */

namespace Maras0830\PayNowSDK\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class PayNowServiceProvider extends ServiceProvider
{
    /**
     *  Boot
     */
    public function boot()
    {
        parent::boot();
        $this->addConfig();
    }

    /**
     *  Config publishing
     */
    private function addConfig()
    {
        $this->publishes([
            __DIR__ . '/../../config/paynow.php' => config_path('paynow.php')
        ], 'config');
    }
}