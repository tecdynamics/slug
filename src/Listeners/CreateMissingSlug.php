<?php

namespace Tec\Slug\Listeners;

use Tec\Slug\Facades\SlugHelper;

class CreateMissingSlug
{
    public function handle(): void
    {
        foreach (SlugHelper::supportedModels() as $model => $name) {
            $model = app($model);

            $model->query()->get()->each(function ($item) {
                SlugHelper::createSlug($item);
            });
        }
    }
}
