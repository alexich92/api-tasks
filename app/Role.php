<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    const NORMAL_USER = 1;

    const ADMIN_USER = 2;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];
}
