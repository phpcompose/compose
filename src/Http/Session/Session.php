<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-01-04
 * Time: 12:35 PM
 */

namespace Compose\Http\Session;

use SessionHandlerInterface;

class Session
{
    protected array $options = [
        'cookie_path' => '/',
        'cookie_timeout' => 21600,
        'garbage_timeout' => 216600,
        'cookie_secure' => false,
        'cookie_domain' => null,
    ];

    private SessionStorageInterface $storage;
    private ?SessionHandlerInterface $handler = null;

    public function __construct(SessionStorageInterface $storage, array $options = null)
    {
        $this->storage = $storage;

        if ($options) {
            $this->options = $options + $this->options;
        }
    }

    public function start(SessionHandlerInterface $handler = null): void
    {
        if ($handler) {
            $this->handler = $handler;
        }

        $this->storage->start($this->handler, $this->options);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        $this->ensureStarted();
        return $this->storage->getId();
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->storage->setId($id);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        $this->ensureStarted();
        return $this->storage->has($name);
    }

    /**
     * @param string $name
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get(string $name, $default = null)
    {
        $this->ensureStarted();
        return $this->storage->get($name, $default);
    }

    /**
     * @param string $name
     * @param $value
     */
    public function set(string $name, $value): void
    {
        $this->ensureStarted();
        $this->storage->set($name, $value);
    }

    /**
     * @param string $name
     */
    public function unset(string $name): void
    {
        $this->ensureStarted();
        $this->storage->remove($name);
    }

    /**
     * Regenerate session id and return this instance
     *
     * @return $this
     */
    public function regenerate(): self
    {
        $this->ensureStarted();
        $this->storage->regenerate(true);
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $this->ensureStarted();
        return $this->storage->all();
    }

    public function __destruct()
    {
        $this->storage->close();
    }

    /**
     * Ensure the session has been started.
     */
    protected function ensureStarted(): void
    {
        if (!$this->storage->isStarted()) {
            $this->start($this->handler);
        }
    }
}
