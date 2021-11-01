<?php
namespace Compose\Event;


interface MessageInterface
{
    public function getTarget() : ?object;
    public function getArguments() : iterable;
}