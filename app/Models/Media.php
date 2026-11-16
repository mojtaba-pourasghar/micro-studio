<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use LogsActivity;

    protected $table = 'media';

    protected $guarded = ['id'];

    public function url(): string
    {
        return asset($this->path);
    }
}
