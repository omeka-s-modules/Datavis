<?php
namespace Datavis\DatasetType;

use Datavis\Api\Representation\DatavisVisRepresentation;
use Laminas\Form\Fieldset;
use Laminas\ServiceManager\ServiceManager;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Form\Element\PropertySelect;

class CountItemsProperties extends AbstractDatasetType
{
    public function getLabel() : string
    {
        return 'Count of items with properties'; // @translate
    }

    public function getDescription() : ?string
    {
        return 'Visualize the count of items that have selected properties.'; // @translate
    }

    public function getDiagramTypeNames() : array
    {
        return ['bar_chart', 'column_chart', 'pie_chart'];
    }

    public function addElements(SiteRepresentation $site, Fieldset $fieldset) : void
    {
        $fieldset->add([
            'type' => PropertySelect::class,
            'name' => 'property_ids',
            'options' => [
                'label' => 'Property', // @translate
                'show_required' => true,
            ],
            'attributes' => [
                'multiple' => true,
                'required' => false,
                'class' => 'chosen-select',
                'data-placeholder' => 'Select properties…', // @translate
            ],
        ]);
    }

    public function getDataset(ServiceManager $services, DatavisVisRepresentation $vis) : array
    {
        $em = $services->get('Omeka\EntityManager');

        // Prepare the query to get the count of items with a property.
        $dql = '
        SELECT COUNT(DISTINCT v.resource)
        FROM Omeka\Entity\Value AS v
        WHERE v.resource IN (:item_ids)
        AND v.property = :property_id';
        $query = $em->createQuery($dql);
        $query->setParameter('item_ids', $this->getItemIds($services, $vis));

        $dataset = [];
        $propertyIds = $vis->datasetData()['property_ids'] ?? [];
        foreach ($propertyIds as $propertyId) {
            $property = $em->find('Omeka\Entity\Property', $propertyId);
            if (null === $property) {
                // This property does not exist.
                continue;
            }
            $vocab = $property->getVocabulary();
            $query->setParameter('property_id', $property->getId());
            $dataset[] = [
                'id' => $property->getId(),
                'label' => $property->getLabel(),
                'label_long' => sprintf('%s (%s)', $property->getLabel(), $vocab->getLabel()),
                'value' => (int) $query->getSingleScalarResult(),
            ];
        }
        return $dataset;
    }
}
