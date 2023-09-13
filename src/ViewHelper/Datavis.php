<?php
namespace Datavis\ViewHelper;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\AbstractHelper;

class Datavis extends AbstractHelper
{
    protected $services;

    public function __construct(ServiceLocatorInterface $services)
    {
        $this->services = $services;
    }

    public function getDatasetTypeNames()
    {
        $datasetTypes = $this->services->get('Datavis\DatasetTypeManager');
        $datasetTypeNames = $datasetTypes->getRegisteredNames();
        sort($datasetTypeNames);
        return $datasetTypeNames;
    }

    public function getDatasetType($name)
    {
        $datasetTypes = $this->services->get('Datavis\DatasetTypeManager');
        return $datasetTypes->get($name);
    }
}
