<?php

namespace App\Providers;

use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use App\Policies\BoardPolicy;
use App\Policies\ColumnPolicy;
use App\Policies\SubtaskPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TagPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Board::class => BoardPolicy::class,
        Column::class => ColumnPolicy::class,
        Task::class => TaskPolicy::class,
        Subtask::class => SubtaskPolicy::class,
        Tag::class => TagPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
