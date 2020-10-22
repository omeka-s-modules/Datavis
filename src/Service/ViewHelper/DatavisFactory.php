<?php
namespace Datavis\Service\ViewHelper;

use Datavis\ViewHelper\Datavis;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DatavisFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new Datavis($services);
    }
}
