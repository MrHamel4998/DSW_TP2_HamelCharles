<?php

namespace App\Providers;

use App\Repositories\CategoryInterface;
use App\Repositories\EquipmentInterface;
use App\Repositories\RentalInterface;
use App\Repositories\ReviewInterface;
use App\Repositories\SportInterface;
use App\Repositories\UserInterface;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\EquipmentRepository;
use App\Repositories\Eloquent\RentalRepository;
use App\Repositories\Eloquent\ReviewRepository;
use App\Repositories\Eloquent\SportRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CategoryInterface::class, CategoryRepository::class);
        $this->app->bind(SportInterface::class, SportRepository::class);
        $this->app->bind(EquipmentInterface::class, EquipmentRepository::class);
        $this->app->bind(RentalInterface::class, RentalRepository::class);
        $this->app->bind(ReviewInterface::class, ReviewRepository::class);
        $this->app->bind(UserInterface::class, UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
