<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    use HasFactory;

    public $guarded = [];
}