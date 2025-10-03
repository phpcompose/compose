<?php
namespace Compose\Mvc\Helper;

interface HelperInterface
{
    /**
     * Invoked by the helper registry with the current rendering context.
     *
     * Implementations may return any value (including $this) to expose further APIs.
     *
     * @param HelperRegistry $helpers
     * @param mixed ...$args
     * @return mixed
     */
    public function __invoke(HelperRegistry $helpers, ...$args);
}
