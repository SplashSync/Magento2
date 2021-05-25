<?php


namespace Splash\Local\Objects\Product;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product;
use Splash\Local\Configurators\ProductConfigurator;
use Splash\Local\Helpers\MageHelper;
use Splash\Client\Splash;

/**
 *  Core Products Fields (required)
 */
trait CoreTrait
{
    /**
     * Build Core Fields using FieldFactory
     */
    protected function buildCoreFields(): void
    {
        //====================================================================//
        // Register Product Configurator
        $this->fieldsFactory()->registerConfigurator(
            "Product",
            new ProductConfigurator()
        );
        //====================================================================//
        // Reference
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("sku")
            ->name('Reference - SKU')
            ->isListed()
            ->microData("http://schema.org/Product", "model")
            ->isRequired();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getCoreFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // MAIN INFORMATIONS
            //====================================================================//
            case 'sku':
                $this->getGeneric($fieldName);

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
    protected function setCoreFields(string $fieldName, $data): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // MAIN INFORMATIONS
            //====================================================================//
            case 'sku':
                $this->setGeneric($fieldName, $data);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }



}
