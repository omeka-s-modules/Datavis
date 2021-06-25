<?php
namespace Datavis\Api\Adapter;

use DateTime;
use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Entity\Site;
use Omeka\Stdlib\ErrorStore;

class DatavisVisAdapter extends AbstractEntityAdapter
{
    protected $sortFields = [];

    public function getResourceName()
    {
        return 'datavis_visualizations';
    }

    public function getRepresentationClass()
    {
        return 'Datavis\Api\Representation\DatavisVisRepresentation';
    }

    public function getEntityClass()
    {
        return 'Datavis\Entity\DatavisVis';
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        if (isset($query['has_diagram_type'])) {
            if ($query['has_diagram_type']) {
                $qb->andWhere($qb->expr()->isNotNull('omeka_root.diagramType'));
            } else {
                $qb->andWhere($qb->expr()->isNull('omeka_root.diagramType'));
            }
        }
        if (isset($query['has_dataset'])) {
            if ($query['has_dataset']) {
                $qb->andWhere($qb->expr()->isNotNull('omeka_root.dataset'));
            } else {
                $qb->andWhere($qb->expr()->isNull('omeka_root.dataset'));
            }
        }
    }

    public function validateRequest(Request $request, ErrorStore $errorStore)
    {
        $data = $request->getContent();
        if (Request::CREATE === $request->getOperation()) {
            // These values are unalterable after creation.
            if (!isset($data['o:site']['o:id'])) {
                $errorStore->addError('o:site', 'A site must have an ID'); // @translate
            }
            if (!isset($data['o-module-datavis:dataset_type']) || !is_string($data['o-module-datavis:dataset_type'])) {
                $errorStore->addError('o-module-datavis:dataset_type', 'dataset_type must be a string'); // @translate
            }
        }
        if (isset($data['o-module-datavis:dataset_data']) && !is_array($data['o-module-datavis:dataset_data'])) {
            $errorStore->addError('o-module-datavis:dataset_data', 'dataset_data must be an array'); // @translate
        }
        if (isset($data['o-module-datavis:diagram_data']) && !is_array($data['o-module-datavis:diagram_data'])) {
            $errorStore->addError('o-module-datavis:diagram_data', 'diagram_data must be an array'); // @translate
        }
    }

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore)
    {
        if (Request::CREATE === $request->getOperation()) {
            $siteData = $request->getValue('o:site');
            $site = $this->getAdapter('sites')->findEntity($siteData['o:id']);
            $entity->setSite($site);

            $datasetType = $request->getValue('o-module-datavis:dataset_type');
            $entity->setDatasetType($datasetType);
        }
        if (Request::UPDATE === $request->getOperation()) {
            $entity->setModified(new DateTime('now'));
        }
        $this->hydrateOwner($request, $entity);
        if ($this->shouldHydrate($request, 'o-module-datavis:diagram_type')) {
            $entity->setDiagramType($request->getValue('o-module-datavis:diagram_type'));
        }
        if ($this->shouldHydrate($request, 'o-module-datavis:dataset_data')) {
            $entity->setDatasetData($request->getValue('o-module-datavis:dataset_data'));
        }
        if ($this->shouldHydrate($request, 'o-module-datavis:diagram_data')) {
            $entity->setDiagramData($request->getValue('o-module-datavis:diagram_data'));
        }
        if ($this->shouldHydrate($request, 'o:title')) {
            $entity->setTitle($request->getValue('o:title'));
        }
        if ($this->shouldHydrate($request, 'o:description')) {
            $entity->setDescription($request->getValue('o:description'));
        }
        if ($this->shouldHydrate($request, 'o:query')) {
            $entity->setQuery($request->getValue('o:query'));
        }
    }

    public function validateEntity(EntityInterface $entity, ErrorStore $errorStore)
    {
        if (!($entity->getSite() instanceof Site)) {
            $errorStore->addError('o:site', 'A visualization must have a site'); // @translate
        }
        if (!is_string($entity->getDatasetType())) {
            $errorStore->addError('o:site', 'A visualization must have a dataset type'); // @translate
        }
        if (!is_string($entity->getTitle())) {
            $errorStore->addError('o:site', 'A visualization must have a title'); // @translate
        }
    }
}
