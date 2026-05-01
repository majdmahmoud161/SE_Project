<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flow extends Model
{
        protected $table = "flows";
protected $fillable = ['name', 'data'];
    protected $casts = ['data' => 'array'];

}
