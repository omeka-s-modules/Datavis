<?php
namespace Datavis\Service\Form\Element;

use Interop\Container\ContainerInterface;
use Datavis\Form\Element\OptionalPropertySelect;
use Laminas\ServiceManager\Factory\FactoryInterface;

class OptionalPropertySelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $element = new OptionalPropertySelect;
        $element->setApiManager($services->get('Omeka\ApiManager'));
        $element->setEventManager($services->get('EventManager'));
        $element->setTranslator($services->get('MvcTranslator'));
        return $element;
    }
}
