<?php


namespace Splash\Local\Objects\Product;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product;
use Splash\Local\Helpers\EavHelper;
use Splash\Local\Helpers\MageHelper;
use Splash\Client\Splash;

/**
 *  Access Products Metadata Informations
 */
trait MetadataTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildMetadataFields()
    {
        //====================================================================//
        // Active => Product Is available_for_order
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("status")
            ->name("Enable Product")
            ->microData("http://schema.org/Product", "offered")
        ;
        //====================================================================//
        // Type
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("type_id")
            ->name("Type")
            ->description('Internal Type')
            ->group("Meta")
            ->microData("http://schema.org/Product", "type")
            ->isReadOnly()
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
    protected function getMetadataFields($key, $fieldName)
    {
        //====================================================================//
        // Read Attribute Data
        switch ($fieldName) {
            case 'status':
                $this->out[$fieldName] = (1 == $this->object->getData($fieldName));

//                Splash::log()->www($fieldName, $this->object->getData($fieldName));

                break;
            case 'type_id':
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
     * @param mixed  $fieldData      Field Data
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function setMetadataFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // PRODUCT MULTI-LANG CONTENTS
            //====================================================================//
            case 'status':
                if($fieldData != $this->object->getData($fieldName)) {
                    $this->object->setStatus((int) $fieldData);
                    $this->needUpdate();
                }

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
