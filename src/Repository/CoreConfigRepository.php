<?php

namespace Kartikey\Core\Repository;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kartikey\Core\Eloquent\Repository;

class CoreConfigRepository extends Repository
{

    /**
     * Specify model class name.
    */
    public function model(): string
    {
        return 'Kartikey\Core\Interface\CoreConfig';
    }

}
