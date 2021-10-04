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

namespace Splash\Local\Objects\Invoice;

use Splash\Local\Helpers\InvoiceStatusHelper;

/**
 * Magento 2 Invoice Status Access
 */
trait StatusTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildStatusFields(): void
    {
        //====================================================================//
        // Order State
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("state")
            ->name("Status")
            ->microData("http://schema.org/Invoice", "paymentStatus")
            ->isListed()
            ->isReadOnly()
        ;
        //====================================================================//
        // Is Validated
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->identifier("isValidated")
            ->name("Is Valid")
            ->microData("http://schema.org/PaymentStatusType", "PaymentDue")
            ->group("Meta")
            ->isReadOnly()
        ;
        //====================================================================//
        // Is Paid
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("isPaid")
            ->name("Is Paid")
            ->microData("http://schema.org/PaymentStatusType", "PaymentComplete")
            ->group("Meta")
            ->isReadOnly()
        ;
        //====================================================================//
        // Is Canceled
        // => There is no difference between a Canceled & Rejected Payments.
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->identifier("isCanceled")
            ->name("Is Canceled")
            ->microData("http://schema.org/PaymentStatusType", "PaymentDeclined")
            ->group("Meta")
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
    protected function getStatusFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'state':
                $this->out[$fieldName] = InvoiceStatusHelper::toSplash((string) $this->object->getState());

                break;
            case 'isValidated':
                $this->out[$fieldName] = InvoiceStatusHelper::isValidated(
                    InvoiceStatusHelper::toSplash((string) $this->object->getState())
                );

                break;
            case 'isPaid':
                $this->out[$fieldName] = InvoiceStatusHelper::isPaid(
                    InvoiceStatusHelper::toSplash((string) $this->object->getState())
                );

                break;
            case 'isCanceled':
                $this->out[$fieldName] = $this->object->isCanceled();

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }
}
