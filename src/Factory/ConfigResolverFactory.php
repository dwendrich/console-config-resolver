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
            if ($container->has($command)) {
                $command = $container->get($command);
            } else {
                throw new ServiceNotFoundException(sprintf(
                    'Unable to resolve "%s".',
                    $command
                ));
            }
        }

        if (! $command instanceof Command) {
            throw new ServiceNotCreatedException(sprintf(
                'Console commands provided by configuration must either be a class name ' .
                'or instance of "Symfony\Component\Console\Command\Command", but "%s" given.',
                is_object($command)
                    ? get_class($command)
                    : gettype($command)
            ));
        }

        return $command;
    }
}
