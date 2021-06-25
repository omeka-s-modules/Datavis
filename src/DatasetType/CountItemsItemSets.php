<?php
namespace Datavis\DatasetType;

use Datavis\Api\Representation\DatavisVisRepresentation;
use Laminas\Form\Fieldset;
use Laminas\ServiceManager\ServiceManager;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Form\Element\ItemSetSelect;

class CountItemsItemSets extends AbstractDatasetType
{
    public function getLabel() : string
    {
        return 'Count of items in item sets'; // @translate
    }

    public function getDescription() : ?string
    {
        return 'Visualize the count of items that are assigned to selected item sets.'; // @translate
    }

    public function getDiagramTypeNames() : array
    {
        return ['bar_chart', 'column_chart', 'pie_chart'];
    }

    public function addElements(SiteRepresentation $site, Fieldset $fieldset) : void
    {
        $fieldset->add([
            'type' => ItemSetSelect::class,
            'name' => 'item_set_ids',
            'options' => [
                'label' => 'Item sets', // @translate
                'query' => ['site_id' => $site->id()],
                'show_required' => true,
            ],
            'attributes' => [
                'multiple' => true,
                'required' => false,
                'class' => 'chosen-select',
                'data-placeholder' => 'Select item sets…', // @translate
            ],
        ]);
    }

    public function getDataset(ServiceManager $services, DatavisVisRepresentation $vis) : array
    {
        $em = $services->get('Omeka\EntityManager');

        // Prepare the query to get the count of items in an item set.
        $dql = '
        SELECT COUNT(i.id)
        FROM Omeka\Entity\Item AS i
        JOIN i.itemSets AS iset
        WHERE i.id IN (:item_ids)
        AND iset.id = :item_set_id';
        $query = $em->createQuery($dql);
        $query->setParameter('item_ids', $this->getItemIds($services, $vis));

        $dataset = [];
        $itemSetIds = $vis->datasetData()['item_set_ids'] ?? [];
        foreach ($itemSetIds as $itemSetId) {
            $itemSet = $em->find('Omeka\Entity\ItemSet', $itemSetId);
            if (null === $itemSet) {
                // This item set does not exist.
                continue;
            }
            $query->setParameter('item_set_id', $itemSet->getId());
            $dataset[] = [
                'id' => $itemSet->getId(),
                'label' => $itemSet->getTitle(),
                'value' => (int) $query->getSingleScalarResult(),
            ];
        }
        return $dataset;
    }
}
