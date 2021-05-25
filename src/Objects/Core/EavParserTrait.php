<?php


namespace Splash\Local\Objects\Core;

use Exception;
use Splash\Client\Splash;
use Splash\Local\Helpers\AttributesHelper;
use Splash\Local\Helpers\DateHelper;
use Splash\Local\Helpers\EavHelper;
use Splash\Local\Helpers\MageHelper;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Catalog\Model\AbstractModel;

/**
 * Access Model Data from Eav Attributes
 */
trait EavParserTrait
{
    /**
     * List of Eav Model Attributes
     *
     * @var Attribute[]
     */
    protected $eavAttributes;

    /**
     * List of Eav Excluded fields Ids
     *
     * @var array
     */
    protected static $eavExcluded = array(
        "entity_id", "old_id"
    );

//    /**
//     * List of Eav Listed fields Ids
//     *
//     * @var array
//     */
//    protected static $eavListed = array();

//    /**
//     * List of Eav Required fields Ids
//     *
//     * @var array
//     */
//    protected static $eavRequired = array();

//    /**
//     * List of Eav Read Only fields Ids
//     *
//     * @var array
//     */
//    protected static $eavReadOnly = array(
//        "created_at", "updated_at"
//    );

    /**
     * Build Fields using FieldFactory
     */
    protected function buildEavFields(): void
    {
        //====================================================================//
        // Walk on Object Model Attributes
        /** @var Attribute $attribute */
        foreach ($this->getEavAttributes() as $index => $attribute) {
            //====================================================================//
            // Detect Already Defined Fields
            if ($this->fieldsFactory()->has($index)) {
                continue;
            }
            //====================================================================//
            // Detect Attribute Type
            if (!$this->isEavField($index) || !EavHelper::toSplashType($attribute)) {
                continue;
            }
            //====================================================================//
            // Decode Attribute Label
            $type = (string) EavHelper::toSplashType($attribute);
            $name = $attribute->getName();
            $label = $attribute->getDefaultFrontendLabel() ?? ucwords(str_replace("_", " ", $name));
            $descPrefix = "[EAV]".($attribute->getIsUserDefined() ? "[Custom] " : "[S] ");
            //====================================================================//
            // Add Eav Field
            $this->fieldsFactory()->create($type)
                ->identifier($name)
                ->name($label)
                ->description($descPrefix.($attribute->getNote() ?? $label))
                ->isRequired($attribute->getIsRequired())
                ->isReadOnly(EavHelper::isReadOnlyType($type))
            ;
            //====================================================================//
            // Eav Field Options
            $choices = AttributesHelper::getSplashChoices($attribute);
            if (!empty($choices)) {
                $this->fieldsFactory()->addChoices($choices);
            }
        }
    }

    /**
     * Read requested Field
     *
     * @param string $key Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getEavFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if Field is Mapped by Eav
        $attribute = $this->isEavField($fieldName);
        if (!$attribute || !isset($this->in[$key])) {
            return;
        }
        //====================================================================//
        // READ Fields
        try {
            $this->out[$fieldName] = EavHelper::toSplashValue(
                $attribute,
                $this->object->getData($fieldName)
            );
        } catch (Exception $e) {
            Splash::log()->report($e);
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
     */
    protected function setEavFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // Check if Field is Mapped by Eav
        $attribute = $this->isEavField($fieldName);
        if (!$attribute|| !isset($this->in[$fieldName])) {
            return;
        }
        //====================================================================//
        // WRITE Field
        try {
            $current = EavHelper::toSplashValue(
                $attribute,
                $this->object->getData($fieldName)
            );
            if($fieldData != $current) {
                $this->object->setData(
                    $fieldName,
                    EavHelper::toMageValue($attribute, $fieldData)
                );
                $this->needUpdate();
            }
        } catch (Exception $e) {
            Splash::log()->report($e);
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Check if Field is Managed by Eav Parser
     *
     * @param string $fieldName Field Identifier / Name
     *
     * @return Attribute|null
     */
    protected function isEavField(string $fieldName): ?Attribute
    {
        //====================================================================//
        // Field is Excluded
        if (in_array($fieldName, self::$eavExcluded, true)) {
            return null;
        }
        //====================================================================//
        // Attribute not Found
        if (empty($attribute = $this->getEavAttribute($fieldName))) {
            return null;
        }

        return $attribute;
    }

    /**
     * Load Model Eav Attribute
     *
     * @param string $fieldName Field Identifier / Name
     *
     * @return Attribute|null
     */
    protected function getEavAttribute(string $fieldName): ?Attribute
    {
        return $this->getEavAttributes()[$fieldName] ?? null;
    }

    /**
     * Load Model Eav Attributes
     *
     * @return array
     */
    protected function getEavAttributes(): array
    {
        if (!isset($this->eavAttributes)) {
            /** @var AbstractModel $model */
            $model= MageHelper::getModel(self::$modelClass);
            $this->eavAttributes = method_exists($model, "getAttributes")
                ? $model->getAttributes()
                : array();
        }

        return $this->eavAttributes;
    }
}
