<?php
namespace Datavis\Form\Element;

use Laminas\Form\Element;
use Laminas\Form\FormElementManager;
use Omeka\Form\Element\PropertySelect;

class GroupByControl extends Element
{
    protected $formElementManager;

    public function setFormElementManager(FormElementManager $formElementManager)
    {
        $this->formElementManager = $formElementManager;
    }

    public function getFormElementManager()
    {
        return $this->formElementManager;
    }

    public function getGroupByElement()
    {
        $element = new Element\Select(sprintf('%s[group_by]', $this->getName()));
        $element->setAttribute('id', 'group-by-select');
        $element->setValueOptions([
            'resource_class' => 'Resource class', // @translate
            'resource_template' => 'Resource template', // @translate
            'property_value' => 'Property value', // @translate
        ]);
        $value = $this->getValue();
        $element->setValue($value['group_by'] ?? null);
        return $element;
    }

    public function getGroupByPropertyElement()
    {
        $element = $this->formElementManager->get(PropertySelect::class);
        $element->setName(sprintf('%s[group_by_property]', $this->getName()));
        $element->setEmptyOption('');
        $element->setAttributes([
            'id' => 'group-by-property-select',
            'data-placeholder' => 'Select a property…', // @translate
            'style' => 'display: none;',
        ]);
        $value = $this->getValue();
        $element->setValue($value['group_by_property'] ?? null);
        return $element;
    }
}
