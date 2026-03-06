<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelatedProduct extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'node_id',
        'link_id',
        'label'
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'node_id');
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'link_id');
    }
}