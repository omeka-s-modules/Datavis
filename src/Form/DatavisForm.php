<?php
namespace Datavis\Form;

use Datavis\DatasetType;
use Datavis\DiagramType;
use Datavis\Form\Element as DatavisElement;
use Laminas\Form\Element as LaminasElement;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Omeka\Form\Element as OmekaElement;

class DatavisForm extends Form
{
    protected $datasetTypeManager;

    protected $diagramTypeManager;

    public function setDatasetTypeManager(DatasetType\Manager $datasetTypeManager)
    {
        $this->datasetTypeManager = $datasetTypeManager;
    }

    public function setDiagramTypeManager(DiagramType\Manager $diagramTypeManager)
    {
        $this->diagramTypeManager = $diagramTypeManager;
    }

    public function init()
    {
        // General elements
        $this->add([
            'type' => LaminasElement\Text::class,
            'name' => 'o:title',
            'options' => [
                'label' => 'Title', // @translate
            ],
            'attributes' => [
                'id' => 'o:title',
                'required' => true,
            ],
        ]);

        $this->add([
            'type' => LaminasElement\Textarea::class,
            'name' => 'o:description',
            'options' => [
                'label' => 'Description', // @translate
            ],
            'attributes' => [
                'id' => 'o:description',
            ],
        ]);

        $this->add([
            'type' => OmekaElement\Query::class,
            'name' => 'o:query',
            'options' => [
                'label' => 'Search query', // @translate
                'info' => 'Configure the logical grouping of items that will be used for your dataset. No query means all items assigned to this site.', // @translate
                'query_resource_type' => 'items',
                'query_partial_excludelist' => [
                    'common/advanced-search/site',
                    'common/advanced-search/sort',
                ],
                'query_preview_append_query' => ['site_id' => $this->getOption('site')->id()],
            ],
        ]);

        // Dataset elements
        $datasetTypeName = $this->getOption('dataset_type');
        $datasetType = $this->datasetTypeManager->get($datasetTypeName);

        $this->add([
            'type' => LaminasElement\Text::class,
            'name' => 'dataset_type',
            'options' => [
                'label' => 'Dataset type', // @translate
            ],
            'attributes' => [
                'id' => 'dataset_type',
                'disabled' => true,
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Hidden::class,
            'name' => 'o-module-datavis:dataset_type',
            'options' => [
                'label' => 'Dataset type', // @translate
            ],
            'attributes' => [
                'id' => 'o-module-datavis:dataset_type',
                'value' => $datasetTypeName,
            ],
        ]);

        $this->add([
            'type' => Fieldset::class,
            'name' => 'o-module-datavis:dataset_data',
            'attributes' => [
                'id' => 'o-module-datavis:dataset_data',
            ],
        ]);
        $datasetType->addElements(
            $this->getOption('site'),
            $this->get('o-module-datavis:dataset_data')
        );

        // Diagram elements
        $valueOptions = [];
        foreach ($datasetType->getDiagramTypeNames() as $diagramTypeName) {
            $diagramType = $this->diagramTypeManager->get($diagramTypeName);
            $valueOptions[$diagramTypeName] = $diagramType->getLabel();
        }
        $this->add([
            'type' => DatavisElement\OptionalSelect::class,
            'name' => 'o-module-datavis:diagram_type',
            'options' => [
                'label' => 'Diagram type', // @translate
                'info' => 'Warning: you will lose the current diagram configuration if you change the diagram type.', // @translate
                'empty_option' => 'Select diagram type', // @translate
                'value_options' => $valueOptions,
            ],
            'attributes' => [
                'id' => 'o-module-datavis:diagram_type',
                'required' => false,
            ],
        ]);

        $this->add([
            'type' => Fieldset::class,
            'name' => 'o-module-datavis:diagram_data',
            'attributes' => [
                'id' => 'o-module-datavis:diagram_data',
            ],
        ]);
        if ($this->getOption('diagram_type')) {
            $this->addDiagramElements($this->getOption('diagram_type'));
        }
    }

    public function addDiagramElements($diagramTypeName)
    {
        $diagramType = $this->diagramTypeManager->get($diagramTypeName);
        $diagramType->addElements(
            $this->getOption('site'),
            $this->get('o-module-datavis:diagram_data')
        );
    }
}
