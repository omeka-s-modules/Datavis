<?php
namespace Datavis\DatasetType;

use Datavis\Api\Representation\DatavisVisRepresentation;
use Doctrine\Common\Collections\Criteria;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\ServiceManager\ServiceManager;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Form\Element\ResourceClassSelect;

class ItemRelationships extends AbstractDatasetType
{
    public function getLabel() : string
    {
        return 'Item relationships'; // @translate
    }

    public function getDescription() : ?string
    {
        return 'Visualize the relationships between items via their resource values.'; // @translate
    }

    public function getDiagramTypeNames() : array
    {
        return ['network_graph', 'arc'];
    }

    public function addElements(SiteRepresentation $site, Fieldset $fieldset) : void
    {
        $fieldset->add([
            'type' => Element\Checkbox::class,
            'name' => 'exclude_unlinked',
            'options' => [
                'label' => 'Exclude items without relationships', // @translate
            ],
            'attributes' => [],
        ]);
    }

    public function getDataset(ServiceManager $services, DatavisVisRepresentation $vis) : array
    {
        $api = $services->get('Omeka\ApiManager');
        $conn = $services->get('Omeka\Connection');
        $urlHelper = $services->get('ViewHelperManager')->get('url');
        $hyperlinkHelper = $services->get('ViewHelperManager')->get('hyperlink');

        $nodes = [];
        $links = [];
        $linkedIds = [];

        $itemIds = $this->getItemIds($services, $vis);
        foreach ($itemIds as $itemId) {
            $item = $api->read('items', $itemId)->getContent();
            if (null === $item) {
                // This item does not exist.
                continue;
            }
            $resourceClass = $item->resourceClass();
            $sourceUrl = $urlHelper(
                'site/resource-id',
                ['site-slug' => $vis->site()->slug(), 'controller' => 'item', 'id' => $item->id()],
                ['force_canonical' => true]
            );
            $nodes[$item->id()] = [
                'id' => $item->id(),
                'label' => $item->displayTitle(),
                'comment' => nl2br($item->displayDescription()),
                'url' => $sourceUrl,
                'group_id' => $resourceClass ? $resourceClass->id() : null,
                'group_label' => $resourceClass ? $resourceClass->label() : null,
            ];
            foreach ($item->objectValues() as $value) {
                $valueResource = $value->valueResource();
                if (!in_array($valueResource->id(), $itemIds)) {
                    // Object resource not in item pool. Do not make a link.
                    continue;
                }
                $property = $value->property();
                $propertyLabel = $property->label();
                $resourceTemplate = $value->resource()->resourceTemplate();
                if ($resourceTemplate) {
                    $resourceTemplateProperty = $resourceTemplate->resourceTemplateProperty($property->id());
                    if ($resourceTemplateProperty) {
                        $propertyLabelAlternate = $resourceTemplateProperty->alternateLabel();
                        if ($propertyLabelAlternate) {
                            $propertyLabel = $propertyLabelAlternate;
                        }
                    }
                }
                $targetUrl = $urlHelper(
                    'site/resource-id',
                    ['site-slug' => $vis->site()->slug(), 'controller' => 'item', 'id' => $valueResource->id()],
                    ['force_canonical' => true]
                );
                $links[] = [
                    'source' => $item->id(),
                    'source_label' => $item->displayTitle(),
                    'source_url' => $sourceUrl,
                    'target' => $valueResource->id(),
                    'target_label' => $valueResource->displayTitle(),
                    'target_url' => $targetUrl,
                    'link_id' => $property->id(),
                    'link_label' => $propertyLabel,
                ];
                $linkedIds[] = $item->id();
                $linkedIds[] = $valueResource->id();
            }
        }

        // Exclude items without relationships if configured to do so.
        $excludeUnlinked = $vis->datasetData()['exclude_unlinked'] ?? false;
        if ($excludeUnlinked) {
            $allIds = array_keys($nodes);
            $linkedIds = array_unique($linkedIds);
            $unlinkedIds = array_diff($allIds, $linkedIds);
            foreach ($unlinkedIds as $unlinkedId) {
                unset($nodes[$unlinkedId]);
            }
        }

        return [
            'nodes' => array_values($nodes),
            'links' => $links,
        ];
    }
}
