<?php

namespace App\Models;

/**
 * @property integer startPointId
 * @property integer endPointId
 */
class Lines extends BaseModel
{
    public function fromPoint()
    {
        return $this->belongsTo(Points::class, 'startPointId', 'id');
    }

    public function toPoint()
    {
        return $this->belongsTo(Points::class, 'endPointId', 'id');
    }
}
