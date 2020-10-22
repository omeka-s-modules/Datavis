<?php
namespace Datavis\DiagramType;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\SiteRepresentation;

class LineChart implements DiagramTypeInterface
{
    public function getLabel() : string
    {
        return 'Line chart'; // @translate
    }

    public function addElements(SiteRepresentation $site, Fieldset $fieldset) : void
    {
        $defaults = [
            'width' => 700,
            'height' => 700,
            'margin_top' => 30,
            'margin_right' => 30,
            'margin_bottom' => 100,
            'margin_left' => 60,
            'curve_type' => 'linear',
            'points' => false,
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
            'name' => 'height',
            'options' => [
                'label' => 'Height', // @translate
            ],
            'attributes' => [
                'min' => 0,
                'value' => $defaults['height'],
                'placeholder' => $defaults['height'],
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
            'name' => 'margin_right',
            'options' => [
                'label' => 'Margin right', // @translate
            ],
            'attributes' => [
                'min' => 0,
                'value' => $defaults['margin_right'],
                'placeholder' => $defaults['margin_right'],
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
            'name' => 'curve_type',
            'options' => [
                'label' => 'Curve type',
                'value_options' => [
                    'linear' => 'Linear',
                    'monotonex' => 'MonotoneX',
                    'natural' => 'Natural',
                    'step' => 'Step',
                    'stepafter' => 'StepAfter',
                    'stepbefore' => 'StepBefore',
                ],
            ],
            'attributes' => [
                'value' => $defaults['curve_type'],
                'required' => true,
            ],
        ]);
        $fieldset->add([
            'type' => Element\Checkbox::class,
            'name' => 'points',
            'options' => [
                'label' => 'Add points',
            ],
            'attributes' => [
                'value' => $defaults['points'],
            ],
        ]);
    }

    public function prepareRender(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile('https://d3js.org/d3.v6.js');
        $view->headScript()->appendFile($view->assetUrl('js/block-layout-render/line_chart.js', 'Datavis'));
        $view->headLink()->appendStylesheet($view->assetUrl('css/block-layout-render/line_chart.css', 'Datavis'));
    }
}
