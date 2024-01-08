<?php
namespace Datavis\Site\NavigationLink;

use Omeka\Api\Exception\NotFoundException;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Site\Navigation\Link\LinkInterface;
use Omeka\Stdlib\ErrorStore;

class Datavis implements LinkInterface
{
    public function getName()
    {
        return 'Data visualization'; // @translate
    }

    public function getFormTemplate()
    {
        return 'common/datavis/navigation-link-form/datavis';
    }

    public function isValid(array $data, ErrorStore $errorStore)
    {
        if (!isset($data['label']) || '' === trim((string) $data['id'])) {
            $errorStore->addError('o:navigation', 'Invalid navigation: Data visualization missing a label');
            return false;
        }
        if (!isset($data['id']) || !is_numeric($data['id'])) {
            $errorStore->addError('o:navigation', 'Invalid navigation: Data visualization missing a visualization');
            return false;
        }
        return true;
    }

    public function getLabel(array $data, SiteRepresentation $site)
    {
        if (isset($data['label']) && '' !== trim($data['label'])) {
            return $data['label'];
        }
        $services = $site->getServiceLocator();
        $api = $services->get('Omeka\ApiManager');
        $translator = $services->get('MvcTranslator');
        try {
            $response = $api->read('datavis_visualizations', $data['id']);
        } catch (NotFoundException $e) {
            return $translator->translate('[Missing Page]'); // @translate
        }
        return $response->getContent()->title();
    }

    public function toZend(array $data, SiteRepresentation $site)
    {
        return [
            'route' => 'site/datavis',
            'params' => [
                'site-slug' => $site->slug(),
                'action' => 'diagram',
                'id' => $data['id'],
            ],
        ];
    }

    public function toJstree(array $data, SiteRepresentation $site)
    {
        return [
            'label' => $data['label'],
            'id' => $data['id'],
        ];
    }
}
