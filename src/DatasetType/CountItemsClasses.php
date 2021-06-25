<?php
namespace Datavis\DatasetType;

use Datavis\Api\Representation\DatavisVisRepresentation;
use Laminas\Form\Fieldset;
use Laminas\ServiceManager\ServiceManager;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Form\Element\ResourceClassSelect;

class CountItemsClasses extends AbstractDatasetType
{
    public function getLabel() : string
    {
        return 'Count of items with classes'; // @translate
    }

    public function getDescription() : ?string
    {
        return 'Visualize the count of items that are instances of selected resource classes.'; // @translate
    }

    public function getDiagramTypeNames() : array
    {
        return ['bar_chart', 'column_chart', 'pie_chart'];
    }

    public function addElements(SiteRepresentation $site, Fieldset $fieldset) : void
    {
        $fieldset->add([
            'type' => ResourceClassSelect::class,
            'name' => 'class_ids',
            'options' => [
                'label' => 'Classes', // @translate
                'show_required' => true,
            ],
            'attributes' => [
                'multiple' => true,
                'required' => false,
                'class' => 'chosen-select',
                'data-placeholder' => 'Select classes…', // @translate
            ],
        ]);
    }

    public function getDataset(ServiceManager $services, DatavisVisRepresentation $vis) : array
    {
        $em = $services->get('Omeka\EntityManager');

        // Prepare the query to get the count of items with a class.
        $dql = '
        SELECT COUNT(i.id)
        FROM Omeka\Entity\Item AS i
        JOIN i.resourceClass AS rc
        WHERE i.id IN (:item_ids)
        AND rc.id = :class_id';
        $query = $em->createQuery($dql);
        $query->setParameter('item_ids', $this->getItemIds($services, $vis));

        $dataset = [];
        $classIds = $vis->datasetData()['class_ids'] ?? [];
        foreach ($classIds as $classId) {
            $class = $em->find('Omeka\Entity\ResourceClass', $classId);
            if (null === $class) {
                // This class does not exist.
                continue;
            }
            $vocab = $class->getVocabulary();
            $query->setParameter('class_id', $class->getId());
            $dataset[] = [
                'id' => $class->getId(),
                'label' => $class->getLabel(),
                'label_long' => sprintf('%s (%s)', $class->getLabel(), $vocab->getLabel()),
                'value' => (int) $query->getSingleScalarResult(),
            ];
        }
        return $dataset;
    }
}
