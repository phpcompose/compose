<?php

namespace Compose\Http\Session;

use RuntimeException;
use SessionHandlerInterface;

class NativeSessionStorage implements SessionStorageInterface
{
    public function start(?SessionHandlerInterface $handler = null, array $options = []): void
    {
        if ($this->isStarted()) {
            return;
        }

        if (headers_sent()) {
            throw new RuntimeException('Cannot start session: headers already sent');
        }

        if ($handler) {
            session_set_save_handler($handler, true);
        }

        $cookieParams = [
            'lifetime' => (int) ($options['cookie_timeout'] ?? 0),
            'path' => (string) ($options['cookie_path'] ?? '/'),
            'domain' => (string) ($options['cookie_domain'] ?? ''),
            'secure' => (bool) ($options['cookie_secure'] ?? false),
            'httponly' => true,
        ];

        session_set_cookie_params($cookieParams);
        ini_set('session.gc_maxlifetime', (string) ($options['garbage_timeout'] ?? 0));
        session_cache_limiter('must-revalidate');

        if (!session_start()) {
            throw new RuntimeException('Unable to start PHP session');
        }
    }

    public function isStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public function getId(): string
    {
        $this->assertStarted();
        return session_id();
    }

    public function setId(string $id): void
    {
        if ($this->isStarted()) {
            throw new RuntimeException('Cannot set session id after session has started');
        }

        session_id($id);
    }

    public function has(string $name): bool
    {
        $this->assertStarted();
        return isset($_SESSION[$name]);
    }

    public function get(string $name, $default = null)
    {
        $this->assertStarted();
        return $_SESSION[$name] ?? $default;
    }

    public function set(string $name, $value): void
    {
        $this->assertStarted();
        $_SESSION[$name] = $value;
    }

    public function remove(string $name): void
    {
        $this->assertStarted();

        if (array_key_exists($name, $_SESSION)) {
            unset($_SESSION[$name]);
        }
    }

    public function regenerate(bool $deleteOldSession = true): void
    {
        $this->assertStarted();
        session_regenerate_id($deleteOldSession);
    }

    public function all(): array
    {
        $this->assertStarted();
        return $_SESSION;
    }

    public function clear(): void
    {
        $this->assertStarted();
        $_SESSION = [];
    }

    public function close(): void
    {
        if ($this->isStarted()) {
            session_write_close();
        }
    }

    private function assertStarted(): void
    {
        if (!$this->isStarted()) {
            throw new RuntimeException('Session has not been started');
        }
    }
}
