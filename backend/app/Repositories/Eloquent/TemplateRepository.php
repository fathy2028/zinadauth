<?php

namespace App\Repositories\Eloquent;

use App\Models\Template;
use App\Models\TemplateLink;
use App\Repositories\Interfaces\TemplateRepositoryInterface;

class TemplateRepository extends BaseRepository implements TemplateRepositoryInterface
{
    public function __construct(Template $template, protected TemplateLink $templateLink)
    {
        parent::__construct($template);
    }

    public function createWithLink(array $attributes, string $linkableType, string $linkableId)
    {
        $template = $this->model->create($attributes);

        $this->templateLink->create([
            'linkable_type' => $linkableType,
            'linkable_id' => $linkableId,
            'template_id' => $template->id,
        ]);

        return $template->load('templateLinks.linkable');
    }
}
