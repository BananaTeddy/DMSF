<?php

declare(strict_types=1);

namespace system\Event;

final class EventManager {
    
    /** @var callback[] $listeners  */
    protected $listeners = [];

    /**
     * @param string $type
     * @param callback $callback
     */
    public function subscribe(string $type, callable $callback): void
    {
        $this->listeners[$type][] = $callback;
    }

    /**
     * Checks if given eventtype has a listener
     * @return bool
     */
    public function hasEventListener(string $type): bool
    {
        return isset($this->listeners[$type]);
    }

    /**
     * Calls all callbacks for given event
     * 
     * @param Event $event
     * @return void
     */
    public function raise(Event $event): void
    {
        if ($this->hasEventListener($event->Type)) {
            foreach ($this->listeners[$event->Type] as $listener) {
                $listener($event);
            }
        }
    }
}
