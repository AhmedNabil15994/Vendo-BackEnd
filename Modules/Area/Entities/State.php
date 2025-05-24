<?php

namespace Modules\Area\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\ScopesTrait;
use Modules\Vendor\Entities\DeliveryCharge;
use Modules\Vendor\Entities\Vendor;

use Spatie\Translatable\HasTranslations;
use Modules\Core\Traits\HasSlugTranslation;

class State extends Model
{
    use HasSlugTranslation;
    use HasTranslations, SoftDeletes, ScopesTrait;

    protected $fillable                     = ["status", "city_id", "title", "slug"];
    public $translatable = ['title', 'slug'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function deliveryCharge()
    {
        return $this->hasOne(\Modules\Company\Entities\DeliveryCharge::class, 'state_id');
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'vendor_states');
    }
}
