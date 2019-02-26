<?php
/**
 * Created by PhpStorm.
 * User: taherodeh
 * Date: 10/01/2019
 * Time: 22:35
 */

namespace Smokie\LaravelCrudz;

use Illuminate\Support\ServiceProvider;
use Smokie\LaravelCrudz\Console\Commands\GenerateResourceCreate;
use Smokie\LaravelCrudz\Console\Commands\GenerateResourceEdit;

/**
 * Class CrudzServiceProvider
 * @package Smokie\LaravelCrudz
 */
class CrudzServiceProvider extends ServiceProvider
{
    public function boot()
    {
        include_once(__DIR__ . '/helpers.php');

        $this->publishes([
            __DIR__ . '/../stubs' => resource_path('crudz/stubs')
        ]);

        $this->commands([
            GenerateResourceCreate::class,
            GenerateResourceEdit::class
        ]);
    }

    public function register()
    {
    }
}
