<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-06-26
 * Time: 4:47 PM
 */

namespace Compose\Event;

use function Clue\StreamFilter\fun;
use PHPUnit\Framework\TestCase;

class EventNotifierTest extends TestCase
{
    /** @var EventNotifier */
    protected $notifier;

    /** @var \ArrayObject */
    protected $results;

    public function setUp()
    {
        $this->notifier = new EventNotifier();
        $this->results = new \ArrayObject();
    }

    /**
     * @throws \Exception
     */
    public function testBasicListeningAndDispatching()
    {
        $results = new \ArrayObject();
        $this->notifier->attach('event.abc', function(EventArgs $args) use($results) {
            $results['test1'] = 'value1';
        });

        $this->assertArrayNotHasKey('test1', $results);
        $this->notifier->notify('event.abc');
        $this->assertEquals('value1', $results['test1']);
    }

    /**
     * @throws \Exception
     */
    public function testAttachAndDetachListener()
    {
        $count = 0;

        $listener = function () use (&$count) {
            $count++;
        };

        $this->notifier->attach('event1', $listener);
        $this->notifier->notify('event1');
        $this->notifier->notify('event1');

        $this->assertEquals(2, $count);

        // now detach
        $this->notifier->detach('event1', $listener);
        $this->notifier->notify('event1');
        $this->assertEquals(2, $count);
    }

    /**
     * @throws \Exception
     */
    public function testEventArgs()
    {
        $args = new EventArgs('event2', ['param1' => 'value2']);
        $this->assertEquals('event2', $args->getName());
        $this->assertArrayHasKey('param1', $args);
        $this->assertEquals('value2', $args['param1']);
        $this->assertNull($args->getSender());



        $this->notifier->attach('event2', function(EventArgs $args) {
            $this->assertEquals('event2', $args->getName());
            $this->assertEquals('value1', $args['param1']);
        });

        $this->notifier->notify('event2', ['param1' => 'value1']);
    }

    public function testSubscription()
    {
        $result = new \ArrayObject();

        $subscriber = new class($result) implements SubscriberInterface {
            protected $object;

            public function __construct(\ArrayObject $object) {
                $this->object = $object;
            }

            public function subscribedEvents(): array
            {
                return ['event3' => 'onEvent3', 'event4' => 'onEvent4'];
            }

            public function onEvent3(EventArgs $args) {
                $this->object['param1'] = $args['param1'];
            }

            public function onEvent4()
            {

            }
        };

        $this->notifier->subscribe($subscriber);
        $this->notifier->notify('event3', ['param1' => 'value1']);

        $this->assertEquals('value1', $result['param1']);
    }
}
