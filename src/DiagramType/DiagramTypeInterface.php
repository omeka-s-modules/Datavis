<?php
namespace Datavis\DiagramType;

use Laminas\Form\Fieldset;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\SiteRepresentation;

interface DiagramTypeInterface
{
    /**
     * Get the label of this diagram type.
     *
     * @return string
     */
    public function getLabel() : string;

    /**
     * Add the form elements used for the diagram data.
     *
     * @param SiteRepresentation $site
     * @param Fieldset $fieldset
     */
    public function addElements(SiteRepresentation $site, Fieldset $fieldset) : void;

    /**
     * Prepare the render of this diagram type.
     *
     * @param PhpRenderer $view
     */
    public function prepareRender(PhpRenderer $view) : void;
}
