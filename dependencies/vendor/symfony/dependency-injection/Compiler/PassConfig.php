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

use Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
/**
 * Compiler Pass Configuration.
 *
 * This class has a default configuration embedded.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class PassConfig
{
    const TYPE_AFTER_REMOVING = 'afterRemoving';
    const TYPE_BEFORE_OPTIMIZATION = 'beforeOptimization';
    const TYPE_BEFORE_REMOVING = 'beforeRemoving';
    const TYPE_OPTIMIZE = 'optimization';
    const TYPE_REMOVE = 'removing';
    private $mergePass;
    private $afterRemovingPasses = [];
    private $beforeOptimizationPasses = [];
    private $beforeRemovingPasses = [];
    private $optimizationPasses;
    private $removingPasses;
    public function __construct()
    {
        $this->mergePass = new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationPass();
        $this->beforeOptimizationPasses = [100 => [$resolveClassPass = new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\RegisterEnvVarProcessorsPass()], -1000 => [new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ExtensionCompilerPass()]];
        $this->optimizationPasses = [[new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ResolveChildDefinitionsPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\RegisterServiceSubscribersPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\DecoratorServicePass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ResolveParameterPlaceHoldersPass(\false, \false), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ResolveFactoryClassPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\FactoryReturnTypePass($resolveClassPass), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\CheckDefinitionValidityPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\AutowireRequiredMethodsPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\AutowirePass(\false), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ResolveTaggedIteratorArgumentPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ResolveServiceSubscribersPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ResolveReferencesToAliasesPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ResolveInvalidReferencesPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass(\true), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\CheckCircularReferencesPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\CheckReferenceValidityPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\CheckArgumentsValidityPass(\false)]];
        $this->beforeRemovingPasses = [-100 => [new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ResolvePrivatesPass()]];
        $this->removingPasses = [[new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\RemovePrivateAliasesPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ReplaceAliasByActualDefinitionPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\RemoveAbstractDefinitionsPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\RepeatedPass([new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\InlineServiceDefinitionsPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\RemoveUnusedDefinitionsPass()]), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\DefinitionErrorExceptionPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\CheckExceptionOnInvalidReferenceBehaviorPass(), new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ResolveHotPathPass()]];
    }
    /**
     * Returns all passes in order to be processed.
     *
     * @return CompilerPassInterface[]
     */
    public function getPasses()
    {
        return \array_merge([$this->mergePass], $this->getBeforeOptimizationPasses(), $this->getOptimizationPasses(), $this->getBeforeRemovingPasses(), $this->getRemovingPasses(), $this->getAfterRemovingPasses());
    }
    /**
     * Adds a pass.
     *
     * @param CompilerPassInterface $pass A Compiler pass
     * @param string                $type The pass type
     *
     * @throws InvalidArgumentException when a pass type doesn't exist
     */
    public function addPass(\Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface $pass, $type = self::TYPE_BEFORE_OPTIMIZATION)
    {
        if (\func_num_args() >= 3) {
            $priority = \func_get_arg(2);
        } else {
            if (__CLASS__ !== static::class) {
                $r = new \ReflectionMethod($this, __FUNCTION__);
                if (__CLASS__ !== $r->getDeclaringClass()->getName()) {
                    @\trigger_error(\sprintf('Method %s() will have a third `int $priority = 0` argument in version 4.0. Not defining it is deprecated since Symfony 3.2.', __METHOD__), \E_USER_DEPRECATED);
                }
            }
            $priority = 0;
        }
        $property = $type . 'Passes';
        if (!isset($this->{$property})) {
            throw new \Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Invalid type "%s".', $type));
        }
        $passes =& $this->{$property};
        if (!isset($passes[$priority])) {
            $passes[$priority] = [];
        }
        $passes[$priority][] = $pass;
    }
    /**
     * Gets all passes for the AfterRemoving pass.
     *
     * @return CompilerPassInterface[]
     */
    public function getAfterRemovingPasses()
    {
        return $this->sortPasses($this->afterRemovingPasses);
    }
    /**
     * Gets all passes for the BeforeOptimization pass.
     *
     * @return CompilerPassInterface[]
     */
    public function getBeforeOptimizationPasses()
    {
        return $this->sortPasses($this->beforeOptimizationPasses);
    }
    /**
     * Gets all passes for the BeforeRemoving pass.
     *
     * @return CompilerPassInterface[]
     */
    public function getBeforeRemovingPasses()
    {
        return $this->sortPasses($this->beforeRemovingPasses);
    }
    /**
     * Gets all passes for the Optimization pass.
     *
     * @return CompilerPassInterface[]
     */
    public function getOptimizationPasses()
    {
        return $this->sortPasses($this->optimizationPasses);
    }
    /**
     * Gets all passes for the Removing pass.
     *
     * @return CompilerPassInterface[]
     */
    public function getRemovingPasses()
    {
        return $this->sortPasses($this->removingPasses);
    }
    /**
     * Gets the Merge pass.
     *
     * @return CompilerPassInterface
     */
    public function getMergePass()
    {
        return $this->mergePass;
    }
    public function setMergePass(\Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface $pass)
    {
        $this->mergePass = $pass;
    }
    /**
     * Sets the AfterRemoving passes.
     *
     * @param CompilerPassInterface[] $passes
     */
    public function setAfterRemovingPasses(array $passes)
    {
        $this->afterRemovingPasses = [$passes];
    }
    /**
     * Sets the BeforeOptimization passes.
     *
     * @param CompilerPassInterface[] $passes
     */
    public function setBeforeOptimizationPasses(array $passes)
    {
        $this->beforeOptimizationPasses = [$passes];
    }
    /**
     * Sets the BeforeRemoving passes.
     *
     * @param CompilerPassInterface[] $passes
     */
    public function setBeforeRemovingPasses(array $passes)
    {
        $this->beforeRemovingPasses = [$passes];
    }
    /**
     * Sets the Optimization passes.
     *
     * @param CompilerPassInterface[] $passes
     */
    public function setOptimizationPasses(array $passes)
    {
        $this->optimizationPasses = [$passes];
    }
    /**
     * Sets the Removing passes.
     *
     * @param CompilerPassInterface[] $passes
     */
    public function setRemovingPasses(array $passes)
    {
        $this->removingPasses = [$passes];
    }
    /**
     * Sort passes by priority.
     *
     * @param array $passes CompilerPassInterface instances with their priority as key
     *
     * @return CompilerPassInterface[]
     */
    private function sortPasses(array $passes)
    {
        if (0 === \count($passes)) {
            return [];
        }
        \krsort($passes);
        // Flatten the array
        return \call_user_func_array('array_merge', $passes);
    }
}
