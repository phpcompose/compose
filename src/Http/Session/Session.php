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
        if(session_status() == PHP_SESSION_NONE) {
            if(headers_sent()) {}

            if($handler) {
                session_set_save_handler($handler, true);
            }

            // set the options and configure the session for the first time
            $options = $this->options;
            session_set_cookie_params(
                $options['cookie_timeout'],
                $options['cookie_path'],
                $options['cookie_domain'],
                $options['cookie_secure'],
                true);
            ini_set('session.gc_maxlifetime', $options['garbage_timeout']);
            session_cache_limiter("must-revalidate");
            // start the session
            session_start();
        }
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return session_id();
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        session_id($id);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name) : bool
    {
        return isset($_SESSION[$name]);
    }

    /**
     * @param string $name
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get(string $name, $default = null)
    {
        return $_SESSION[$name] ?? $default;
    }

    /**
     * @param string $name
     * @param $value
     */
    public function set(string $name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * @param string $name
     */
    public function unset(string $name)
    {
        $_SESSION[$name] = null;
        unset($_SESSION[$name]);
    }

    /**
     * Regenerate session id and return this instance
     *
     * @return $this
     */
    public function regenerate() : self
    {
        session_regenerate_id();
        return $this;
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return $_SESSION;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }
}
