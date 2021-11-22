<?php
namespace Datavis\Form;

use Datavis\DatasetType\Manager;
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
    }
}
