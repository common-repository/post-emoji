<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler;

use Emoji\Vendor\Symfony\Component\DependencyInjection\ContainerInterface;
use Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Emoji\Vendor\Symfony\Component\DependencyInjection\Reference;
/**
 * Checks that all references are pointing to a valid service.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class CheckExceptionOnInvalidReferenceBehaviorPass extends \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass
{
    protected function processValue($value, $isRoot = \false)
    {
        if (!$value instanceof \Emoji\Vendor\Symfony\Component\DependencyInjection\Reference) {
            return parent::processValue($value, $isRoot);
        }
        if (\Emoji\Vendor\Symfony\Component\DependencyInjection\ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE === $value->getInvalidBehavior() && !$this->container->has($id = (string) $value)) {
            throw new \Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException($id, $this->currentId);
        }
        return $value;
    }
}
