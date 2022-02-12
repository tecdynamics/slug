<?php

namespace Tec\Slug\Http\Requests;

use Tec\Support\Http\Requests\Request;

class SlugRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'    => 'required',
            'slug_id' => 'required',
        ];
    }
}
