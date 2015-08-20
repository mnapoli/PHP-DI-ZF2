<?php
/**
 * PHP-DI
 *
 * @link http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\ZendFramework2\Service;

use Acclimate\Container\ContainerAcclimator;
use DI\Container;
use DI\ContainerBuilder;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Abstract factory responsible of trying to build services from the PHP DI container
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @author Martin Fris
 */
final class DIContainerFactory implements FactoryInterface
{

    /**
     * @var Container
     */
    private $container;

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Container
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if ($this->container !== null) {
            return $this->container;
        }

        $builder = new ContainerBuilder();
        $config = $serviceLocator->get('config');
        $configFile = $this->getDefinitionsFilePath($config);

        if (file_exists($configFile)) {
            $builder->addDefinitions($configFile);
        }

        $builder->useAnnotations(true);

        $acclimator = new ContainerAcclimator();
        $zfContainer = $acclimator->acclimate($serviceLocator);
        $builder->wrapContainer($zfContainer);

        $this->container = $builder->build();

        return $this->container;
    }

    /**
     * return definitions file path
     *
     * @param array $config
     *
     * @return string
     */
    private function getDefinitionsFilePath(array $config)
    {
        $filePath = __DIR__ . '/../../../../../../../config/php-di.config.php';

        if (isset($config['phpdi-zf2']) && isset($config['phpdi-zf2']['definitionsFile'])) {
            $filePath = $config['phpdi-zf2']['definitionsFile'];
        }

        return $filePath;
    }
}
