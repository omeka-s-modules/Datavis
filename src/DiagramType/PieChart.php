<?php
namespace Datavis\DiagramType;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\SiteRepresentation;

class PieChart implements DiagramTypeInterface
{
    public function getLabel() : string
    {
        return 'Pie chart'; // @translate
    }

    public function addElements(SiteRepresentation $site, Fieldset $fieldset) : void
    {
        $defaults = [
            'width' => 700,
            'height' => 700,
            'margin' => 30,
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
            'name' => 'margin',
            'options' => [
                'label' => 'Margin', // @translate
            ],
            'attributes' => [
                'min' => 0,
                'value' => $defaults['margin'],
                'placeholder' => $defaults['margin'],
                'required' => true,
            ],
        ]);
    }

    public function prepareRender(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile('https://d3js.org/d3.v6.js');
        $view->headScript()->appendFile('https://d3js.org/d3-collection.v1.min.js');
        $view->headScript()->appendFile($view->assetUrl('js/diagram-render/pie_chart.js', 'Datavis'));
        $view->headLink()->appendStylesheet($view->assetUrl('css/diagram-render/pie_chart.css', 'Datavis'));
    }
}
