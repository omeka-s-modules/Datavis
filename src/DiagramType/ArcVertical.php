<?php
namespace Datavis\DiagramType;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\SiteRepresentation;

class ArcVertical implements DiagramTypeInterface
{
    public function getLabel() : string
    {
        return 'Arc'; // @translate
    }

    public function addElements(SiteRepresentation $site, Fieldset $fieldset) : void
    {
        $defaults = [
            'width' => 800,
            'order' => 'by_group',
            'step' => 14,
            'label_font_size' => 'medium',
            'margin_top' => 30,
            'margin_bottom' => 30,
            'margin_left' => 200,
        ];

        $fieldset->add([
            'type' => Element\Number::class,
            'name' => 'width',
            'options' => [
                'label' => 'Width', // @translate
            ],
            'attributes' => [
                'min' => 0,
                'value' => $defaults['width'],
                'placeholder' => $defaults['width'],
                'required' => true,
            ],
        ]);
        $fieldset->add([
            'type' => Element\Number::class,
            'name' => 'margin_top',
            'options' => [
                'label' => 'Margin top', // @translate
            ],
            'attributes' => [
                'min' => 0,
                'value' => $defaults['margin_top'],
                'placeholder' => $defaults['margin_top'],
                'required' => true,
            ],
        ]);
        $fieldset->add([
            'type' => Element\Number::class,
            'name' => 'margin_bottom',
            'options' => [
                'label' => 'Margin bottom', // @translate
            ],
            'attributes' => [
                'min' => 0,
                'value' => $defaults['margin_bottom'],
                'placeholder' => $defaults['margin_bottom'],
                'required' => true,
            ],
        ]);
        $fieldset->add([
            'type' => Element\Number::class,
            'name' => 'margin_left',
            'options' => [
                'label' => 'Margin left', // @translate
            ],
            'attributes' => [
                'min' => 0,
                'value' => $defaults['margin_left'],
                'placeholder' => $defaults['margin_left'],
                'required' => true,
            ],
        ]);
        $fieldset->add([
            'type' => Element\Select::class,
            'name' => 'order',
            'options' => [
                'label' => 'Order',
                'value_options' => [
                    'by_group' => 'By group', // @translate
                    'by_label' => 'By label', // @translate
                ],
            ],
            'attributes' => [
                'value' => $defaults['order'],
                'required' => true,
            ],
        ]);
        $fieldset->add([
            'type' => Element\Select::class,
            'name' => 'label_font_size',
            'options' => [
                'label' => 'Label font size', // @translate
                'value_options' => [
                    'xx-small' => 'xx-small',
                    'x-small' => 'x-small',
                    'small' => 'small',
                    'medium' => 'medium',
                    'large' => 'large',
                    'x-large' => 'x-large',
                    'xx-large' => 'xx-large',
                    'xxx-large' => 'xxx-large',
                ],
            ],
            'attributes' => [
                'value' => $defaults['label_font_size'],
                'required' => true,
            ],
        ]);
        $fieldset->add([
            'type' => Element\Number::class,
            'name' => 'step',
            'options' => [
                'label' => 'Space between labels', // @translate
            ],
            'attributes' => [
                'min' => 1,
                'value' => $defaults['step'],
                'placeholder' => $defaults['step'],
                'required' => true,
            ],
        ]);
    }

    public function prepareRender(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile('https://d3js.org/d3.v6.js');
        $view->headScript()->appendFile('https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js');
        $view->headScript()->appendFile($view->assetUrl('js/diagram-render/item_relationships.js', 'Datavis'));
        $view->headScript()->appendFile($view->assetUrl('js/diagram-render/arc_vertical.js', 'Datavis'));
        $view->headLink()->appendStylesheet($view->assetUrl('css/diagram-render/arc_vertical.css', 'Datavis'));
    }
}
