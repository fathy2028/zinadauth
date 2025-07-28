<?php

namespace App\Providers;

use App\Repositories\Eloquent\SettingRepository;
use App\Repositories\Eloquent\TemplateRepository;
use App\Repositories\Eloquent\WorkshopRepository;
use App\Repositories\Interfaces\SettingRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
use App\Repositories\Interfaces\WorkshopRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Interfaces\QuestionRepositoryInterface;
use App\Repositories\Eloquent\QuestionRepository;
use App\Repositories\Interfaces\AssignmentRepositoryInterface;
use App\Repositories\Eloquent\AssignmentRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(QuestionRepositoryInterface::class, QuestionRepository::class);
        $this->app->bind(WorkshopRepositoryInterface::class, WorkshopRepository::class);
        $this->app->bind(AssignmentRepositoryInterface::class, AssignmentRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(TemplateRepositoryInterface::class, TemplateRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
