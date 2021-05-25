<?php


namespace Splash\Local\Objects\Product\Variants;


use ArrayObject;
use Exception;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Catalog\Model\ResourceModel\Product;
use Splash\Local\Helpers\AttributesHelper;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Splash\Client\Splash;

/**
 * Product Variants Attributes Trait
 */
trait AttributesTrait
{
    /**
     * Build Attributes Fields using FieldFactory
     *
     * @return void
     */
    protected function buildVariantsAttributesFields(): void
    {
        //====================================================================//
        // Product Variation Attribute Code (Default Language Only)
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("attribute_code")
            ->name("Attr. Code")
            ->inList("attributes")
            ->group("Attributes")
            ->addOption("isLowerCase", true)
            ->microData("http://schema.org/Product", "VariantAttributeCode")
            ->isNotTested()
        ;
        //====================================================================//
        // Product Variation Attribute Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("frontend_label")
            ->name("Attr. Label")
            ->inList("attributes")
            ->group("Attributes")
            ->microData("http://schema.org/Product", "VariantAttributeName")
            ->isReadOnly()
            ->isNotTested()
        ;
        //====================================================================//
        // Product Variation Attribute Value
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("value")
            ->Name("Attr. Value")
            ->inList("attributes")
            ->group("Attributes")
            ->microData("http://schema.org/Product", "VariantAttributeValue")
            ->isNotTested()
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
    protected function getVariantsAttributesFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "attributes", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Walk on Product Attributes
        foreach ($this->configurableAttributes ?? array() as $index => $attribute) {
            //====================================================================//
            // Read Attribute Data
            switch ($fieldId) {
                case 'attribute_code':
                case 'frontend_label':
                    $value = $attribute[$fieldId];

                    break;
                case 'value':
                    $value = $this->object->getAttributeText($attribute['attribute_code']);

                    break;
                default:
                    return;
            }

            self::lists()->insert($this->out, "attributes", $fieldId, $index, $value);
        }
        unset($this->in[$key]);
    }

    //====================================================================//
    // Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    protected function setVariantsAttributesFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // Variants Infos are Only Used on Create
        if ($fieldName != "attributes") {
            return;
        }
        //====================================================================//
        // Safety Check
        if (!empty($fieldData) && empty($this->parent)) {
            Splash::log()->err("You try to set Attributes of non Configurable Product");

            return;
        }
        if (is_iterable($fieldData)) {
            $fieldData = ($fieldData instanceof ArrayObject) ? $fieldData->getArrayCopy() : (array) $fieldData;
            //====================================================================//
            // Update Product Configurable Attributes
            if($this->updateUsedAttributesIds($fieldData)) {
                $this->updateAttributesValues($fieldData);
            }
        }

        unset($this->in[$fieldName]);
    }

    //====================================================================//
    // Fields Writing Functions
    //====================================================================//

    /**
     * Update Parent Product Attributes Associations
     *
     * @param array  $fieldData Field Data
     *
     * @return bool
     */
    private function updateUsedAttributesIds(array $fieldData): bool
    {
        //====================================================================//
        // Safety Check
        if (!$this->parent) {
            return true;
        }
        /** @var Product $resource */
        $resource = $this->parent->getResource();
        //====================================================================//
        // Walk on New Product Attributes
        $newAttrIds = array();
        foreach ($fieldData as $attrArray) {
            //====================================================================//
            // Identify Product Attribute
            $attribute = $resource->getAttribute($attrArray['attribute_code']);
            //====================================================================//
            // Attribute not Found => Cancel Update !!
            if (!$attribute) {
                return Splash::log()->err(
                    sprintf("Attribute %s was not found on Magento, update skipped.", $attrArray['attribute_code'])
                );
            }
            //====================================================================//
            // Attribute Found
            $newAttrIds[] = $attribute->getId();
        }
        //====================================================================//
        // Compare Attributes Ids
        if (empty(array_diff($newAttrIds, array_keys($this->configurableAttributes ?? array())))) {
            return true;
        }
        //====================================================================//
        // Update Used Attributes Ids
        /** @var ConfigurableProduct $typeInstance */
        $typeInstance = $this->parent->getTypeInstance();
        $typeInstance->setUsedProductAttributeIds($newAttrIds, $this->parent);
        $configurableAttributesData = $typeInstance->getConfigurableAttributesAsArray($this->parent);
        /** @phpstan-ignore-next-line */
        $this->parent
            ->setCanSaveConfigurableAttributes(true)
            ->setConfigurableAttributesData($configurableAttributesData)
        ;
        $this->parent->save();

        return true;
    }

    /**
     * Update Parent Product Attributes Associations
     *
     * @param array $fieldData Field Data
     *
     * @return bool
     * @throws Exception
     */
    private function updateAttributesValues(array $fieldData): bool
    {
        /** @var Product $resource */
        $resource = $this->object->getResource();
        //====================================================================//
        // Walk on New Product Attributes
        foreach ($fieldData as $attrArray) {
            //====================================================================//
            // Identify Product Attribute
            /** @var Attribute $attribute */
            $attribute = $resource->getAttribute($attrArray['attribute_code']);
            //====================================================================//
            // Attribute not Found => Cancel Update !!
            if (empty($attribute)) {
                return Splash::log()->err(
                    sprintf("Attribute %s was not found on Magento, update skipped.", $attrArray['attribute_code'])
                );
            }
            //====================================================================//
            // Compare Attribute Values
            $value = $this->object->getAttributeText($attrArray['attribute_code']);
            if ($value == $attrArray['value']) {
                return false;
            }
            //====================================================================//
            // Identify New Attribute Value Id
            if ($attribute->usesSource()) {
                $attributeValue = AttributesHelper::getValueFromLabel($attribute, $attrArray['value']);
                //====================================================================//
                // Attribute not Found => Cancel Update !!
                if (!$attributeValue) {
                    Splash::log()->err(
                        sprintf("Attribute value %s was not found for %s, update skipped.", $attrArray['value'], $attrArray['attribute_code'])
                    );

                    continue;
                }
                $attrArray['value'] = $attributeValue;
            }
            //====================================================================//
            // Attribute Found
            $this->object->setData($attrArray['attribute_code'], (string) $attrArray['value']);
            $this->needUpdate();
        }

        return true;
    }
}
