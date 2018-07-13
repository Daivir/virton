<?php
namespace Tests\EventManager;

use Virton\EventManager\EventManager;
use PHPUnit\Framework\TestCase;
use Psr\EventManager\EventInterface;

class EventManagerTest extends TestCase
{
    /**
     * @var EventManager
     */
    private $manager;

    protected function setUp()
    {
        $this->manager = new EventManager();
    }

    private function makeEvent(string $eventName = 'test.event', $target = 'target')
    {
        $event = $this->getMockBuilder(EventInterface::class)->getMock();
        $event->method('getName')->willReturn($eventName);
        return $event;
    }

    public function testTriggerEvent()
    {
        $event = $this->makeEvent();
        $this->manager->attach($event->getName(), function () {
            echo 'Event';
        });
        $this->manager->trigger($event->getName());
        $this->expectOutputString('Event');
    }

    public function testTriggerEventWithEventObject()
    {
        $event = $this->makeEvent();
        $this->manager->attach($event->getName(), function () {
            echo 'Event';
        });
        $this->manager->trigger($event->getName());
        $this->expectOutputString($event);
        $this->expectOutputString('Event');
    }

    public function testTriggerMultipleEvent()
    {
        $event = $this->makeEvent();

        $this->manager->attach($event->getName(), function () {
            echo 'EventA';
        });
        $this->manager->attach($event->getName(), function () {
            echo 'EventB';
        });

        $this->manager->trigger($event->getName());

        $this->expectOutputRegex('/EventA/');
        $this->expectOutputRegex('/EventB/');
    }

    public function testTriggerOrderWithPriority()
    {
        $event = $this->makeEvent();

        $this->manager->attach($event->getName(), function () {
            echo 'EventA';
        }, 100);
        $this->manager->attach($event->getName(), function () {
            echo 'EventC';
        });
        $this->manager->attach($event->getName(), function () {
            echo 'EventB';
        }, 10);

        $this->manager->trigger($event->getName());

        $this->expectOutputString('EventAEventBEventC');
    }

    public function testDetachListener()
    {
        $event = $this->makeEvent();

        $callbackA = function () {
            echo 'EventA';
        };
        $callbackB = function () {
            echo 'EventB';
        };

        $this->manager->attach($event->getName(), $callbackA);
        $this->manager->attach($event->getName(), $callbackB);
        $this->manager->detach($event->getName(), $callbackB);
        $this->manager->trigger($event->getName());
        $this->expectOutputString('EventA');
    }

    public function testClearListeners()
    {
        $eventA = $this->makeEvent('a');
        $eventB = $this->makeEvent('b');

        $this->manager->attach($eventA->getName(), function () {
            echo 'EventA';
        });
        $this->manager->attach($eventA->getName(), function () {
            echo 'EventAA';
        });
        $this->manager->attach($eventB->getName(), function () {
            echo 'EventB';
        });

        $this->manager->clearListeners($eventA->getName());

        $this->manager->trigger($eventA->getName());
        $this->manager->trigger($eventB->getName());

        $this->expectOutputString('EventB');
    }
}
