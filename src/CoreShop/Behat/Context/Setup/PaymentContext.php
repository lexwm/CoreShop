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

namespace CoreShop\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Carbon\Carbon;
use CoreShop\Behat\Service\SharedStorageInterface;
use CoreShop\Component\Core\Model\OrderInterface;
use CoreShop\Component\Core\Model\PaymentInterface;
use CoreShop\Component\Core\Model\PaymentProviderInterface;
use CoreShop\Component\PayumPayment\Model\GatewayConfig;
use CoreShop\Component\Resource\Factory\FactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pimcore\Tool;

final class PaymentContext implements Context
{
    public function __construct(
        private SharedStorageInterface $sharedStorage,
        private EntityManagerInterface $entityManager,
        private FactoryInterface $paymentFactory,
        private FactoryInterface $paymentProviderFactory,
        private FactoryInterface $gatewayConfigFactory,
    ) {
    }

    /**
     * @Given /^There is a payment provider "([^"]+)" using factory "([^"]+)"$/
     * @Given /^the site has a payment provider "([^"]+)" using factory "([^"]+)"$/
     */
    public function thereIsAPaymentProviderUsingFactory($name, $factory): void
    {
        /**
         * @var PaymentProviderInterface $paymentProvider
         */
        $paymentProvider = $this->paymentProviderFactory->createNew();
        /**
         * @var GatewayConfig $gatewayConfig
         */
        $gatewayConfig = $this->gatewayConfigFactory->createNew();

        foreach (Tool::getValidLanguages() as $lang) {
            $paymentProvider->setTitle($name, $lang);
        }

        $gatewayConfig->setFactoryName($factory);
        $gatewayConfig->setGatewayName($name);
        $paymentProvider->setGatewayConfig($gatewayConfig);
        $paymentProvider->setIdentifier($name);
        $paymentProvider->addStore($this->sharedStorage->get('store'));
        $paymentProvider->setActive(true);

        $this->entityManager->persist($gatewayConfig);
        $this->entityManager->persist($paymentProvider);
        $this->entityManager->flush();

        $this->sharedStorage->set('payment-provider', $paymentProvider);
    }

    /**
     * @Given /^I create a payment for (my order) with (payment provider "[^"]+") and amount ([^"]+)$/
     * @Given /^I create a payment for (my order) with (payment provider "[^"]+")$/
     */
    public function iCreateAPaymentForOrderWithProviderAndAmount(OrderInterface $order, PaymentProviderInterface $paymentProvider, $amount = null): void
    {
        /**
         * @var PaymentInterface $payment
         */
        $payment = $this->paymentFactory->createNew();
        $payment->setCurrency($order->getBaseCurrency());
        $payment->setNumber($order->getId());
        $payment->setPaymentProvider($paymentProvider);
        $payment->setTotalAmount($amount ?? $order->getPaymentTotal());
        $payment->setState(PaymentInterface::STATE_NEW);
        $payment->setDatePayment(Carbon::now());
        $payment->setOrder($order);

        $this->entityManager->persist($payment->getCurrency());
        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        $this->sharedStorage->set('orderPayment', $payment);
    }
}
