<?php

namespace Tec\Slug\Facades;

use Illuminate\Support\Facades\Facade;
use Tec\Slug\SlugHelper as SlugHelperService;

class SlugHelper extends Facade
{

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SlugHelperService::class;
    }
}
