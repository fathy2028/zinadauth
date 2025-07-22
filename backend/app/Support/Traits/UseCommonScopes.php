<?php

namespace App\Support\Traits;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait UseCommonScopes
{
    #[Scope]
    protected function status(Builder $query, \BackedEnum $status): void
    {
        $query->where('status', $status);
    }

    #[Scope]
    protected function isDeleted(Builder $query): void
    {
        $query->where('is_deleted', true);
    }
}
