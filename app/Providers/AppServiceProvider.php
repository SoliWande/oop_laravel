<?php

namespace App\Providers;

use App\Http\Controllers\ProcessController;
use App\Jobs\BuildWorld;
use App\Models\Animal;
use App\Models\Chicken;
use App\Models\Dinosaur;
use App\Models\Egg;
use App\Models\Falcon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('BuildWorld', function () { return new BuildWorld(); });
        $this->app->bind('Animal', function () { return new Animal(); });
        $this->app->bind('Dinosaur', function () { return new Dinosaur(); });
        $this->app->bind('Falcon', function () { return new Falcon(); });
        $this->app->bind('Chicken', function () { return new Chicken(); });
        $this->app->bind('Egg', function () { return new Egg(); });
        $this->app->bind('Process', function () { return new ProcessController(); });
    }
}
