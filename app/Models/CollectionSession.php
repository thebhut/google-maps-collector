<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_uuid',
        'source',
        'search_query',
        'google_maps_url',
        'started_at',
        'finished_at',
        'status',
        'businesses_detected',
        'businesses_uploaded',
        'duplicates_count',
        'errors_count',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'businesses_detected' => 'integer',
            'businesses_uploaded' => 'integer',
            'duplicates_count' => 'integer',
            'errors_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function errors(): HasMany
    {
        return $this->hasMany(CollectionError::class);
    }
}
