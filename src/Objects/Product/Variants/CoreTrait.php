<?php


namespace Splash\Local\Objects\Product\Variants;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product;
use Splash\Local\Helpers\MageHelper;
use Splash\Client\Splash;

/**
 *  Core Functions to Access Products Variants Informations
 */
trait CoreTrait
{
    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * Identify Parent Product
     *
     * @param int|null $productId
     * @return null|Product
     */
    protected function getParentProduct(?int $productId): ?Product
    {
        //====================================================================//
        // Product is New
        if (empty($productId)) {
            return null;
        }
        //====================================================================//
        // Load List of Parents Products
        $parents = $this->getConfigurableManager()->getParentIdsByChild($productId);
        if (empty($parents)) {
            return null;
        }
        //====================================================================//
        // Load Parent Product
        try {
            /** @phpstan-ignore-next-line */
            return $this->repository->getById((int) array_shift($parents));
        } catch (\Throwable $exception) {
            Splash::log()->err($exception->getMessage());
        }

        return null;
    }

    /**
     * Get Configurable Products Children Ids
     *
     * @param int|null $productId
     *
     * @return array
     */
    protected function getChildrenIds(?int $productId): array
    {
        //====================================================================//
        // Product is New
        if (empty($productId)) {
            return array();
        }
        //====================================================================//
        // Load Parent Product Variants Ids
        $childrenIds = $this->getConfigurableManager()->getChildrenIds($productId);

        return array_shift($childrenIds) ?: array();
    }

    /**
     * Get Configurable Products Manager
     *
     * @return Configurable
     */
    protected function getConfigurableManager(): Configurable {
        if (!isset($this->configurable)) {
            /** @var Configurable $configurable */
            $configurable = MageHelper::getModel(Configurable::class);
            $this->configurable = $configurable;
        }

        return $this->configurable;
    }


}
