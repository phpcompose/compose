<?php

namespace Compose\Event;

interface SubscriberInterface
{
    /**
     * @return array<class-string, string|string[]>
     */
    public function getSubscribedEvents(): array;
}
