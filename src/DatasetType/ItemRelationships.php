<?php
namespace Datavis\DatasetType;

use Datavis\Api\Representation\DatavisVisRepresentation;
use Datavis\Form\Element\GroupByControl;
use Datavis\Form\Element\OptionalPropertySelect;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\ServiceManager\ServiceManager;
use Omeka\Api\Exception\NotFoundException;
use Omeka\Api\Representation\SiteRepresentation;

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
        return ['network_graph', 'arc_vertical'];
    }

    public function addElements(SiteRepresentation $site, Fieldset $fieldset) : void
    {
        $fieldset->add([
            'type' => GroupByControl::class,
            'name' => 'group_by_control',
            'options' => [
                'label' => 'Group by', // @translate
            ],
            'attributes' => [],
        ]);
        $fieldset->add([
            'type' => OptionalPropertySelect::class,
            'name' => 'limit_properties',
            'options' => [
                'label' => 'Limit relationships to these properties', // @translate
                'info' => 'Selecting no properties will include all relationships.', // @translate
                'empty_option' => '',
            ],
            'attributes' => [
                'multiple' => true,
                'class' => 'chosen-select',
                'data-placeholder' => 'Select properties…', // @translate
            ],
        ]);
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

        $groupByControl = $vis->datasetData()['group_by_control'] ?? [];
        $groupBy = $groupByControl['group_by'] ?? 'resource_class';
        $groupByPropertyId = $groupByControl['group_by_property'] ?? null;
        $limitProperties = $vis->datasetData()['limit_properties'] ?? false;
        $excludeUnlinked = $vis->datasetData()['exclude_unlinked'] ?? false;

        // Get the property for group by "property_value". Fall back on
        // "resource_class" if the property is invalid.
        if ('property_value' === $groupBy) {
            if (is_numeric($groupByPropertyId)) {
                try {
                    $groupByProperty = $api->read('properties', $groupByPropertyId)->getContent();
                } catch (NotFoundException $e) {
                    $groupBy = 'resource_class';
                }
            } else {
                $groupBy = 'resource_class';
            }
        }

        $nodes = [];
        $links = [];
        $linkedIds = [];
        // Set the group cache array. Use this to assign IDs to groups that
        // don't have natural IDs. Note that we assign null to 0 index so
        // ordering works in the diagram.
        $groupCache = [0 => null];

        $itemIds = $this->getItemIds($services, $vis);
        foreach ($itemIds as $itemId) {
            $item = $api->read('items', $itemId)->getContent();
            if (null === $item) {
                // This item does not exist.
                continue;
            }
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
            ];
            // Set the group
            $resourceClass = $item->resourceClass();
            $resourceTemplate = $item->resourceTemplate();
            switch ($groupBy) {
                case 'property_value':
                    // Get the first property value, set the group label, and give
                    // every unique label a unique ID. Note that we get the group
                    // label according to the data type.
                    $propertyValue = $item->value($groupByProperty->term());
                    $groupLabel = null;
                    if ($propertyValue) {
                        switch ($propertyValue->type()) {
                            case 'resource':
                            case 'resource:item':
                            case 'resource:itemset':
                            case 'resource:media':
                                // Get the title of the value resource.
                                $groupLabel = $propertyValue->valueResource()->title();
                                break;
                            case 'literal':
                            case 'uri':
                            default:
                                // Cast all other value objects to a string so individual
                                // data types can return a literal-like value.
                                $groupLabel = (string) $propertyValue;
                        }
                    }
                    if (!in_array($groupLabel, $groupCache)) {
                        $groupCache[] = $groupLabel;
                    }
                    $nodes[$item->id()]['group_id'] = array_search($groupLabel, $groupCache);
                    $nodes[$item->id()]['group_label'] = $groupLabel;
                    break;
                case 'resource_template':
                    $nodes[$item->id()]['group_id'] = $resourceTemplate ? $resourceTemplate->id() : null;
                    $nodes[$item->id()]['group_label'] = $resourceTemplate ? $resourceTemplate->label() : null;
                    break;
                case 'resource_class':
                default:
                    $nodes[$item->id()]['group_id'] = $resourceClass ? $resourceClass->id() : null;
                    $nodes[$item->id()]['group_label'] = $resourceClass ? $resourceClass->label() : null;
            }
            foreach ($item->objectValues() as $value) {
                $valueResource = $value->valueResource();
                if (!in_array($valueResource->id(), $itemIds)) {
                    // Object resource not in item pool. Do not make a link.
                    continue;
                }
                $property = $value->property();
                if ($limitProperties && !in_array($property->id(), $limitProperties)) {
                    // Property not in the defined allow-list. Do not make a link.
                    continue;
                }
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
