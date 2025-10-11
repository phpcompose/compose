<?php

namespace Compose\Template\Helper;

use Compose\Container\ServiceFactoryInterface;
use Compose\Container\ServiceResolver;
use Compose\Support\Configuration;
use Compose\Template\Template;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class HelperRegistry implements HelperRegistryInterface, ServiceFactoryInterface
{

    /** @deprecated */
    public $currentView;

    /** @deprecated */
    public $currentRequest;

    private ServiceResolver $resolver;
    private array $definitions = [];
    private array $instances = [];

    private ?Template $view = null;
    private ?ServerRequestInterface $request = null;

    public function __construct(ServiceResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public static function create(ContainerInterface $container, string $id): self
    {
        $registry = new self(new ServiceResolver($container));
        $config = $container->get(Configuration::class);

        $helpers = $config['templates']['helpers'] ?? [];
        foreach ($helpers as $alias => $definition) {
            if (is_int($alias)) {
                $registry->extend($definition);
                continue;
            }

            $registry->register((string) $alias, $definition);
        }

        return $registry;
    }

    public function __invoke()
    {
        return $this;
    }

    public function register(string $name, $definition): void
    {
        if (!is_string($definition) && !is_callable($definition) && !is_object($definition)) {
            throw new \InvalidArgumentException('Helper definition must be a class name, callable, or object.');
        }

        if (isset($this->definitions[$name])) {
            if ($this->definitions[$name] === $definition) {
                return;
            }

            unset($this->instances[$name]);
        }

        $this->definitions[$name] = $definition;
    }

    /**
     * Registers all public methods of the given class or object as helper aliases.
     */
    public function extend($helper): void
    {
        $reflection = new \ReflectionClass($helper);
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $name = $method->getName();
            if ($name === '__construct' || str_starts_with($name, '__')) {
                continue;
            }

            if ($this->has($name)) {
                continue;
            }

            $this->register($name, $helper);
        }
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->definitions);
    }

    public function setContext(?Template $view, ?ServerRequestInterface $request): void
    {
        $this->view = $view;
        $this->request = $request;
        $this->currentView = $view;
        $this->currentRequest = $request;
    }

    public function getCurrentView(): ?Template
    {
        return $this->view;
    }

    public function getCurrentRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    public function get(string $name)
    {
        return $this->invokeHelper($name, []);
    }

    public function getMany(array $names): array
    {
        $result = [];
        foreach ($names as $name) {
            $result[$name] = $this->get($name);
        }

        return $result;
    }

    public function call(string $name, ...$arguments)
    {
        return $this->invokeHelper($name, $arguments);
    }

    public function registerMethodAlias(string $alias, string $helperName, string $method): void
    {
        if ($this->has($alias)) {
            return;
        }

        $this->register($alias, function (...$arguments) use ($helperName, $method) {
            $helper = $this->get($helperName);

            if (!is_object($helper) || !method_exists($helper, $method)) {
                throw new \LogicException(sprintf('Helper "%s" does not provide method "%s".', $helperName, $method));
            }

            return $helper->$method(...$arguments);
        });
    }

    public function __call($name, $arguments)
    {
        return $this->call($name, ...$arguments);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    private function invokeHelper(string $name, array $arguments)
    {
        if (!$this->has($name)) {
            throw new \Exception('Helper is not registered: ' . $name);
        }

        $definition = $this->resolveDefinition($name);

        if (is_object($definition) && $definition instanceof HelperRegistryAwareInterface) {
            $definition->setHelperRegistry($this);
        }

        if (is_object($definition) && method_exists($definition, $name)) {
            return $definition->$name(...$arguments);
        }

        if (is_callable($definition)) {
            return $definition(...$arguments);
        }

        if (is_object($definition) && method_exists($definition, '__call')) {
            return $definition->$name(...$arguments);
        }

        throw new \LogicException('Helper is not callable: ' . $name);
    }

    private function resolveDefinition(string $name)
    {
        $definition = $this->definitions[$name];

        if (is_string($definition)) {
            if (!isset($this->instances[$name])) {
                $instance = $this->resolver->resolve($definition);
                $this->instances[$name] = $instance;
            }

            return $this->instances[$name];
        }

        if (is_object($definition) && !($definition instanceof \Closure)) {
            if (!isset($this->instances[$name])) {
                $this->instances[$name] = $definition;
            }

            return $this->instances[$name];
        }

        return $definition;
    }
}
