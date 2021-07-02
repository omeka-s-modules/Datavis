<?php
namespace Datavis\DatasetType;

use Datavis\Api\Representation\DatavisVisRepresentation;
use Laminas\Form\Fieldset;
use Laminas\ServiceManager\ServiceManager;
use Omeka\Api\Representation\SiteRepresentation;

class Unknown implements DatasetTypeInterface
{
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getLabel() : string
    {
        return '[Unknown]'; // @translate
    }

    public function getDescription() : ?string
    {
        return null;
    }

    public function getDiagramTypeNames() : array
    {
        return [];
    }

    public function addElements(SiteRepresentation $site, Fieldset $fieldset) : void
    {
    }

    public function getDataset(ServiceManager $services, DatavisVisRepresentation $vis) : array
    {
        return [];
    }
}
