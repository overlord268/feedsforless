<?php

namespace App\Domains\Quotes\Models;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\PackagingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteRequestItem extends Model
{
    protected $fillable = [
        'quote_request_id',
        'product_id',
        'packaging_type_id',
        'qty',
        'estimated_freight_cost',
        'estimated_product_cost',
        'line_total_cost',
    ];

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function packagingType(): BelongsTo
    {
        return $this->belongsTo(PackagingType::class);
    }
}