<?php
namespace Compose\Support\Pattern;

/**
 * Trait the provides default instance functionality
 *
 * Provides ability to set and access default instance for any object using it.
 */
trait SharedInstanceTrait
{

    protected static ?self $_t_defaultinstance;

    /**
     * Returns the single instance of this object.
     *
     * If you need to simply check if there is default instance, use hasDefaultInstance()
     * This method will create new instance if instance isn't available
     * @return static
     * @throws \Exception
     */
    final public static function sharedInstance()
    {
        if (!self::$_t_defaultinstance) {
            throw new Exception('Default Instance not found for: ' . get_called_class());
        }

        return self::$_t_defaultinstance;
    }

    /**
     * Checks if there is any default instance
     *
     * @return bool
     */
    final public static function hasSharedInstance()
    {
        return (self::$_t_defaultinstance !== null);
    }

    /**
     * Sets the default instance.
     *
     * The instance must be same object type of the class type
     *
     * @param Closure|object|SharedInstanceTrait $instance
     * @return null
     */
    final public static function setSharedInstance(self $instance)
    {
        self::$_t_defaultinstance = $instance;
    }
}