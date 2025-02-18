<?php

declare(strict_types=1);

/*
 * CoreShop
 *
 * This source file is available under two different licenses:
 *  - GNU General Public License version 3 (GPLv3)
 *  - CoreShop Commercial License (CCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GPLv3 and CCL
 *
 */

namespace CoreShop\Bundle\TrackingBundle\Tracker\Google\TagManager;

use CoreShop\Bundle\TrackingBundle\Resolver\ConfigResolverInterface;
use CoreShop\Bundle\TrackingBundle\Tracker\AbstractEcommerceTracker;
use Pimcore\Analytics\TrackerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagManagerClassicEcommerce extends AbstractEcommerceTracker
{
    public CodeTracker $codeTracker;

    public ConfigResolverInterface $config;

    protected bool $dataLayerIncluded = false;

    public function setTracker(TrackerInterface $tracker): void
    {
        // not implemented in GTM. Use CodeTracker instead.
    }

    public function setCodeTracker(CodeTracker $tracker): void
    {
        $this->codeTracker = $tracker;
    }

    public function getCodeTracker(): CodeTracker
    {
        return $this->codeTracker;
    }

    public function setConfigResolver(ConfigResolverInterface $config): void
    {
        $this->config = $config;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'template_prefix' => '@CoreShopTracking/Tracking/gtm/classic',
        ]);
    }

    public function trackProduct($product): void
    {
        // not implemented
    }

    public function trackProductImpression($product): void
    {
        // not implemented
    }

    public function trackCartAdd($cart, $product, float $quantity = 1.0): void
    {
        // not implemented
    }

    public function trackCartRemove($cart, $product, float $quantity = 1.0): void
    {
        // not implemented
    }

    public function trackCheckoutStep($cart, $stepIdentifier = null, bool $isFirstStep = false, $checkoutOption = null): void
    {
        // not implemented
    }

    public function trackCheckoutComplete($order): void
    {
        $this->ensureDataLayer();

        $items = $order['items'];

        $actionData = array_merge($this->transformOrder($order), ['transactionProducts' => []]);

        foreach ($items as $item) {
            $actionData['transactionProducts'][] = $this->transformProductAction($item);
        }

        $parameters = [];
        $parameters['actionData'] = $actionData;

        $result = $this->renderTemplate('checkout_complete', $parameters);
        $this->codeTracker->addCodePart($result);
    }

    protected function transformOrder(array $actionData): array
    {
        return [
            'transactionId' => $actionData['id'],
            'transactionAffiliation' => $actionData['affiliation'] ?: '',
            'transactionTotal' => $actionData['total'],
            'transactionTax' => $actionData['totalTax'],
            'transactionShipping' => $actionData['shipping'],
            'transactionCurrency' => $actionData['currency'],
        ];
    }

    protected function transformProductAction(array $item): array
    {
        return $this->filterNullValues([
            'id' => $item['id'],
            'sku' => $item['sku'],
            'name' => $item['name'],
            'category' => $item['category'],
            'price' => round($item['price'], 2),
            'quantity' => $item['quantity'],
        ]);
    }

    protected function ensureDataLayer(): void
    {
        if ($this->dataLayerIncluded) {
            return;
        }

        $result = $this->renderTemplate('data_layer', []);
        $this->codeTracker->addCodePart($result);
        $this->dataLayerIncluded = true;
    }
}
