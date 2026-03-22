<?php

namespace Domain\EventManagement\Models;

use Domain\ProductCatalog\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $event_id
 * @property int|null $product_id
 * @property string $applies_to  'order' | 'product'
 * @property bool $is_active
 * @property array $fields
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @mixin \Eloquent
 */
class EventFormQuestion extends Model
{
    protected $table = 'event_form_questions';

    protected $fillable = [
        'event_id',
        'product_id',
        'applies_to',
        'is_active',
        'fields',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'fields' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
