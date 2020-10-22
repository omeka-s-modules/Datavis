<?php
namespace Datavis\DiagramType;

use Laminas\Form\Fieldset;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\SiteRepresentation;

class Unknown implements DiagramTypeInterface
{
    protected $name;

    public function __construct(?string $name)
    {
        $this->name = $name;
    }

    public function getLabel() : string
    {
        return '[Unknown]'; // @translate
    }

    public function addElements(SiteRepresentation $site, Fieldset $fieldset) : void
    {
    }

    public function prepareRender(PhpRenderer $view) : void
    {
    }
}
