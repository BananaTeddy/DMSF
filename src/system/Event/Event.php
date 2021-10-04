<?php declare(strict_types=1);
namespace system\Event;

class Event {
    
    /** @var mixed $Source That which triggered the event */
    public $Source;

    /** @var string $Type The type of the event */
    public $Type;

    public function __construct(string $type, $source) {
        $this->Type = $type;
        $this->Source = $source;
    }
}
