<?php
namespace Datavis\Service\Delegator;

use Datavis\Form\Element;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;

class FormElementDelegatorFactory implements DelegatorFactoryInterface
{
    public function __invoke(ContainerInterface $container, $name,
        callable $callback, array $options = null
    ) {
        $formElement = $callback();
        $formElement->addClass(Element\GroupByControl::class, 'formDatavisGroupByControl');
        return $formElement;
    }
}
