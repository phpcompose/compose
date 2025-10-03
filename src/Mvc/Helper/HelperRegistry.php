<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-04-02
 * Time: 10:35 AM
 */

namespace Compose\Mvc\Helper;

use Compose\Container\ContainerAwareInterface;
use Compose\Container\ContainerAwareTrait;
use Compose\Container\ServiceResolver;
use Compose\Mvc\View;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class HelperRegistry
 * @package Compose\Mvc\Helper
 */
class HelperRegistry implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @deprecated Use getCurrentView()/getCurrentRequest()
     * @var View|null
     */
    public $currentView;

    /**
     * @deprecated Use getCurrentView()/getCurrentRequest()
     * @var ServerRequestInterface|null
     */
    public $currentRequest;

    private ServiceResolver $resolver;
    private array $definitions = [];
    private array $instances = [];

    private ?View $view = null;
    private ?ServerRequestInterface $request = null;

    public function __construct(ServiceResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function __invoke()
    {
        return $this;
    }

    public function register(string $name, $definition): void
    {
        if (isset($this->definitions[$name])) {
            throw new \InvalidArgumentException("Helper is already registered with that name: {$name}");
        }

        if (!is_string($definition) && !is_callable($definition) && !is_object($definition)) {
            throw new \InvalidArgumentException('Helper definition must be a class name, callable, or object.');
        }

        $this->definitions[$name] = $definition;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->definitions);
    }

    public function setContext(?View $view, ?ServerRequestInterface $request): void
    {
        $this->view = $view;
        $this->request = $request;
        $this->currentView = $view;
        $this->currentRequest = $request;
    }

    public function getCurrentView(): ?View
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

        $this->register($alias, function(HelperRegistry $helpers, ...$arguments) use ($helperName, $method) {
            $helper = $helpers->get($helperName);

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

        $callable = $this->resolveDefinition($name);

        if (is_object($callable) && property_exists($callable, 'registry')) {
            $callable->registry = $this;
        }

        if (!is_callable($callable)) {
            throw new \LogicException('Helper is not callable: ' . $name);
        }

        return $callable($this, ...$arguments);
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
