<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Emoji\Vendor\Symfony\Component\DependencyInjection\Dumper;

use Emoji\Vendor\Symfony\Component\DependencyInjection\Argument\ArgumentInterface;
use Emoji\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
use Emoji\Vendor\Symfony\Component\DependencyInjection\Definition;
use Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Emoji\Vendor\Symfony\Component\DependencyInjection\Parameter;
use Emoji\Vendor\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Emoji\Vendor\Symfony\Component\DependencyInjection\Reference;
/**
 * GraphvizDumper dumps a service container as a graphviz file.
 *
 * You can convert the generated dot file with the dot utility (http://www.graphviz.org/):
 *
 *   dot -Tpng container.dot > foo.png
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GraphvizDumper extends \Emoji\Vendor\Symfony\Component\DependencyInjection\Dumper\Dumper
{
    private $nodes;
    private $edges;
    // All values should be strings
    private $options = ['graph' => ['ratio' => 'compress'], 'node' => ['fontsize' => '11', 'fontname' => 'Arial', 'shape' => 'record'], 'edge' => ['fontsize' => '9', 'fontname' => 'Arial', 'color' => 'grey', 'arrowhead' => 'open', 'arrowsize' => '0.5'], 'node.instance' => ['fillcolor' => '#9999ff', 'style' => 'filled'], 'node.definition' => ['fillcolor' => '#eeeeee'], 'node.missing' => ['fillcolor' => '#ff9999', 'style' => 'filled']];
    /**
     * Dumps the service container as a graphviz graph.
     *
     * Available options:
     *
     *  * graph: The default options for the whole graph
     *  * node: The default options for nodes
     *  * edge: The default options for edges
     *  * node.instance: The default options for services that are defined directly by object instances
     *  * node.definition: The default options for services that are defined via service definition instances
     *  * node.missing: The default options for missing services
     *
     * @return string The dot representation of the service container
     */
    public function dump(array $options = [])
    {
        foreach (['graph', 'node', 'edge', 'node.instance', 'node.definition', 'node.missing'] as $key) {
            if (isset($options[$key])) {
                $this->options[$key] = \array_merge($this->options[$key], $options[$key]);
            }
        }
        $this->nodes = $this->findNodes();
        $this->edges = [];
        foreach ($this->container->getDefinitions() as $id => $definition) {
            $this->edges[$id] = \array_merge($this->findEdges($id, $definition->getArguments(), \true, ''), $this->findEdges($id, $definition->getProperties(), \false, ''));
            foreach ($definition->getMethodCalls() as $call) {
                $this->edges[$id] = \array_merge($this->edges[$id], $this->findEdges($id, $call[1], \false, $call[0] . '()'));
            }
        }
        return $this->container->resolveEnvPlaceholders($this->startDot() . $this->addNodes() . $this->addEdges() . $this->endDot(), '__ENV_%s__');
    }
    /**
     * Returns all nodes.
     *
     * @return string A string representation of all nodes
     */
    private function addNodes()
    {
        $code = '';
        foreach ($this->nodes as $id => $node) {
            $aliases = $this->getAliases($id);
            $code .= \sprintf("  node_%s [label=\"%s\\n%s\\n\", shape=%s%s];\n", $this->dotize($id), $id . ($aliases ? ' (' . \implode(', ', $aliases) . ')' : ''), $node['class'], $this->options['node']['shape'], $this->addAttributes($node['attributes']));
        }
        return $code;
    }
    /**
     * Returns all edges.
     *
     * @return string A string representation of all edges
     */
    private function addEdges()
    {
        $code = '';
        foreach ($this->edges as $id => $edges) {
            foreach ($edges as $edge) {
                $code .= \sprintf("  node_%s -> node_%s [label=\"%s\" style=\"%s\"%s];\n", $this->dotize($id), $this->dotize($edge['to']), $edge['name'], $edge['required'] ? 'filled' : 'dashed', $edge['lazy'] ? ' color="#9999ff"' : '');
            }
        }
        return $code;
    }
    /**
     * Finds all edges belonging to a specific service id.
     *
     * @param string $id        The service id used to find edges
     * @param array  $arguments An array of arguments
     * @param bool   $required
     * @param string $name
     *
     * @return array An array of edges
     */
    private function findEdges($id, array $arguments, $required, $name, $lazy = \false)
    {
        $edges = [];
        foreach ($arguments as $argument) {
            if ($argument instanceof \Emoji\Vendor\Symfony\Component\DependencyInjection\Parameter) {
                $argument = $this->container->hasParameter($argument) ? $this->container->getParameter($argument) : null;
            } elseif (\is_string($argument) && \preg_match('/^%([^%]+)%$/', $argument, $match)) {
                $argument = $this->container->hasParameter($match[1]) ? $this->container->getParameter($match[1]) : null;
            }
            if ($argument instanceof \Emoji\Vendor\Symfony\Component\DependencyInjection\Reference) {
                $lazyEdge = $lazy;
                if (!$this->container->has((string) $argument)) {
                    $this->nodes[(string) $argument] = ['name' => $name, 'required' => $required, 'class' => '', 'attributes' => $this->options['node.missing']];
                } elseif ('service_container' !== (string) $argument) {
                    $lazyEdge = $lazy || $this->container->getDefinition((string) $argument)->isLazy();
                }
                $edges[] = ['name' => $name, 'required' => $required, 'to' => $argument, 'lazy' => $lazyEdge];
            } elseif ($argument instanceof \Emoji\Vendor\Symfony\Component\DependencyInjection\Argument\ArgumentInterface) {
                $edges = \array_merge($edges, $this->findEdges($id, $argument->getValues(), $required, $name, \true));
            } elseif ($argument instanceof \Emoji\Vendor\Symfony\Component\DependencyInjection\Definition) {
                $edges = \array_merge($edges, $this->findEdges($id, $argument->getArguments(), $required, ''), $this->findEdges($id, $argument->getProperties(), \false, ''));
                foreach ($argument->getMethodCalls() as $call) {
                    $edges = \array_merge($edges, $this->findEdges($id, $call[1], \false, $call[0] . '()'));
                }
            } elseif (\is_array($argument)) {
                $edges = \array_merge($edges, $this->findEdges($id, $argument, $required, $name, $lazy));
            }
        }
        return $edges;
    }
    /**
     * Finds all nodes.
     *
     * @return array An array of all nodes
     */
    private function findNodes()
    {
        $nodes = [];
        $container = $this->cloneContainer();
        foreach ($container->getDefinitions() as $id => $definition) {
            $class = $definition->getClass();
            if ('\\' === \substr($class, 0, 1)) {
                $class = \substr($class, 1);
            }
            try {
                $class = $this->container->getParameterBag()->resolveValue($class);
            } catch (\Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException $e) {
            }
            $nodes[$id] = ['class' => \str_replace('\\', '\\\\', $class), 'attributes' => \array_merge($this->options['node.definition'], ['style' => $definition->isShared() ? 'filled' : 'dotted'])];
            $container->setDefinition($id, new \Emoji\Vendor\Symfony\Component\DependencyInjection\Definition('stdClass'));
        }
        foreach ($container->getServiceIds() as $id) {
            if (\array_key_exists($id, $container->getAliases())) {
                continue;
            }
            if (!$container->hasDefinition($id)) {
                $nodes[$id] = ['class' => \str_replace('\\', '\\\\', \get_class($container->get($id))), 'attributes' => $this->options['node.instance']];
            }
        }
        return $nodes;
    }
    private function cloneContainer()
    {
        $parameterBag = new \Emoji\Vendor\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag($this->container->getParameterBag()->all());
        $container = new \Emoji\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder($parameterBag);
        $container->setDefinitions($this->container->getDefinitions());
        $container->setAliases($this->container->getAliases());
        $container->setResources($this->container->getResources());
        foreach ($this->container->getExtensions() as $extension) {
            $container->registerExtension($extension);
        }
        return $container;
    }
    /**
     * Returns the start dot.
     *
     * @return string The string representation of a start dot
     */
    private function startDot()
    {
        return \sprintf("digraph sc {\n  %s\n  node [%s];\n  edge [%s];\n\n", $this->addOptions($this->options['graph']), $this->addOptions($this->options['node']), $this->addOptions($this->options['edge']));
    }
    /**
     * Returns the end dot.
     *
     * @return string
     */
    private function endDot()
    {
        return "}\n";
    }
    /**
     * Adds attributes.
     *
     * @param array $attributes An array of attributes
     *
     * @return string A comma separated list of attributes
     */
    private function addAttributes(array $attributes)
    {
        $code = [];
        foreach ($attributes as $k => $v) {
            $code[] = \sprintf('%s="%s"', $k, $v);
        }
        return $code ? ', ' . \implode(', ', $code) : '';
    }
    /**
     * Adds options.
     *
     * @param array $options An array of options
     *
     * @return string A space separated list of options
     */
    private function addOptions(array $options)
    {
        $code = [];
        foreach ($options as $k => $v) {
            $code[] = \sprintf('%s="%s"', $k, $v);
        }
        return \implode(' ', $code);
    }
    /**
     * Dotizes an identifier.
     *
     * @param string $id The identifier to dotize
     *
     * @return string A dotized string
     */
    private function dotize($id)
    {
        return \strtolower(\preg_replace('/\\W/i', '_', $id));
    }
    /**
     * Compiles an array of aliases for a specified service id.
     *
     * @param string $id A service id
     *
     * @return array An array of aliases
     */
    private function getAliases($id)
    {
        $aliases = [];
        foreach ($this->container->getAliases() as $alias => $origin) {
            if ($id == $origin) {
                $aliases[] = $alias;
            }
        }
        return $aliases;
    }
}
