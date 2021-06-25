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
     * Get all IDs of items that are in the dataset's search query.
     *
     * Note that the items are restricted to the visualization's site.
     *
     * @param ServiceManager $services
     * @param  DatavisVisRepresentation $vis
     * @return array
     */
    public function getItemIds(ServiceManager $services, DatavisVisRepresentation $vis)
    {
        $api = $services->get('Omeka\ApiManager');

        parse_str($vis->query(), $query);
        $query['site_id'] = $vis->site()->id();
        return $api->search('items', $query, ['returnScalar' => 'id'])->getContent();
    }
}
