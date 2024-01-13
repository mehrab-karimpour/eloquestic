<?php

namespace mehrab\eloquestic;

use App\Services\Search\SearchService;
use Illuminate\Support\ServiceProvider;

class EloquesticServiceProvider extends ServiceProvider
{
    // bootstrap web services
    // listen for events
    // publish configuration files or database migrations
    public function boot(): void
    {

    }

    // extend functionality from other classes
    // register service provider
    // create singleton
    public function register(): void
    {
        $this->app->singleton('eloquestic', function () {
            return new Eloquestic();
        });
    }
}
