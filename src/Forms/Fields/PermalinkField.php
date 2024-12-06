<?php

namespace Tec\Slug\Forms\Fields;

use Tec\Base\Forms\FormField;

class PermalinkField extends FormField
{
    protected function getTemplate(): string
    {
        return 'packages/slug::forms.fields.permalink';
    }
}
