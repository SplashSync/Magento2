<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Core;

use Exception;
use Magento\Catalog\Model\AbstractModel;
use Magento\Eav\Model\Entity\Attribute;
use Splash\Client\Splash;
use Splash\Local\Helpers\AttributesHelper;
use Splash\Local\Helpers\EavHelper;
use Splash\Local\Helpers\MageHelper;

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
     * @param string $key       Input List Key
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
                $this->extractData($fieldName)
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
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    protected function setEavFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // Check if Field is Mapped by Eav
        $attribute = $this->isEavField($fieldName);
        if (!$attribute || !isset($this->in[$fieldName])) {
            return;
        }
        //====================================================================//
        // WRITE Field
        try {
            $current = EavHelper::toSplashValue(
                $attribute,
                $this->extractData($fieldName)
            );
            if ($fieldData != $current) {
                $this->object->setData(
                    $fieldName,
                    /** @phpstan-ignore-next-line */
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
     * @return null|Attribute
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
     * @return null|Attribute
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
            $model = MageHelper::getModel(self::$modelClass);
            $this->eavAttributes = method_exists($model, "getAttributes")
                ? $model->getAttributes()
                : array();
        }

        return $this->eavAttributes;
    }

    /**
     * @param string $fieldName
     *
     * @return null|bool|float|int|string
     */
    private function extractData(string $fieldName)
    {
        if (method_exists($this->object, 'getData')) {
            return $this->object->getData($fieldName);
        }

        try {
            $method = "get".ucwords(str_replace("_", "", $fieldName));

            return $this->object->{ $method }();
        } catch (\Throwable $throwable) {
            Splash::log()->err($throwable->getMessage());

            return null;
        }
    }
}
