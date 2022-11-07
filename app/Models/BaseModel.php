<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string created_at
 * @property string updated_at
 */
class BaseModel extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

//    protected function createdAt(): Attribute
//    {
//        return Attribute::make(
//            get: fn ($value) => date('Y-m-d H:i:s', strtotime($value))
//        );
//    }
//
//    protected function updatedAt(): Attribute
//    {
//        return Attribute::make(
//            get: fn ($value) => date('Y-m-d H:i:s', strtotime($value))
//        );
//    }
}
