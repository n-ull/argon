<?php

namespace Domain\ProductCatalog\Enums;

enum ProductPriceType : string
{
    case STANDARD = "standard";
    case FREE = "free";
    case STAGGERED = "staggered";
}
