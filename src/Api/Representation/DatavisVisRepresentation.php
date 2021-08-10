<?php
namespace Datavis\Api\Representation;

use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\AbstractEntityRepresentation;

class DatavisVisRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLdType()
    {
        return 'o-module-datavis:Visualization';
    }

    public function getJsonLd()
    {
        $owner = $this->owner();
        $site = $this->site();
        $modified = $this->modified();
        $datasetModified = $this->datasetModified();
        return [
            'o:site' => $site->getReference(),
            'o:owner' => $owner ? $owner->getReference() : null,
            'o:created' => $this->getDateTime($this->created()),
            'o:modified' => $modified ? $this->getDateTime($modified) : null,
            'o-module-datavis:dataset_modified' => $datasetModified ? $this->getDateTime($datasetModified) : null,
            'o-module-datavis:dataset_type' => $this->datasetType(),
            'o-module-datavis:diagram_type' => $this->diagramType(),
            'o-module-datavis:dataset_data' => $this->datasetData(),
            'o-module-datavis:diagram_data' => $this->diagramData(),
            'o:title' => $this->title(),
            'o:description' => $this->description(),
            'o:query' => $this->query(),
        ];
    }

    public function adminUrl($action = null, $canonical = false)
    {
        $url = $this->getViewHelper('Url');
        return $url(
            'admin/site/slug/datavis',
            [
                'site-slug' => $this->site()->slug(),
                'id' => $this->id(),
                'action' => $action,
            ],
            ['force_canonical' => $canonical]
        );
    }

    public function owner()
    {
        return $this->getAdapter('users')->getRepresentation($this->resource->getOwner());
    }

    public function site()
    {
        return $this->getAdapter('sites')->getRepresentation($this->resource->getSite());
    }

    public function created()
    {
        return $this->resource->getCreated();
    }

    public function modified()
    {
        return $this->resource->getModified();
    }

    public function datasetModified()
    {
        return $this->resource->getDatasetModified();
    }

    public function datasetType()
    {
        return $this->resource->getDatasetType();
    }

    public function diagramType()
    {
        return $this->resource->getDiagramType();
    }

    public function datasetData()
    {
        return $this->resource->getDatasetData();
    }

    public function diagramData()
    {
        return $this->resource->getDiagramData();
    }

    public function dataset()
    {
        return $this->resource->getDataset();
    }

    public function title()
    {
        return $this->resource->getTitle();
    }

    public function description()
    {
        return $this->resource->getDescription();
    }

    public function query()
    {
        return $this->resource->getQuery();
    }

    public function getDatasetType()
    {
        $datasetTypes = $this->getServiceLocator()->get('Datavis\DatasetTypeManager');
        return $datasetTypes->get($this->datasetType());
    }

    public function getDiagramType()
    {
        $diagramTypes = $this->getServiceLocator()->get('Datavis\DiagramTypeManager');
        return $diagramTypes->get($this->diagramType());
    }

    public function datasetUrl(array $options = [])
    {
        $url = $this->getViewHelper('Url');
        return $url(
            'site/datavis',
            [
                'site-slug' => $this->site()->slug(),
                'action' => 'dataset',
                'id' => $this->id(),
            ],
            [
                'force_canonical' => true,
                'query' => ['pretty_print' => $options['pretty_print'] ?? false],
            ]
        );
    }

    public function diagramUrl()
    {
        $url = $this->getViewHelper('Url');
        return $url(
            'site/datavis',
            [
                'site-slug' => $this->site()->slug(),
                'action' => 'diagram',
                'id' => $this->id(),
            ],
            ['force_canonical' => true]
        );
    }

    public function diagram(PhpRenderer $view, array $blockData)
    {
        $view->headScript()->appendFile($view->assetUrl('js/diagram-render.js', 'Datavis'));
        $this->getDiagramType()->prepareRender($view);
        return sprintf(
            '<h5>%s</h5>
            <p>%s</p>
            <div class="datavis-diagram %s"
                data-id="%s"
                data-diagram-type="%s"
                data-dataset-url="%s"
                data-dataset-data="%s"
                data-diagram-data="%s"
                data-block-data="%s"></div>',
            $view->escapeHtml($this->title()),
            $view->escapeHtml($this->description()),
            $view->escapeHtml($this->diagramType()),
            $view->escapeHtml($this->id()),
            $view->escapeHtml($this->diagramType()),
            $view->escapeHtml($this->datasetUrl()),
            $view->escapeHtml(json_encode($this->datasetData())),
            $view->escapeHtml(json_encode($this->diagramData())),
            $view->escapeHtml(json_encode($blockData))
        );
    }
}
