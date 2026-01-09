<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property \Illuminate\Support\Carbon|null $two_factor_confirmed_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\OrganizerManagement\Models\Organizer> $organizers
 * @property-read int|null $organizers_count
 */
	class User extends \Eloquent {}
}

namespace Domain\EventManagement\Models{
/**
 * Event Model
 * 
 * Represents an event in the event management system.
 *
 * @property int $id
 * @property string $title The title of the event
 * @property string|null $description The description of the event
 * @property array $location_info JSON object containing location information
 * @property EventStatus $status The current status of the event
 * @property \Carbon\Carbon $start_date The start date and time of the event
 * @property \Carbon\Carbon $end_date The end date and time of the event
 * @property int $organizer_id The ID of the organizer
 * @property bool $is_featured Whether the event is featured
 * @property string $slug The URL slug for the event
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read Organizer $organizer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Ticket> $tickets
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereLocationInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereOrganizerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $event_category_id
 * @property-read \Domain\EventManagement\Models\EventCategory|null $category
 * @property-read mixed $widget_stats
 * @property-read \Domain\EventManagement\Models\EventStatistics $statistics
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\EventManagement\Models\TaxAndFee> $taxesAndFees
 * @property-read int|null $taxes_and_fees_count
 * @property-read int|null $tickets_count
 * @method static \Database\Factories\Domain\EventManagement\Models\EventFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereEventCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event withoutTrashed()
 */
	class Event extends \Eloquent {}
}

namespace Domain\EventManagement\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $icon
 * @property string|null $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\EventManagement\Models\Event> $events
 * @property-read int|null $events_count
 * @method static \Domain\EventManagement\Database\Factories\EventCategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventCategory whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventCategory whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventCategory whereUpdatedAt($value)
 */
	class EventCategory extends \Eloquent {}
}

namespace Domain\EventManagement\Models{
/**
 * @property int $id
 * @property int $event_id
 * @property int $unique_visitors
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\EventManagement\Models\Event $event
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStatistics newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStatistics newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStatistics query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStatistics whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStatistics whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStatistics whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStatistics whereUniqueVisitors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStatistics whereUpdatedAt($value)
 */
	class EventStatistics extends \Eloquent {}
}

namespace Domain\EventManagement\Models{
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\EventManagement\Models\Event> $events
 * @property-read int|null $events_count
 * @method static \Domain\EventManagement\Database\Factories\TaxAndFeeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxAndFee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxAndFee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxAndFee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxAndFee whereApplicableGateways($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxAndFee whereCalculationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxAndFee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxAndFee whereDisplayMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxAndFee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxAndFee whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxAndFee whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxAndFee whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxAndFee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxAndFee whereValue($value)
 */
	class TaxAndFee extends \Eloquent {}
}

namespace Domain\Ordering\Models{
/**
 * @property int $id
 * @property int $event_id
 * @property string $total_before_additions
 * @property string $total_gross
 * @property string $status
 * @property string $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Ordering\Models\OrderItem> $order_items
 * @property-read int|null $order_items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalBeforeAdditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalGross($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $reference_id Reference used to find the order through the payment gateway.
 * @property string|null $organizer_raise_method_snapshot The preferred raise money method used by the organization at the moment the order was paid.
 * @property string|null $used_payment_gateway_snapshot The payment gateway used at the moment the order was paid.
 * @property numeric $subtotal
 * @property numeric $taxes_total
 * @property numeric $fees_total
 * @property array<array-key, mixed>|null $items_snapshot Snapshot of order items with prices at purchase time
 * @property array<array-key, mixed>|null $taxes_snapshot Applied taxes snapshot
 * @property array<array-key, mixed>|null $fees_snapshot Applied fees snapshot
 * @property int|null $user_id
 * @property-read \App\Models\User|null $client
 * @property-read \Domain\EventManagement\Models\Event $event
 * @property-read mixed $is_paid
 * @property-read mixed $total
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Ordering\Models\OrderItem> $orderItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Ticketing\Models\Ticket> $tickets
 * @property-read int|null $tickets_count
 * @method static \Database\Factories\Domain\Ordering\Models\OrderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereFeesSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereFeesTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereItemsSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrganizerRaiseMethodSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTaxesSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTaxesTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUsedPaymentGatewaySnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order withoutTrashed()
 */
	class Order extends \Eloquent {}
}

namespace Domain\Ordering\Models{
/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $product_price_id
 * @property int|null $quantity
 * @property float $unit_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Domain\Ordering\Models\Order $order
 * @property-read Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Domain\ProductCatalog\Models\ProductPrice|null $productPrice
 * @method static \Database\Factories\Domain\Ordering\Models\OrderItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereProductPriceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem withoutTrashed()
 */
	class OrderItem extends \Eloquent {}
}

namespace Domain\OrganizerManagement\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $logo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Event> $events
 * @property-read int|null $events_count
 * @property-read \Domain\OrganizerManagement\Models\OrganizerSettings|null $settings
 * @method static \Domain\OrganizerManagement\Database\Factories\OrganizerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Eventse\Eloquent\Builder<static>|Organizer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $owner_id
 * @property-read \App\Models\User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer withoutTrashed()
 */
	class Organizer extends \Eloquent {}
}

namespace Domain\OrganizerManagement\Models{
/**
 * @property int $id
 * @property int $organizer_id
 * @property string|null $raise_money_method
 * @property string|null $raise_money_account
 * @property bool $is_modo_active
 * @property bool $is_mercadopago_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Domain\OrganizerManagement\Models\Organizer|null $organizer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings query()
 * @mixin \Eloquent
 * @method static \Database\Factories\Domain\OrganizerManagement\Models\OrganizerSettingsFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereIsMercadopagoActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereIsModoActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereOrganizerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereRaiseMoneyAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereRaiseMoneyMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings withoutTrashed()
 */
	class OrganizerSettings extends \Eloquent {}
}

namespace Domain\ProductCatalog\Models{
/**
 * Product model representing items available for purchase
 *
 * @property string $name Product name
 * @property string $description Product description
 * @property int $max_per_order Maximum quantity per order
 * @property int $min_per_order Minimum quantity per order
 * @property ProductType $product_type Type of product
 * @property string $product_price_type Product price type
 * @property bool $hide_before_sale_start_date Whether to hide product before sale starts
 * @property bool $hide_after_sale_end_date Whether to hide product after sale ends
 * @property bool $hide_when_sold_out Whether to hide product when sold out
 * @property bool $show_stock Whether to display stock information
 * @property \Carbon\Carbon $start_sale_date When product sale begins
 * @property \Carbon\Carbon $end_sale_date When product sale ends
 * @property int $event_id Associated event ID
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\ProductCatalog\Models\ProductPrice> $product_prices
 * @property-read int|null $product_prices_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereEndSaleDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereHideAfterSaleEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereHideBeforeSaleStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereHideWhenSoldOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMaxPerOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMinPerOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereShowStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStartSaleDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $sort_order
 * @property bool $is_hidden
 * @property-read \Domain\EventManagement\Models\Event $event
 * @method static \Database\Factories\Domain\ProductCatalog\Models\ProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductPriceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withoutTrashed()
 */
	class Product extends \Eloquent {}
}

namespace Domain\ProductCatalog\Models{
/**
 * ProductPrice model represents pricing information for products.
 * 
 * This model handles product pricing data including regular prices,
 * sale dates, stock quantities, and visibility settings.
 *
 * @property int $product_id
 * @property float $price
 * @property string $label
 * @property \Illuminate\Support\Carbon|null $start_sale_date
 * @property \Illuminate\Support\Carbon|null $end_sale_date
 * @property int|null $stock
 * @property int $quantity_sold
 * @property bool $is_hidden
 * @property int $sort_order
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Product $product
 * @property int $id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereEndSaleDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereQuantitySold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereStartSaleDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Database\Factories\Domain\ProductCatalog\Models\ProductPriceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice withoutTrashed()
 */
	class ProductPrice extends \Eloquent {}
}

namespace Domain\Ticketing\Models{
/**
 * @property int $id
 * @property string $token
 * @property \Domain\Ticketing\Enums\TicketType $type
 * @property int|null $event_id
 * @property int|null $user_id
 * @property int|null $order_id
 * @property int|null $product_id
 * @property \Domain\Ticketing\Enums\TicketStatus $status
 * @property int $transfers_left
 * @property bool $is_courtesy
 * @property \Illuminate\Support\Carbon|null $used_at
 * @property \Illuminate\Support\Carbon|null $expired_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Domain\EventManagement\Models\Event|null $event
 * @property-read \Domain\Ordering\Models\Order|null $order
 * @property-read \Domain\ProductCatalog\Models\Product|null $product
 * @property-read \App\Models\User|null $user
 * @method static \Domain\Ticketing\Database\Factories\TicketFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereIsCourtesy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereTransfersLeft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket withoutTrashed()
 */
	class Ticket extends \Eloquent {}
}

