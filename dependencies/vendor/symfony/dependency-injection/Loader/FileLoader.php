<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Emoji\Vendor\Symfony\Component\DependencyInjection\Loader;

use Emoji\Vendor\Symfony\Component\Config\FileLocatorInterface;
use Emoji\Vendor\Symfony\Component\Config\Loader\FileLoader as BaseFileLoader;
use Emoji\Vendor\Symfony\Component\Config\Resource\GlobResource;
use Emoji\Vendor\Symfony\Component\DependencyInjection\ChildDefinition;
use Emoji\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
use Emoji\Vendor\Symfony\Component\DependencyInjection\Definition;
use Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
/**
 * FileLoader is the abstract class used by all built-in loaders that are file based.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class FileLoader extends \Emoji\Vendor\Symfony\Component\Config\Loader\FileLoader
{
    protected $container;
    protected $isLoadingInstanceof = \false;
    protected $instanceof = [];
    public function __construct(\Emoji\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder $container, \Emoji\Vendor\Symfony\Component\Config\FileLocatorInterface $locator)
    {
        $this->container = $container;
        parent::__construct($locator);
    }
    /**
     * Registers a set of classes as services using PSR-4 for discovery.
     *
     * @param Definition $prototype A definition to use as template
     * @param string     $namespace The namespace prefix of classes in the scanned directory
     * @param string     $resource  The directory to look for classes, glob-patterns allowed
     * @param string     $exclude   A globed path of files to exclude
     */
    public function registerClasses(\Emoji\Vendor\Symfony\Component\DependencyInjection\Definition $prototype, $namespace, $resource, $exclude = null)
    {
        if ('\\' !== \substr($namespace, -1)) {
            throw new \Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Namespace prefix must end with a "\\": "%s".', $namespace));
        }
        if (!\preg_match('/^(?:[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*+\\\\)++$/', $namespace)) {
            throw new \Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Namespace is not a valid PSR-4 prefix: "%s".', $namespace));
        }
        $classes = $this->findClasses($namespace, $resource, $exclude);
        // prepare for deep cloning
        $serializedPrototype = \serialize($prototype);
        $interfaces = [];
        $singlyImplemented = [];
        foreach ($classes as $class => $errorMessage) {
            if (\interface_exists($class, \false)) {
                $interfaces[] = $class;
            } else {
                $this->setDefinition($class, $definition = \unserialize($serializedPrototype));
                if (null !== $errorMessage) {
                    $definition->addError($errorMessage);
                    continue;
                }
                foreach (\class_implements($class, \false) as $interface) {
                    $singlyImplemented[$interface] = isset($singlyImplemented[$interface]) ? \false : $class;
                }
            }
        }
        foreach ($interfaces as $interface) {
            if (!empty($singlyImplemented[$interface])) {
                $this->container->setAlias($interface, $singlyImplemented[$interface])->setPublic(\false);
            }
        }
    }
    /**
     * Registers a definition in the container with its instanceof-conditionals.
     *
     * @param string $id
     */
    protected function setDefinition($id, \Emoji\Vendor\Symfony\Component\DependencyInjection\Definition $definition)
    {
        $this->container->removeBindings($id);
        if ($this->isLoadingInstanceof) {
            if (!$definition instanceof \Emoji\Vendor\Symfony\Component\DependencyInjection\ChildDefinition) {
                throw new \Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Invalid type definition "%s": ChildDefinition expected, "%s" given.', $id, \get_class($definition)));
            }
            $this->instanceof[$id] = $definition;
        } else {
            $this->container->setDefinition($id, $definition instanceof \Emoji\Vendor\Symfony\Component\DependencyInjection\ChildDefinition ? $definition : $definition->setInstanceofConditionals($this->instanceof));
        }
    }
    private function findClasses($namespace, $pattern, $excludePattern)
    {
        $parameterBag = $this->container->getParameterBag();
        $excludePaths = [];
        $excludePrefix = null;
        if ($excludePattern) {
            $excludePattern = $parameterBag->unescapeValue($parameterBag->resolveValue($excludePattern));
            foreach ($this->glob($excludePattern, \true, $resource, \true) as $path => $info) {
                if (null === $excludePrefix) {
                    $excludePrefix = $resource->getPrefix();
                }
                // normalize Windows slashes
                $excludePaths[\str_replace('\\', '/', $path)] = \true;
            }
        }
        $pattern = $parameterBag->unescapeValue($parameterBag->resolveValue($pattern));
        $classes = [];
        $extRegexp = \defined('Emoji\\Vendor\\HHVM_VERSION') ? '/\\.(?:php|hh)$/' : '/\\.php$/';
        $prefixLen = null;
        foreach ($this->glob($pattern, \true, $resource) as $path => $info) {
            if (null === $prefixLen) {
                $prefixLen = \strlen($resource->getPrefix());
                if ($excludePrefix && 0 !== \strpos($excludePrefix, $resource->getPrefix())) {
                    throw new \Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Invalid "exclude" pattern when importing classes for "%s": make sure your "exclude" pattern (%s) is a subset of the "resource" pattern (%s).', $namespace, $excludePattern, $pattern));
                }
            }
            if (isset($excludePaths[\str_replace('\\', '/', $path)])) {
                continue;
            }
            if (!\preg_match($extRegexp, $path, $m) || !$info->isReadable()) {
                continue;
            }
            $class = $namespace . \ltrim(\str_replace('/', '\\', \substr($path, $prefixLen, -\strlen($m[0]))), '\\');
            if (!\preg_match('/^[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*+(?:\\\\[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*+)*+$/', $class)) {
                continue;
            }
            try {
                $r = $this->container->getReflectionClass($class);
            } catch (\ReflectionException $e) {
                $classes[$class] = $e->getMessage();
                continue;
            }
            // check to make sure the expected class exists
            if (!$r) {
                throw new \Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Expected to find class "%s" in file "%s" while importing services from resource "%s", but it was not found! Check the namespace prefix used with the resource.', $class, $path, $pattern));
            }
            if ($r->isInstantiable() || $r->isInterface()) {
                $classes[$class] = null;
            }
        }
        // track only for new & removed files
        if ($resource instanceof \Emoji\Vendor\Symfony\Component\Config\Resource\GlobResource) {
            $this->container->addResource($resource);
        } else {
            foreach ($resource as $path) {
                $this->container->fileExists($path, \false);
            }
        }
        return $classes;
    }
}
