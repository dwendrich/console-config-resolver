<?php
namespace ConsoleConfigResolver\Factory;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class ConfigResolverFactory
 *
 * @package ConsoleConfigResolver\Factory
 * @author Daniel Wendrich <daniel.wendrich@gmail.com>
 */
class ConfigResolverFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $name = 'UNKNOWN';
        $version = 'UNKNOWN';

        $config = $container->has('config')
            ? $container->get('config')
            : [];

        if (isset($config[$requestedName]['name'])) {
            $name = $config[$requestedName]['name'];
        }

        if (isset($config[$requestedName]['version'])) {
            $version = $config[$requestedName]['version'];
        }

        $application = new Application($name, $version);

        // add application commands
        if (! empty($config[$requestedName]['commands']) && is_array($config[$requestedName]['commands'])) {
            foreach ($config[$requestedName]['commands'] as $command) {
                $command = $this->resolveCommand($command, $container);
                $application->add($command);
            }
        }

        return $application;
    }

    private function resolveCommand($command, ContainerInterface $container)
    {
        if (is_string($command)) {
            // first check, if di container knows how to instantiate the object
            if ($container->has($command)) {
                $command = $container->get($command);
            } else {
                if (!class_exists($command)) {
                    throw new ServiceNotFoundException(
                        sprintf(
                            'An invalid command was registered; resolved to class "%s" ' .
                            'which does not exist; please provide a valid class name ' .
                            'resolving to an implementation of "%s".',
                            $command,
                            Command::class
                        )
                    );
                }
                $command = new $command();
            }
        }

        if (! $command instanceof Command) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'An invalid command was registered. Expected an instance of ' .
                    '(or string class name resolving to) "%s", but "%s" was received.',
                    Command::class,
                    (is_object($command) ? get_class($command) : gettype($command))
                )
            );
        }

        return $command;
    }
}
