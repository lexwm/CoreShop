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

namespace CoreShop\Component\Customer\Model;

use CoreShop\Component\Resource\Pimcore\Model\AbstractPimcoreModel;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class Company extends AbstractPimcoreModel implements CompanyInterface
{
    public function isEqualTo(UserInterface $user)
    {
        return $user instanceof self && $user->getId() === $this->getId();
    }
}
