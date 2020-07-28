<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-06-18
 * Time: 11:26 AM
 */

namespace Compose\Container;


use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class InvokableClass {
    public function __invoke(ServiceContainer $container)
    {
        return new \ArrayObject();
    }

    static function create() {
        return new \stdClass();
    }
}

class Service1 {

}

class Service2 implements ResolvableInterface  {

}

class Service3 implements ContainerAwareInterface{
    use ContainerAwareTrait;

    public $service;
    public function __construct(Service2 $service2)
    {
        $this->service = $service2;
    }
}

interface Service2Interface {

}

class Service2Factory implements ServiceFactoryInterface
{
    public static function create(ContainerInterface $container, string $name)
    {
        return new Service2();
    }
}

class Service4 {
    public function __construct(Service2 $service2, Service3 $service3)
    {
        $this->service2 = $service2;
        $this->service3 = $service3;
    }
}

/**
 * Class ServiceContainerTest
 * @package Compose\Container
 */
final class ServiceContainerTest extends TestCase
{
    /** @var ServiceContainer */
    protected $container;

    public function setUp()
    {
        $this->container = new ServiceContainer();
    }

    /**
     * Disable setting any scalar value as service.  This is rather odd resctriction
     */
    public function testCannotSetScalar() : void
    {
        $this->expectException(\LogicException::class);

        $this->container->set('abc', 12);
    }

    /**
     *
     */
    public function testCanSetCallable() : void
    {
        $this->assertNull($this->container->set('abc', [InvokableClass::class, 'create']));
    }

    public function testCanResolveCallableDependencies() : void
    {
        $this->assertNull($this->container->set('callback', function(Service2 $service2) {
            return [

            ];
        }));

        $this->container->get('callback');


    }

    public function testCanSetClass() : void
    {
        $this->assertNull($this->container->set('def', InvokableClass::class));
    }

    public function testObjectService() : void
    {
        $service = new \stdClass();
        $this->container->set('service1', $service);
        $this->assertSame($service, $this->container->get('service1'));
    }

    public function testCallableService() : void
    {
        $this->container->set('service2', [InvokableClass::class, 'create']);
        $service = $this->container->get('service2');
        $this->assertSame($service, $this->container->get('service2'));
    }

    public function testClosureService() : void
    {
        $this->container->set('service3', function () {
            return new \ArrayObject();
        });

        $service = $this->container->get('service3');
        $this->assertSame($service, $this->container->get('service3'));
        $this->assertNotSame($service, new \ArrayObject());
    }

    public function testCannotSetExistingService() : void
    {
        $this->expectException(\LogicException::class);

        $this->container->set('service1', function() {
            return new \stdClass();
        });

        $this->container->set('service1', new \ArrayObject());
    }

    /**
     * @throws \Exception
     */
    public function testAlias() : void
    {
        $this->container->set('service5', function () {
            return new \stdClass();
        });

        $this->container->set('service6', 'service5'); // setup alias
        $this->container->set('service7', 'service6');
        $service6 = $this->container->get('service6');
        $service5 = $this->container->get('service5');
        $service7 = $this->container->get('service7');

        $this->assertSame($service5, $service6);
        $this->assertSame($service7, $service5);
        $this->assertSame($service7, $service6);
    }

    /**
     * Should be unable to resolve class that is not registered
     * and NOT implementing Service interece
     * @throws \Exception
     */
    public function testCannotResolveClass() : void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->container->get(Service1::class);
    }

    /**
     * Should be able to resolve class
     * @throws \Exception
     */
    public function testCanResolveClass() : void
    {
        $service = $this->container->get(Service2::class);
        $this->assertInstanceOf(Service2::class, $service);
    }

    /**
     * @throws \Exception
     */
    public function testCanResolveWithFactoryClass() : void
    {
        $this->container->set(Service2::class, Service2Factory::class);
        $this->container->set(Service2Interface::class, Service2::class);
        $service2 = $this->container->get(Service2::class);
        $service2Interface = $this->container->get(Service2Interface::class);

        $this->assertInstanceOf(Service2::class, $service2);
        $this->assertSame($service2, $service2Interface);
        $this->assertSame($service2, $this->container->get(Service2::class));
    }

    /**
     * @throws \Exception
     */
    public function testContainerAwareInterface() : void
    {
        /** @var Service3 $service */
        $service = $this->container->get(Service3::class);
        $this->assertSame($service->getContainer(), $this->container);
    }

    /**
     * @throws \Exception
     */
    public function testCanResolveDependencies() : void
    {
        $service3 = $this->container->get(Service3::class);
        $this->assertInstanceOf(Service3::class, $service3);

        $service2 = $this->container->get(Service2::class);
        $this->assertSame($service2, $service3->service);
    }

    public function testHas() : void
    {
        $container = $this->container;
        $container->set('abc', Service2::class);
        $this->assertTrue($container->has(Service3::class));
        $this->assertTrue($container->has('abc'));
        $this->assertNotTrue($container->has(Service1::class));
    }

    public function testCanRegisterAnyClass() : void
    {
        $this->container->set(Service4::class);
        $service = $this->container->get(Service4::class);
        $this->assertInstanceOf(Service4::class, $service);
        $this->assertInstanceOf(Service2::class, $service->service2);
    }
}
