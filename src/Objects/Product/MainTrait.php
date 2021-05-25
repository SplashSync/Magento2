<?php


namespace Splash\Local\Objects\Product;

use Splash\Local\Helpers\MageHelper;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\CatalogInventory\Model\Stock\Item;
use Splash\Components\UnitConverter as Units;
use Splash\Client\Splash;

/**
 * Product Main Fields
 */
trait MainTrait
{
    /**
     * @var array
     */
    private static $mageDims = array(
        "lbs" => Units::LENGTH_INCH,
        "kgs" => Units::LENGTH_CM,
    );

    /**
     * @var array
     */
    private static $mageWeight = array(
        "lbs" => Units::MASS_LIVRE,
        "kgs" => Units::MASS_KG,
    );

    /**
     * Build Fields using FieldFactory
     */
    protected function buildMainFields(): void
    {
        //====================================================================//
        // Weight
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("weight")
            ->name("Weight")
            ->group('Shipping')
            ->microData("http://schema.org/Product", "weight")
        ;
        //====================================================================//
        // Height
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("ts_dimensions_height")
            ->name("Package height")
            ->group('Shipping')
            ->microData("http://schema.org/Product", "height")
        ;
        //====================================================================//
        // Depth
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("ts_dimensions_length")
            ->Name("Package depth")
            ->group('Shipping')
            ->microData("http://schema.org/Product", "depth")
        ;
        //====================================================================//
        // Width
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("ts_dimensions_width")
            ->Name("Package width")
            ->group('Shipping')
            ->microData("http://schema.org/Product", "width")
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getMainFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'weight':
                //====================================================================//
                //  Return Normalized Weight
                $this->out[$fieldName] = self::units()->normalizeWeight(
                    (float) $this->object->getData($fieldName),
                    static::$mageWeight[self::getStoreUnit()] ?: Units::MASS_KG
                );

                break;

            case 'ts_dimensions_length':
            case 'ts_dimensions_width':
            case 'ts_dimensions_height':
                //====================================================================//
                //  Return Normalized Dimension
                $this->out[$fieldName] = self::units()->normalizeLength(
                    (float) $this->object->getData($fieldName),
                    static::$mageDims[self::getStoreUnit()] ?: Units::LENGTH_CM
                );

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $data      Field Data
     *
     * @return void
     */
    protected function setMainFields(string $fieldName, $data): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'weight':
                $current = $this->object->getData($fieldName);
                //====================================================================//
                //  Compute Normalized Weight
                $new = self::units()->convertWeight(
                    (float) $data,
                    static::$mageWeight[self::getStoreUnit()] ?: Units::MASS_KG
                );
                if( abs($new - $current) < 1E-3) {
                    break;
                }
                $this->setGeneric($fieldName, $new);

                break;

            case 'ts_dimensions_length':
            case 'ts_dimensions_width':
            case 'ts_dimensions_height':
                $current = $this->object->getData($fieldName);
                //====================================================================//
                //  Compute Normalized Length
                $new = self::units()->convertLength(
                    (float) $data,
                    static::$mageDims[self::getStoreUnit()] ?: Units::LENGTH_CM
                );
                if( abs($new - $current) < 1E-3) {
                    break;
                }
                $this->setGeneric($fieldName, $new);

                break;

            default:
                return;
        }
        unset($this->in[$fieldName]);
    }


    /**
     * Get Store default Unit
     *
     * @return null|string
     */
    protected static function getStoreUnit(): ?string
    {
        /** @var null|string $unit */
        static $unit;

        if (!$unit) {
            $unit = MageHelper::getStoreConfig("general/locale/weight_unit") ?: "kgs";
        }

        return $unit;
    }
}
