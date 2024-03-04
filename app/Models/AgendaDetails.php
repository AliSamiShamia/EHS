<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgendaDetails extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agenda_time',
        'type',
        'title',
        'subtitle',
        'colored',
        'order',
        'agenda_id',
    ];

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class, 'agenda_id');
    }
}
