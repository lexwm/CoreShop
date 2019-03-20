<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2019 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace CoreShop\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use CoreShop\Behat\Service\SharedStorageInterface;
use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop\Component\Product\Model\ManufacturerInterface;
use CoreShop\Component\Product\Model\ProductUnitInterface;
use CoreShop\Component\Resource\Factory\FactoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pimcore\File;
use Pimcore\Model\DataObject\Service;

final class ProductUnitContext implements Context
{
    /**
     * @var SharedStorageInterface
     */
    private $sharedStorage;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var FactoryInterface
     */
    private $productUnitFactory;

    /**
     * @param SharedStorageInterface $sharedStorage
     * @param ObjectManager          $objectManager
     * @param FactoryInterface       $productUnitFactory
     */
    public function __construct(
        SharedStorageInterface $sharedStorage,
        ObjectManager $objectManager,
        FactoryInterface $productUnitFactory
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->objectManager = $objectManager;
        $this->productUnitFactory = $productUnitFactory;
    }

    /**
     * @Given /^the site has a product-unit "([^"]+)"$/
     */
    public function thereIsAProductUnit($name)
    {
        /**
         * @var ProductUnitInterface $unit
         */
        $unit = $this->productUnitFactory->createNew();

        $unit->setName($name);

        $this->objectManager->persist($unit);
        $this->objectManager->flush();

        $this->sharedStorage->set('product-unit', $unit);
    }
}