<?php


namespace Splash\Local\Objects\Product;

use Splash\Local\Helpers\MageHelper;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\CatalogInventory\Model\Stock\Item;

/**
 * Product Stock Fields
 */
trait StockTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildStockFields(): void
    {
        //====================================================================//
        // Stock Reel
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("quantity_and_stock_status")
            ->name("Stock")
            ->microData("http://schema.org/Offer", "inventoryLevel")
        ;
        //====================================================================//
        // Out of Stock Flag
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("is_in_stock")
            ->name("This product is out of stock")
            ->microData("http://schema.org/ItemAvailability", "OutOfStock")
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
    protected function getStockFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'quantity_and_stock_status':
                // $this->out[$fieldName] = $this->object->getExtensionAttributes()->getStockItem()->getQty();
                $stockItem = self::getStockItem($this->object->getEntityId());
                $this->out[$fieldName] = $stockItem ? (int) $stockItem->getQty() : 0;

                break;

            case 'is_in_stock':
                $stockItem = self::getStockItem($this->object->getEntityId());
                $this->out[$fieldName] = !$stockItem || !$stockItem->getIsInStock();

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
    protected function setStockFields(string $fieldName, $data): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'quantity_and_stock_status':
                $stockItem = self::getStockItem($this->object->getEntityId());
                if ($stockItem && ($stockItem->getQty() == $data)) {
                    break;
                }
                $this->object->setQuantityAndStockStatus(array(
                    'qty' => (int) $data,
                    'is_in_stock' => empty($data) ? 0 : 1,
                ));
                $this->needUpdate();

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Read Product Stock Item
     *
     * @param int $productId
     *
     * @return null|Item
     */
    protected static function getStockItem(int $productId): ?Item
    {
        /** @var null|StockItemRepository $registry */
        static $registry;

        if (!$registry) {
            /** @var StockItemRepository $registry */
            $registry = MageHelper::getModel(StockItemRepository::class);
        }

        /** @phpstan-ignore-next-line */
        return $registry->get($productId) ?: null;
    }

}
