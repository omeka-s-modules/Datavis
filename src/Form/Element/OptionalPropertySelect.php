<?php
namespace Datavis\Form\Element;

use Omeka\Form\Element\PropertySelect;
use Zend\InputFilter\InputProviderInterface;

/**
 * By default, Laminas sets Select elements as required. This makes it optional.
 */
class OptionalPropertySelect extends PropertySelect implements InputProviderInterface
{
    public function getInputSpecification() : array
    {
        return [
            'name' => $this->getName(),
            'required' => false,
            'validators' => [],
            'filters' => [],
        ];
    }
}
