<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ContentRule extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    protected $casts = ['approved_at' => 'datetime'];

    public const STATUSES = ['proposed' => 'پیشنهادی', 'approved' => 'تأییدشده', 'archived' => 'بایگانی'];
}
