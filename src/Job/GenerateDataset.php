<?php
namespace Datavis\Job;

use DateTime;
use Datavis\Entity\DatavisVis;
use Omeka\Job\AbstractJob;
use Omeka\Job\Exception;

class GenerateDataset extends AbstractJob
{
    public function perform()
    {
        $services = $this->getServiceLocator();
        $em = $services->get('Omeka\EntityManager');
        $api = $services->get('Omeka\ApiManager');

        // Validate the visualization.
        $visId = $this->getArg('datavis_vis_id');
        if (!is_numeric($visId)) {
            throw new Exception\RuntimeException('Missing datavis_vis_id');
        }
        $entity = $em->find(DatavisVis::class, $visId);
        if (null === $entity) {
            throw new Exception\RuntimeException('Cannot find visualization');
        }

        // Get the dataset.
        $vis = $api->read('datavis_visualizations', $visId)->getContent();
        $dataset = $vis->getDatasetType()->getDataset($services, $vis);

        // Update the visualization.
        $entity->setDataset($dataset);
        $entity->setDatasetModified(new DateTime('now'));
        $em->flush();
    }
}
