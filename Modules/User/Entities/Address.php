<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $guarded = ['id'];

    public function state()
    {
        return $this->belongsTo(\Modules\Area\Entities\State::class);
    }
}
