<?php
namespace Datavis\DiagramType;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\SiteRepresentation;

class NetworkGraph implements DiagramTypeInterface
{
    public function getLabel() : string
    {
        return 'Network graph'; // @translate
    }

    public function addElements(SiteRepresentation $site, Fieldset $fieldset) : void
    {
        $defaults = [
            'width' => 700,
            'height' => 700,
            'color_scheme' => 'schemeCategory10',
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
            'type' => Element\Select::class,
            'name' => 'color_scheme',
            'options' => [
                'label' => 'Color scheme', // @translate
                'info' => 'Click <a href="https://d3js.org/d3-scale-chromatic/categorical" target="_blank">here</a> to view the available color schemes.', // @translate
                'escape_info' => false,
                'value_options' => [
                    'schemeCategory10' => 'schemeCategory10',
                    'schemeAccent' => 'schemeAccent',
                    'schemeDark2' => 'schemeDark2',
                    'schemePaired' => 'schemePaired',
                    'schemePastel1' => 'schemePastel1',
                    'schemePastel2' => 'schemePastel2',
                    'schemeSet1' => 'schemeSet1',
                    'schemeSet2' => 'schemeSet2',
                    'schemeSet3' => 'schemeSet3',
                    'schemeTableau10' => 'schemeTableau10',
                ],
            ],
            'attributes' => [
                'value' => $defaults['color_scheme'],
                'required' => true,
            ],
        ]);
    }

    public function prepareRender(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile('https://d3js.org/d3.v6.js');
        $view->headScript()->appendFile('https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js');
        $view->headScript()->appendFile($view->assetUrl('js/diagram-render/item_relationships.js', 'Datavis'));
        $view->headScript()->appendFile($view->assetUrl('js/diagram-render/network_graph.js', 'Datavis'));
        $view->headLink()->appendStylesheet($view->assetUrl('css/diagram-render/network_graph.css', 'Datavis'));
    }
}
