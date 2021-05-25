<?php


namespace Splash\Local\Objects\Product;


use Splash\Local\Objects\Product\Variants;

trait VariantsTrait
{
    use Variants\CoreTrait;
    use Variants\CRUDTrait;
    use Variants\VariantTrait;
    use Variants\AttributesTrait;
}