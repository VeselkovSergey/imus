<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property integer user_id
 * @property string title
 * @property string data
 */
class Layouts extends BaseModel
{
    public function points()
    {
        return $this->hasMany(Points::class, 'layout_id', 'id');
    }

    protected function data(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
        );
    }
}
