<?php
namespace Datavis\ViewHelper;

use Laminas\Form\View\Helper\AbstractHelper;
use Laminas\Form\ElementInterface;

class GroupByControl extends AbstractHelper
{
    public function __invoke(ElementInterface $element)
    {
        return $this->render($element);
    }

    public function render(ElementInterface $element)
    {
        return $this->getView()->partial('common/datavis/view-helper/group-by-control', ['element' => $element]);
    }
}
