<?php

namespace App\Domains\Quotes\Models;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\PackagingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqListItem extends Model
{
    protected $fillable = [
        'rfq_list_id',
        'product_id',
        'packaging_type_id',
        'quantity',
    ];

    public function rfqList(): BelongsTo
    {
        return $this->belongsTo(RfqList::class);
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