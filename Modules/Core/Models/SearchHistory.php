<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    protected $table = 'search_histories';

    protected $fillable = [
        'user_id',
        'query',
        'filters',
        'results_count',
    ];

    protected $casts = [
        'filters' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
