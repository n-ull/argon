<?php

namespace Domain\EventManagement\Models;

use Domain\EventManagement\Database\Factories\TaxAndFeeFactory;
use Domain\EventManagement\Enums\CalculationType;
use Domain\EventManagement\Enums\DisplayMode;
use Domain\EventManagement\Enums\TaxFeeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Domain\OrganizerManagement\Models\Organizer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TaxAndFee Model
 *
 * @property int $id
 * @property int $event_id
 * @property TaxFeeType $type
 * @property string $name
 * @property CalculationType $calculation_type
 * @property float $value
 * @property DisplayMode $display_mode
 * @property array|null $applicable_gateways
 * @property bool $is_active
 * @property int $sort_order
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read Event $event
 */
class TaxAndFee extends Model
{
    use HasFactory;

    protected $table = 'taxes_and_fees';

    protected $fillable = [
        'organizer_id',
        'type',
        'name',
        'calculation_type',
        'value',
        'display_mode',
        'applicable_gateways',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'type' => TaxFeeType::class,
        'calculation_type' => CalculationType::class,
        'display_mode' => DisplayMode::class,
        'applicable_gateways' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'value' => 'float',
    ];

    // TODO: make this a BelongsToMany relationship, to reuse this taxes inside other events
    // of the same organizer
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_tax_and_fee')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function isApplicableToGateway(?string $gateway): bool
    {
        if (empty($this->applicable_gateways)) {
            return true;
        }

        return in_array($gateway, $this->applicable_gateways);
    }

    public function calculateAmount(float $baseAmount): float
    {
        return $this->calculation_type === CalculationType::PERCENTAGE
            ? $baseAmount * ($this->value / 100)
            : $this->value;
    }

    protected static function newFactory(): TaxAndFeeFactory
    {
        return TaxAndFeeFactory::new();
    }
}
