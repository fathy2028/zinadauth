<?php

namespace App\Repositories\Interfaces;

use App\Models\Template;

interface TemplateRepositoryInterface extends BaseRepositoryInterface
{
    public function createWithLink(array $attributes, string $linkableType, string $linkableId);
}
