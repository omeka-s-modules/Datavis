<?php
namespace Datavis\Form;

use Datavis\DatasetType\Manager;
use Laminas\Form\Element;
use Laminas\Form\Form;

class DatasetTypeForm extends Form
{
    protected $datasetTypeManager;

    public function setDatasetTypeManager(Manager $datasetTypeManager)
    {
        $this->datasetTypeManager = $datasetTypeManager;
    }

    public function init()
    {
        $valueOptions = [];
        $datasetTypeNames = $this->datasetTypeManager->getRegisteredNames();
        foreach ($datasetTypeNames as $datasetTypeName) {
            $valueOptions[$datasetTypeName] = $this->datasetTypeManager->get($datasetTypeName)->getLabel();
        }
        $this->add([
            'type' => Element\Radio::class,
            'name' => 'o-module-datavis:dataset_type',
            'options' => [
                'label' => 'Dataset type', // @translate
                'value_options' => $valueOptions,
            ],
        ]);
    }
}
