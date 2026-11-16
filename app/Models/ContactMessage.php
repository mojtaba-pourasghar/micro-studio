<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['newsletter' => 'boolean', 'read_at' => 'datetime'];
}
