<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ProductCatalog\Enums;

use Domain\ProductCatalog\Enums\ProductType;
use PHPUnit\Framework\TestCase;

class ProductTypeTest extends TestCase
{
    /** @test */
    public function it_has_general_type(): void
    {
        $type = ProductType::GENERAL;

        $this->assertEquals('general', $type->value);
        $this->assertInstanceOf(ProductType::class, $type);
    }

    /** @test */
    public function it_has_ticket_type(): void
    {
        $type = ProductType::TICKET;

        $this->assertEquals('ticket', $type->value);
        $this->assertInstanceOf(ProductType::class, $type);
    }

    /** @test */
    public function it_can_be_created_from_value(): void
    {
        $type = ProductType::from('general');

        $this->assertEquals(ProductType::GENERAL, $type);
    }

    /** @test */
    public function it_can_retrieve_all_cases(): void
    {
        $cases = ProductType::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(ProductType::GENERAL, $cases);
        $this->assertContains(ProductType::TICKET, $cases);
    }

    /** @test */
    public function it_can_be_compared(): void
    {
        $type1 = ProductType::GENERAL;
        $type2 = ProductType::GENERAL;
        $type3 = ProductType::TICKET;

        $this->assertTrue($type1 === $type2);
        $this->assertFalse($type1 === $type3);
    }
}