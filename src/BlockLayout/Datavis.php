<?php
namespace Datavis\BlockLayout;

use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Site\BlockLayout\AbstractBlockLayout;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\View\Renderer\PhpRenderer;

class Datavis extends AbstractBlockLayout
{
    public function getLabel()
    {
        return 'Data visualization'; // @translate
    }

    public function form(PhpRenderer $view, SiteRepresentation $site,
        SitePageRepresentation $page = null, SitePageBlockRepresentation $block = null
    ) {
        $vises = $view->api()->search('datavis_visualizations', [
            'has_diagram_type' => true,
            'has_dataset' => true,
        ])->getContent();

        $valueOptions = [];
        foreach ($vises as $vis) {
            $owner = $vis->owner();
            $ownerEmail = $owner->email() ?? 'unknown';
            $ownerName = $owner->name() ?? null;
            if (!isset($valueOptions[$ownerEmail])) {
                $valueOptions[$ownerEmail] = [
                    'label' => ('unknown' === $ownerEmail)
                        ? '[Unknown]' // @translate
                        : sprintf('%s (%s)', $ownerName, $ownerEmail),
                    'options' => [],
                ];
            }
            $valueOptions[$ownerEmail]['options'][$vis->id()] = $vis->title();
        }

        $form = new Form;
        $form->add([
            'type' => Element\Select::class,
            'name' => 'o:block[__blockIndex__][o:data][id]',
            'options' => [
                'label' => 'Visualization', // @translate
                'empty_option' => 'Select visualization…', // @translate
                'value_options' => $valueOptions,
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $defaults = [
            'id' => null,
        ];
        $data = $block ? $block->data() + $defaults : $defaults;
        $form->setData([
            'o:block[__blockIndex__][o:data][id]' => $data['id'],
        ]);

        return $view->formCollection($form);
    }

    public function render(PhpRenderer $view, SitePageBlockRepresentation $block)
    {
        $blockData = $block->data();
        $vis = $view->api()->searchOne('datavis_visualizations', [
            'id' => $blockData['id'],
            'has_diagram_type' => true,
            'has_dataset' => true,
        ])->getContent();
        if (!$vis) {
            return; // The visualization is invalid. Do nothing.
        }
        return $vis->diagram($view, $blockData);
    }
}
