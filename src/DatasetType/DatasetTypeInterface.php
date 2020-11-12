<?php
namespace Datavis\DatasetType;

use Datavis\Api\Representation\DatavisVisRepresentation;
use Laminas\Form\Fieldset;
use Laminas\ServiceManager\ServiceManager;
use Omeka\Api\Representation\SiteRepresentation;

interface DatasetTypeInterface
{
    /**
     * Get the label of this dataset type.
     *
     * @return string
     */
    public function getLabel() : string;

    /**
     * Get the description of this dataset type.
     *
     * @return string
     */
    public function getDescription() : ?string;

    /**
     * Get the names of the diagram types that are compatible with this dataset.
     *
     * @return array
     */
    public function getDiagramTypeNames() : array;

    /**
     * Add the form elements used for the dataset data.
     *
     * @param SiteRepresentation $site
     * @param Fieldset $fieldset
     */
    public function addElements(SiteRepresentation $site, Fieldset $fieldset) : void;

    /**
     * Generate and return the JSON dataset given a visualiation.
     *
     * @param ServiceManager $services
     * @param DatavisVisRepresentation $vis
     * @return array
     */
    public function getDataset(ServiceManager $services, DatavisVisRepresentation $vis) : array;
}
