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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read Organizer $organizer
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
 * @property int $event_category_id
 * @property-read \Domain\EventManagement\Models\EventCategory|null $category
 * @method static \Domain\EventManagement\Database\Factories\EventFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereEventCategoryId($value)
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
 * @property string $reference_id
 * @property string|null $organizer_raise_method_snapshot
 * @property string|null $used_payment_gateway_snapshot
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrganizerRaiseMethodSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUsedPaymentGatewaySnapshot($value)
 */
	class Order extends \Eloquent {}
}

namespace Domain\Ordering\Models{
/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int|null $quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
 * @property string $unit_price
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereUnitPrice($value)
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
 */
	class Organizer extends \Eloquent {}
}

namespace Domain\OrganizerManagement\Models{
/**
 * @property-read \Domain\OrganizerManagement\Models\Organizer|null $organizer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $organizer_id
 * @property string $raise_money_method
 * @property string|null $raise_money_account
 * @property int $is_modo_active
 * @property int $is_mercadopago_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereIsMercadopagoActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereIsModoActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereOrganizerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereRaiseMoneyAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereRaiseMoneyMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings whereUpdatedAt($value)
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
 * @method static \Domain\ProductCatalog\Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductPriceType($value)
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
 */
	class ProductPrice extends \Eloquent {}
}

