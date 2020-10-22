<?php
namespace Datavis\ControllerPlugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorInterface;

class Datavis extends AbstractPlugin
{
    protected $services;

    public function __construct(ServiceLocatorInterface $services)
    {
        $this->services = $services;
    }

    public function getDatasetTypeNames()
    {
        $datasetTypes = $this->services->get('Datavis\DatasetTypeManager');
        return $datasetTypes->getRegisteredNames();
    }

    public function getDatasetType($name)
    {
        $datasetTypes = $this->services->get('Datavis\DatasetTypeManager');
        return $datasetTypes->get($name);
    }

    public function getDiagramType($name)
    {
        $diagramTypes = $this->services->get('Datavis\DiagramTypeManager');
        return $diagramTypes->get($name);
    }
}
