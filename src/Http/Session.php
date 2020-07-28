<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-01-04
 * Time: 12:35 PM
 */

namespace Compose\Http;



/**
 * Class Session
 * @package Compose\Http
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

//        if (isset($_SESSION['__destroyed__'])) {
//            if ($_SESSION['__destroyed__'] < time()-300) {
//                // Should not happen usually. This could be attack or due to unstable network.
//                // Remove all authentication status of this users session.
//                remove_all_authentication_flag_from_active_sessions($_SESSION['userid']);
//                throw(new DestroyedSessionAccessException);
//            }
//            if (isset($_SESSION['__session_id__'])) {
//                // Not fully expired yet. Could be lost cookie by unstable network.
//                // Try again to set proper session ID cookie.
//                // NOTE: Do not try to set session ID again if you would like to remove
//                // authentication flag.
//                session_commit();
//                session_id($_SESSION['__session_id__']);
//                // New session ID should exist
//                session_start();
//                return;
//            }
//        }
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
     * @param null $default
     * @return null
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
     * @return Session
     */
    public function regenerate() : self
    {
        session_regenerate_id();
//        $session_id = session_create_id();
//        $_SESSION['__session_id__'] = $session_id;
//        $_SESSION['__destroyed__'] = time();
//        session_commit();
//
//        // Start session with new session ID
//        session_id($session_id);
//        ini_set('session.use_strict_mode', 0);
//        session_start();
//        ini_set('session.use_strict_mode', 1);
//
//        // New session does not need them
//        unset($_SESSION['__destroyed__']);
//        unset($_SESSION['__session_id__']);
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