<?php
namespace ConsoleConfigResolver\Test\Factory;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use ConsoleConfigResolver\Factory\ConfigResolverFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class ConfigResolverFactoryTest extends TestCase
{
    public function testCreateInstance()
    {
        $config = [
            'console' => [
            ],
        ];

        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);

        $factory = new ConfigResolverFactory();

        /** @var Application $instance */
        $instance = $factory->__invoke($container->reveal(), 'console');

        $this->assertInstanceOf(Application::class, $instance);
    }

    public function testCreateInstanceWithName()
    {
        $config = [
            'console' => [
                'name' => 'My console application',
            ],
        ];

        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);

        $factory = new ConfigResolverFactory();

        /** @var Application $instance */
        $instance = $factory->__invoke($container->reveal(), 'console');

        $this->assertInstanceOf(Application::class, $instance);
        $this->assertEquals($config['console']['name'], $instance->getName());
    }

    public function testCreateInstanceWithNameAndVersion()
    {
        $config = [
            'console' => [
                'name' => 'My console application',
                'version'  => '1.0.0',
            ],
        ];

        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);

        $factory = new ConfigResolverFactory();

        /** @var Application $instance */
        $instance = $factory->__invoke($container->reveal(), 'console');

        $this->assertInstanceOf(Application::class, $instance);
        $this->assertEquals($config['console']['name'], $instance->getName());
        $this->assertEquals($config['console']['version'], $instance->getVersion());
    }

    public function testResolveCommandByObject()
    {
        $command = new Command('command');

        $config = [
            'console' => [
                'name'     => 'My console application',
                'version'  => '1.0.0',
                'commands' => [
                    $command,
                ],
            ],
        ];

        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);

        $factory = new ConfigResolverFactory();

        /** @var Application $instance */
        $instance = $factory->__invoke($container->reveal(), 'console');

        $this->assertInstanceOf(Application::class, $instance);
        $this->assertInstanceOf(
            Command::class,
            $instance->get($command->getName())
        );
    }

    /**
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotCreatedException
     * @expectedExceptionMessageRegExp =^Console commands provided by configuration must either be a class name or instance of.*=
     */
    public function testResolveCommandByObjectOfWrongTypeThrowsException()
    {
        $config = [
            'console' => [
                'name'     => 'My console application',
                'version'  => '1.0.0',
                'commands' => [
                    new \stdClass(),
                ],
            ],
        ];

        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);

        $factory = new ConfigResolverFactory();

        /** @var Application $instance */
        $instance = $factory->__invoke($container->reveal(), 'console');
    }

    public function testResolveCommandByString()
    {
        $config = [
            'console' => [
                'name'     => 'My console application',
                'version'  => '1.0.0',
                'commands' => [
                    'TestCommand',
                ],
            ],
        ];

        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);

        $command = new Command('command');

        $container->has('TestCommand')->willReturn(true);
        $container->get('TestCommand')->willReturn($command);

        $factory = new ConfigResolverFactory();

        /** @var Application $instance */
        $instance = $factory->__invoke($container->reveal(), 'console');

        $this->assertInstanceOf(Application::class, $instance);
        $this->assertInstanceOf(
            Command::class,
            $instance->get($command->getName())
        );
    }

    /**
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     * @expectedExceptionMessage Unable to resolve "Application\TestCommand".
     */
    public function testResolveUnknownCommandByStringThrowsException()
    {
        $config = [
            'console' => [
                'name'     => 'My console application',
                'version'  => '1.0.0',
                'commands' => [
                    'Application\TestCommand',
                ],
            ],
        ];

        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);
        $container->has('Application\TestCommand')->willReturn(false);

        $factory = new ConfigResolverFactory();

        /** @var Application $instance */
        $instance = $factory->__invoke($container->reveal(), 'console');
    }
}
