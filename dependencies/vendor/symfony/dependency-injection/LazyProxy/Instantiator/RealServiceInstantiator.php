<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Emoji\Vendor\Symfony\Component\DependencyInjection\LazyProxy\Instantiator;

use Emoji\Vendor\Symfony\Component\DependencyInjection\ContainerInterface;
use Emoji\Vendor\Symfony\Component\DependencyInjection\Definition;
/**
 * {@inheritdoc}
 *
 * Noop proxy instantiator - produces the real service instead of a proxy instance.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class RealServiceInstantiator implements \Emoji\Vendor\Symfony\Component\DependencyInjection\LazyProxy\Instantiator\InstantiatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function instantiateProxy(\Emoji\Vendor\Symfony\Component\DependencyInjection\ContainerInterface $container, \Emoji\Vendor\Symfony\Component\DependencyInjection\Definition $definition, $id, $realInstantiator)
    {
        return \call_user_func($realInstantiator);
    }
}
