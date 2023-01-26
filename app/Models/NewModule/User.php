<?php

namespace App\Models\NewModule;

use App\Models\LaravueModel;

/**
 * Class Product
 *
 * @package App\Models\NewModule
 *
 * @property String $name
 * {{ laravue-insert:property }}
 */
class User extends LaravueModel
{

    protected $table = 'new_module.users';

    // {{ laravue-insert:relationship }}
}
