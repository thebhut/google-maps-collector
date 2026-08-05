<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionError extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'collection_session_id',
        'error_type',
        'message',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function collectionSession(): BelongsTo
    {
        return $this->belongsTo(CollectionSession::class);
    }
}
