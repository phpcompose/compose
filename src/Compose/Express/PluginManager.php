<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-11-05
 * Time: 9:15 PM
 */

namespace Compose\Express;




use Compose\System\Event\NotifierInterface;

class PluginManager
{
    protected
        /**
         * @var NotifierInterface
         */
        $notifier;

    /**
     * PluginManager constructor.
     * @param NotifierInterface $notifier
     */
    public function __construct(NotifierInterface $notifier)
    {
        $this->notifier = $notifier;
    }

    /**
     * @param Plugin $plugin
     */
    public function plug(Plugin $plugin)
    {
        $plugin->onPlug($this->notifier);
    }

    /**
     * @param Plugin $plugin
     */
    public function unplug(Plugin $plugin)
    {
        $plugin->onUnplug($this->notifier);
    }

}