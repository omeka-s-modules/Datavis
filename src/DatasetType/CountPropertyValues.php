<?php
namespace Datavis\DatasetType;

use Datavis\Api\Representation\DatavisVisRepresentation;
use Datavis\Form\Element as DatavisElement;
use Laminas\Form\Fieldset;
use Laminas\ServiceManager\ServiceManager;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Form\Element\PropertySelect;

class CountPropertyValues extends AbstractDatasetType
{
    public function getLabel() : string
    {
        return 'Count of property values'; // @translate
    }

    public function getDescription() : ?string
    {
        return 'Visualize the count of values of a selected property.'; // @translate
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
            'type' => DatavisElement\OptionalNumber::class,
            'name' => 'count_min',
            'options' => [
                'label' => 'Minimum count', // @translate
            ],
            'attributes' => [
                'id' => 'count_min',
                'placeholder' => 'No minimum', // @translate
                'required' => false,
            ],
        ]);
        $fieldset->add([
            'type' => DatavisElement\OptionalNumber::class,
            'name' => 'count_max',
            'options' => [
                'label' => 'Maximum count', // @translate
            ],
            'attributes' => [
                'id' => 'count_max',
                'placeholder' => 'No maximum', // @translate
                'required' => false,
            ],
        ]);
    }

    public function getDataset(ServiceManager $services, DatavisVisRepresentation $vis) : array
    {
        $em = $services->get('Omeka\EntityManager');
        $datasetData = $vis->datasetData();

        // Prepare the query to get the count of property values.
        $dql = '
        SELECT v.value, COUNT(v.value) AS count
        FROM Omeka\Entity\Value AS v
        WHERE v.resource IN (:item_ids)
        AND v.property = :property_id
        AND v.value IS NOT NULL
        AND v.value != \'\'
        GROUP BY v.value';
        $query = $em->createQuery($dql);
        $query->setParameter('item_ids', $this->getItemIds($services, $vis));
        $query->setParameter('property_id', $datasetData['property_id']);

        $dataset = [];
        foreach ($query->getResult() as $result) {
            if ($datasetData['count_min'] && ($result['count'] < $datasetData['count_min'])) {
                continue;
            }
            if ($datasetData['count_max'] && ($result['count'] > $datasetData['count_max'])) {
                continue;
            }
            $dataset[] = [
                'label' => $result['value'],
                'value' => (int) $result['count'],
            ];
        }
        return $dataset;
    }
}
