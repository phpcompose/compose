<?php


namespace Compose\Mvc\Helper;


interface HelperInterface
{
    public function setRegistry(HelperRegistry $registry);
    public function getRegistry() : HelperRegistry;
}