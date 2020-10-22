<?php
namespace Datavis\Service\ControllerPlugin;

use Interop\Container\ContainerInterface;
use Datavis\ControllerPlugin\Datavis;
use Zend\ServiceManager\Factory\FactoryInterface;

class DatavisFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new Datavis($services);
    }
}
