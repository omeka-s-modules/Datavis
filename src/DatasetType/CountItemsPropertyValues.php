<?php
namespace Datavis\DatasetType;

use Datavis\Api\Representation\DatavisVisRepresentation;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\ServiceManager\ServiceManager;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Form\Element\PropertySelect;

class CountItemsPropertyValues extends AbstractDatasetType
{
    public function getLabel() : string
    {
        return 'Count of items with property values'; // @translate
    }

    public function getDescription() : ?string
    {
        return 'Visualize the count of items that have selected values of a selected property.'; // @translate
    }

    public function getDiagramTypeNames() : array
    {
        return ['bar_chart', 'column_chart', 'pie_chart'];
    }

    public function addElements(SiteRepresentation $site, Fieldset $fieldset) : void
    {
        $fieldset->add([
            'type' => PropertySelect::class,
            'name' => 'property_id',
            'options' => [
                'label' => 'Value property', // @translate
                'show_required' => true,
                'empty_option' => '',
            ],
            'attributes' => [
                'required' => false,
                'class' => 'chosen-select',
                'data-placeholder' => 'Select one…', // @translate
                'required' => true,
            ],
        ]);
        $fieldset->add([
            'type' => Element\Textarea::class,
            'name' => 'values',
            'options' => [
                'label' => 'Values', // @translate
                'info' => 'Enter values, separated by new lines.', // @translate
            ],
            'attributes' => [
                'id' => 'values',
                'rows' => 10,
                'required' => true,
            ],
        ]);
    }

    public function getDataset(ServiceManager $services, DatavisVisRepresentation $vis) : array
    {
        $em = $services->get('Omeka\EntityManager');
        $datasetData = $vis->datasetData();

        // Prepare the query to get the count of items with a property value.
        $dql = '
        SELECT COUNT(DISTINCT v.resource)
        FROM Omeka\Entity\Value AS v
        WHERE v.resource IN (:item_ids)
        AND v.property = :property_id
        AND v.value = :value
        AND v.value IS NOT NULL
        AND v.value != \'\'';
        $query = $em->createQuery($dql);
        $query->setParameter('item_ids', $this->getItemIds($services, $vis));
        $query->setParameter('property_id', $datasetData['property_id']);

        $dataset = [];
        $values = array_filter(array_map('trim', explode("\n", $datasetData['values'] ?? '')));
        foreach ($values as $value) {
            $query->setParameter('value', $value);
            $dataset[] = [
                'label' => $value,
                'value' => (int) $query->getSingleScalarResult(),
            ];
        }
        return $dataset;
    }
}
