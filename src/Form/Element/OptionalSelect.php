<?php
namespace Datavis\Form\Element;

use Zend\Form\Element\Select;
use Zend\InputFilter\InputProviderInterface;

/**
 * By default, Laminas sets Select elements as required. This makes it optional.
 */
class OptionalSelect extends Select implements InputProviderInterface
{
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => false,
            'validators' => [],
            'filters' => [],
        ];
    }
}
