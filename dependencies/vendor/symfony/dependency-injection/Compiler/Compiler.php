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

use Emoji\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
use Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\EnvParameterException;
/**
 * This class is used to remove circular dependencies between individual passes.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Compiler
{
    private $passConfig;
    private $log = [];
    private $loggingFormatter;
    private $serviceReferenceGraph;
    public function __construct()
    {
        $this->passConfig = new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\PassConfig();
        $this->serviceReferenceGraph = new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraph();
    }
    /**
     * Returns the PassConfig.
     *
     * @return PassConfig The PassConfig instance
     */
    public function getPassConfig()
    {
        return $this->passConfig;
    }
    /**
     * Returns the ServiceReferenceGraph.
     *
     * @return ServiceReferenceGraph The ServiceReferenceGraph instance
     */
    public function getServiceReferenceGraph()
    {
        return $this->serviceReferenceGraph;
    }
    /**
     * Returns the logging formatter which can be used by compilation passes.
     *
     * @return LoggingFormatter
     *
     * @deprecated since version 3.3, to be removed in 4.0. Use the ContainerBuilder::log() method instead.
     */
    public function getLoggingFormatter()
    {
        if (null === $this->loggingFormatter) {
            @\trigger_error(\sprintf('The %s() method is deprecated since Symfony 3.3 and will be removed in 4.0. Use the ContainerBuilder::log() method instead.', __METHOD__), \E_USER_DEPRECATED);
            $this->loggingFormatter = new \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\LoggingFormatter();
        }
        return $this->loggingFormatter;
    }
    /**
     * Adds a pass to the PassConfig.
     *
     * @param CompilerPassInterface $pass A compiler pass
     * @param string                $type The type of the pass
     */
    public function addPass(\Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface $pass, $type = \Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION)
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
        $this->passConfig->addPass($pass, $type, $priority);
    }
    /**
     * Adds a log message.
     *
     * @param string $string The log message
     *
     * @deprecated since version 3.3, to be removed in 4.0. Use the ContainerBuilder::log() method instead.
     */
    public function addLogMessage($string)
    {
        @\trigger_error(\sprintf('The %s() method is deprecated since Symfony 3.3 and will be removed in 4.0. Use the ContainerBuilder::log() method instead.', __METHOD__), \E_USER_DEPRECATED);
        $this->log[] = $string;
    }
    /**
     * @final
     */
    public function log(\Emoji\Vendor\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface $pass, $message)
    {
        if (\false !== \strpos($message, "\n")) {
            $message = \str_replace("\n", "\n" . \get_class($pass) . ': ', \trim($message));
        }
        $this->log[] = \get_class($pass) . ': ' . $message;
    }
    /**
     * Returns the log.
     *
     * @return array Log array
     */
    public function getLog()
    {
        return $this->log;
    }
    /**
     * Run the Compiler and process all Passes.
     */
    public function compile(\Emoji\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        try {
            foreach ($this->passConfig->getPasses() as $pass) {
                $pass->process($container);
            }
        } catch (\Exception $e) {
            $usedEnvs = [];
            $prev = $e;
            do {
                $msg = $prev->getMessage();
                if ($msg !== ($resolvedMsg = $container->resolveEnvPlaceholders($msg, null, $usedEnvs))) {
                    $r = new \ReflectionProperty($prev, 'message');
                    $r->setAccessible(\true);
                    $r->setValue($prev, $resolvedMsg);
                }
            } while ($prev = $prev->getPrevious());
            if ($usedEnvs) {
                $e = new \Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\EnvParameterException($usedEnvs, $e);
            }
            throw $e;
        } finally {
            $this->getServiceReferenceGraph()->clear();
        }
    }
}
