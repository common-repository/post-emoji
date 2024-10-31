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

use Emoji\Vendor\Symfony\Component\DependencyInjection\Definition;
use Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\RuntimeException;
/**
 * Throws an exception for any Definitions that have errors and still exist.
 *
 * @author Ryan Weaver <ryan@knpuniversity.com>
 */
class DefinitionErrorExceptionPass extends \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass
{
    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = \false)
    {
        if (!$value instanceof \Emoji\Vendor\Symfony\Component\DependencyInjection\Definition || empty($value->getErrors())) {
            return parent::processValue($value, $isRoot);
        }
        // only show the first error so the user can focus on it
        $errors = $value->getErrors();
        $message = \reset($errors);
        throw new \Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\RuntimeException($message);
    }
}
