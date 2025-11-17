<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ProductCatalog\Enums;

use Domain\ProductCatalog\Enums\ProductPriceType;
use PHPUnit\Framework\TestCase;

class ProductPriceTypeTest extends TestCase
{
    /** @test */
    public function it_has_standard_type(): void
    {
        $type = ProductPriceType::STANDARD;

        $this->assertEquals('standard', $type->value);
        $this->assertInstanceOf(ProductPriceType::class, $type);
    }

    /** @test */
    public function it_has_free_type(): void
    {
        $type = ProductPriceType::FREE;

        $this->assertEquals('free', $type->value);
        $this->assertInstanceOf(ProductPriceType::class, $type);
    }

    /** @test */
    public function it_has_staggered_type(): void
    {
        $type = ProductPriceType::STAGGERED;

        $this->assertEquals('staggered', $type->value);
        $this->assertInstanceOf(ProductPriceType::class, $type);
    }

    /** @test */
    public function it_can_be_created_from_value(): void
    {
        $type = ProductPriceType::from('standard');

        $this->assertEquals(ProductPriceType::STANDARD, $type);
    }

    /** @test */
    public function it_can_retrieve_all_cases(): void
    {
        $cases = ProductPriceType::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(ProductPriceType::STANDARD, $cases);
        $this->assertContains(ProductPriceType::FREE, $cases);
        $this->assertContains(ProductPriceType::STAGGERED, $cases);
    }

    /** @test */
    public function it_can_be_compared(): void
    {
        $type1 = ProductPriceType::STANDARD;
        $type2 = ProductPriceType::STANDARD;
        $type3 = ProductPriceType::FREE;

        $this->assertTrue($type1 === $type2);
        $this->assertFalse($type1 === $type3);
    }

    /** @test */
    public function it_can_try_from_with_valid_value(): void
    {
        $type = ProductPriceType::tryFrom('free');

        $this->assertNotNull($type);
        $this->assertEquals(ProductPriceType::FREE, $type);
    }

    /** @test */
    public function it_returns_null_for_invalid_value(): void
    {
        $type = ProductPriceType::tryFrom('invalid');

        $this->assertNull($type);
    }
}
