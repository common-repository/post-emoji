<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Emoji\Vendor\Symfony\Component\DependencyInjection;

/**
 * ContainerAwareInterface should be implemented by classes that depends on a Container.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface ContainerAwareInterface
{
    /**
     * Sets the container.
     */
    public function setContainer(\Emoji\Vendor\Symfony\Component\DependencyInjection\ContainerInterface $container = null);
}
