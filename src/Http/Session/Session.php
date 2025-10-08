<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-01-04
 * Time: 12:35 PM
 */

namespace Compose\Http\Session;



/**
 * Class Session
 * @package Compose\Http\Session
 */
class Session
{
    protected
        /**
         * @var array
         */
        $options = [
            'cookie_path' => '/',
            'cookie_timeout' => 21600,
            'garbage_timeout' => 216600,
            'cookie_secure' => false,
            'cookie_domain' => null
        ],

        /**
         * @var string
         */
        $id;

    /**
     * Session constructor.
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        if($options) {
            $this->options = $options + $this->options;
        }
    }

    /**
     * @param \SessionHandlerInterface|null $handler
     */
    public function start(\SessionHandlerInterface $handler = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (headers_sent()) {
                throw new \RuntimeException('Cannot start session: headers already sent');
            }

            if ($handler) {
                session_set_save_handler($handler, true);
            }

            // set the options and configure the session for the first time
            $options = $this->options;
            $cookieDomain = $options['cookie_domain'] ?? '';
            session_set_cookie_params(
                (int) ($options['cookie_timeout'] ?? 0),
                (string) ($options['cookie_path'] ?? '/'),
                (string) $cookieDomain,
                (bool) ($options['cookie_secure'] ?? false),
                true
            );
            ini_set('session.gc_maxlifetime', (string) ($options['garbage_timeout'] ?? 0));
            session_cache_limiter('must-revalidate');

            // start the session
            session_start();
        }
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        $this->ensureStarted();
        return session_id();
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        // set session id before starting the session
        if (session_status() !== PHP_SESSION_NONE) {
            throw new \RuntimeException('Cannot set session id after session has started');
        }
        session_id($id);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name) : bool
    {
        $this->ensureStarted();
        return isset($_SESSION[$name]);
    }

    /**
     * @param string $name
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get(string $name, $default = null)
    {
        $this->ensureStarted();
        return $_SESSION[$name] ?? $default;
    }

    /**
     * @param string $name
     * @param $value
     */
    public function set(string $name, $value)
    {
        $this->ensureStarted();
        $_SESSION[$name] = $value;
    }

    /**
     * @param string $name
     */
    public function unset(string $name)
    {
        $this->ensureStarted();
        if (array_key_exists($name, $_SESSION)) {
            $_SESSION[$name] = null;
            unset($_SESSION[$name]);
        }
    }

    /**
     * Regenerate session id and return this instance
     *
     * @return $this
     */
    public function regenerate() : self
    {
        $this->ensureStarted();
        // delete old session id by passing true
        session_regenerate_id(true);
        return $this;
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        $this->ensureStarted();
        return $_SESSION;
    }

    public function __destruct()
    {
        if (session_status() !== PHP_SESSION_NONE) {
            // write and close the session to ensure data is persisted
            session_write_close();
        }
    }

    /**
     * Ensure the session has been started.
     */
    protected function ensureStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $this->start();
        }
    }
}
