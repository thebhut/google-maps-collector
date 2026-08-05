<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'website',
        'category',
        'rating',
        'review_count',
        'place_id',
        'maps_url',
        'latitude',
        'longitude',
        'source',
        'first_collected_at',
        'last_collected_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'review_count' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'first_collected_at' => 'datetime',
            'last_collected_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
