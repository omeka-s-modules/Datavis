<?php
namespace Datavis\Service\Form\Element;

use Interop\Container\ContainerInterface;
use Datavis\Form\Element\GroupByControl;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GroupByControlFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $element = new GroupByControl;
        $element->setFormElementManager($services->get('FormElementManager'));
        return $element;
    }
}
