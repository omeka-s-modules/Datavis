<?php
namespace Datavis;

use Omeka\Module\AbstractModule;
use Omeka\Permissions\Assertion as OmekaAssertion;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Permissions\Acl\Assertion as LaminasAssertion;
use Laminas\ServiceManager\ServiceLocatorInterface;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include sprintf('%s/config/module.config.php', __DIR__);
    }

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $acl = $this->getServiceLocator()->get('Omeka\Acl');

        $acl->allow(null, 'Datavis\Controller\Site\Index', ['dataset', 'diagram']);
        $acl->allow(null, 'Datavis\Controller\SiteAdmin\Index', ['index', 'browse',  'add', 'edit', 'delete', 'add-dataset-type', 'get-diagram-fieldset', 'dataset', 'diagram']);
        $acl->allow(null, 'Datavis\Api\Adapter\DatavisVisAdapter', ['search', 'read', 'create', 'update', 'delete']);

        // Provide the site-specific "add-visualization" privilege for creating
        // visualizations.
        $acl->allow(null, 'Omeka\Entity\Site', 'add-visualization', new OmekaAssertion\HasSitePermissionAssertion('admin'));

        // We give "create" privilege to every role so permission checks fall to
        // the site-specific "add-visualization" privilege (checked in the
        // DatavisVisAdapter API adapter). We do this instead of using a
        // HasSitePermissionAssertion because Omeka checks permissions before
        // the adapter hydrates the site.
        $acl->allow(null, 'Datavis\Entity\DatavisVis', ['read', 'create']);

        // Users who are not global admins or supervisors (site_admin) must be
        // site managers (admin) and must own the visualization they intend to
        // update or delete.
        $adminAssertion = new LaminasAssertion\AssertionAggregate;
        $adminAssertion->addAssertions([
            new OmekaAssertion\OwnsEntityAssertion,
            new OmekaAssertion\HasSitePermissionAssertion('admin'),
        ]);
        $acl->allow(null, 'Datavis\Entity\DatavisVis', ['update', 'delete'], $adminAssertion);
    }

    public function install(ServiceLocatorInterface $services)
    {
        $sql = <<<'SQL'
CREATE TABLE datavis_vis (id INT UNSIGNED AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, site_id INT NOT NULL, created DATETIME NOT NULL, modified DATETIME DEFAULT NULL, dataset_modified DATETIME DEFAULT NULL, dataset_type VARCHAR(255) NOT NULL, diagram_type VARCHAR(255) DEFAULT NULL, dataset_data LONGTEXT NOT NULL COMMENT '(DC2Type:json)', diagram_data LONGTEXT NOT NULL COMMENT '(DC2Type:json)', dataset LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, query LONGTEXT NOT NULL, INDEX IDX_A4B1D3067E3C61F9 (owner_id), INDEX IDX_A4B1D306F6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE datavis_vis ADD CONSTRAINT FK_A4B1D3067E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON DELETE SET NULL;
ALTER TABLE datavis_vis ADD CONSTRAINT FK_A4B1D306F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE;
SQL;
        $conn = $services->get('Omeka\Connection');
        $conn->exec('SET FOREIGN_KEY_CHECKS=0;');
        $conn->exec($sql);
        $conn->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function uninstall(ServiceLocatorInterface $services)
    {
        $conn = $services->get('Omeka\Connection');
        $conn->exec('SET FOREIGN_KEY_CHECKS=0;');
        $conn->exec('DROP TABLE IF EXISTS datavis_vis;');
        $conn->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        // Add the "datavis_id" filter to item search.
        $sharedEventManager->attach(
            'Omeka\Api\Adapter\ItemAdapter',
            'api.search.query',
            function (Event $event) {
                $query = $event->getParam('request')->getContent();
                if (!(isset($query['datavis_id']) && is_numeric($query['datavis_id']))) {
                    return;
                }
                $qb = $event->getParam('queryBuilder');
                $itemAdapter = $event->getTarget();
                $api = $this->getServiceLocator()->get('Omeka\ApiManager');
                $vis = $api->read('datavis_visualizations', $query['datavis_id'])->getContent();
                parse_str($vis->query(), $visQuery);
                $visQuery['site_id'] = $vis->site()->id();
                $visItemIds = $api->search('items', $visQuery, ['returnScalar' => 'id'])->getContent();
                $qb->andWhere($qb->expr()->in('omeka_root.id', $itemAdapter->createNamedParameter($qb, $visItemIds)));
            }
        );
        // Copy Datavis-related data for the CopyResources module.
        $sharedEventManager->attach(
            '*',
            'copy_resources.sites.post',
            function (Event $event) {
                $services = $this->getServiceLocator();
                $api = $services->get('Omeka\ApiManager');
                $connection = $services->get('Omeka\Connection');

                $site = $event->getParam('resource');
                $siteCopy = $event->getParam('resource_copy');
                $copyResources = $event->getParam('copy_resources');

                $copyResources->revertSiteBlockLayouts($siteCopy->id(), 'datavis');
                $copyResources->revertSiteNavigationLinkTypes($siteCopy->id(), 'datavis');

                // Copy visualizations.
                $visualizations = $api->search('datavis_visualizations', ['site_id' => $site->id()])->getContent();
                $visualizationmMap = [];
                foreach ($visualizations as $visualization) {
                    $callback = function (&$jsonLd) use ($siteCopy) {
                        unset($jsonLd['o:owner']);
                        $jsonLd['o:site']['o:id'] = $siteCopy->id();
                    };
                    $visualizationCopy = $copyResources->createResourceCopy('datavis_visualizations', $visualization, $callback);
                    $visualizationmMap[$visualization->id()] = $visualizationCopy->id();
                }

                // Copy the datasets, which are not copied in the above process.
                $sql = 'UPDATE datavis_vis v1
                    INNER JOIN datavis_vis v2
                    SET v1.dataset = v2.dataset, v1.dataset_modified = NOW()
                    WHERE v1.id = :vis_copy_id
                    AND v2.id = :vis_id';
                $stmt = $connection->prepare($sql);
                foreach ($visualizationmMap as $visId => $visCopyId) {
                    $stmt->bindValue('vis_copy_id', $visCopyId);
                    $stmt->bindValue('vis_id', $visId);
                    $stmt->executeStatement();
                }

                // Modify block data.
                $callback = function (&$data) use ($visualizationmMap) {
                    if (isset($data['id']) && is_numeric($data['id'])) {
                        $id = $data['id'];
                        $data['id'] = array_key_exists($id, $visualizationmMap) ? $visualizationmMap[$id] : $id;
                    }
                };
                $copyResources->modifySiteBlockData($siteCopy->id(), 'datavis', $callback);

                // Modify site navigation.
                $callback = function (&$link) use ($visualizationmMap) {
                    if (isset($link['data']['id']) && is_numeric($link['data']['id'])) {
                        $id = $link['data']['id'];
                        $link['data']['id'] = array_key_exists($id, $visualizationmMap) ? $visualizationmMap[$id] : $id;
                    }
                };
                $copyResources->modifySiteNavigation($siteCopy->id(), 'datavis', $callback);
            }
        );
    }
}
