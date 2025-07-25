<?php

namespace App\Providers;

use App\Models\Question;
use App\Models\Workshop;
use App\Policies\QuestionPolicy;
use App\Policies\WorkshopPolicy;
use App\Models\Assignment;
use App\Policies\AssignmentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Question::class => QuestionPolicy::class,
        Workshop::class => WorkshopPolicy::class,
        Assignment::class => AssignmentPolicy::class, // Assuming AssignmentPolicy exists
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
