<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-06-26
 * Time: 4:47 PM
 */

namespace Compose\Event;

use PHPUnit\Framework\TestCase;

class MessageOne implements EventInterface
{

}

class EventDispatcherTest extends TestCase
{
    /** @var EventDispatcher */
    protected $notifier;

    /** @var \ArrayObject */
    protected $results;

    protected function setUp(): void
    {
        $this->notifier = new EventDispatcher();
        $this->results = new \ArrayObject();
    }

    /**
     * @throws \Exception
     */
    public function testBasicListeningAndDispatching()
    {
        $results = new \ArrayObject();
        $this->notifier->attach(MessageOne::class, function(MessageOne $message) use($results) {
            $results['test1'] = 'value1';
        });

        $this->assertArrayNotHasKey('test1', $results);
        $this->notifier->dispatch(new MessageOne());
        $this->assertEquals('value1', $results['test1']);
    }

    /**
     * @throws \Exception
     */
    public function testCustomMessageObjectListening()
    {
        $this->notifier->attach('message.two', function(Message $message) {
            $message['arg1'] = 'changed';
        });

        $message = new Message('message.two', ['arg1' => 'val1']);
        $this->notifier->dispatch($message);

        $this->assertEquals('changed', $message['arg1']);
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
        $this->notifier->dispatch(new Message('event1'));
        $this->notifier->dispatch(new Message('event1'));

        $this->assertEquals(2, $count);

        // now detach
        $this->notifier->detach('event1', $listener);
        $this->notifier->dispatch(new Message('event1'));
        $this->assertEquals(2, $count);
    }

    /**
     * @throws \Exception
     */
    public function _testEventArgs()
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

        $this->notifier->dispatch(new Message('event2', ['param1' => 'value1']));
    }

    /**
     * @throws \Exception
     */
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

            public function onEvent3(Message $message) {
                $this->object['param1'] = $message['param1'];
            }

            public function onEvent4()
            {

            }
        };

        $this->notifier->subscribe($subscriber);
        $this->notifier->dispatch(new Message('event3', ['param1' => 'value1']));

        $this->assertEquals('value1', $result['param1']);
    }
}
