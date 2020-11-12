<?php
namespace Datavis\DatasetType;

use Datavis\Api\Representation\DatavisVisRepresentation;
use Laminas\ServiceManager\ServiceManager;

abstract class AbstractDatasetType implements DatasetTypeInterface
{
    public function getDescription() : ?string
    {
        return null;
    }

    /**
     * Get all IDs of items that are in the dataset's item pool.
     *
     * Note that the items are restricted to the visualization's site.
     *
     * @param ServiceManager $services
     * @param  DatavisVisRepresentation $vis
     * @return array
     */
    public function getItemPoolIds(ServiceManager $services, DatavisVisRepresentation $vis)
    {
        $api = $services->get('Omeka\ApiManager');

        $itemPool = $vis->itemPool();
        $itemPool['site_id'] = $vis->site()->id();
        return $api->search('items', $itemPool, ['returnScalar' => 'id'])->getContent();
    }
}
