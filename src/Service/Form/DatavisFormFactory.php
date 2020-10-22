<?php
namespace Datavis\Service\Form;

use Datavis\Form\DatavisForm;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class DatavisFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $form = new DatavisForm(null, $options);
        $form->setDatasetTypeManager($services->get('Datavis\DatasetTypeManager'));
        $form->setDiagramTypeManager($services->get('Datavis\DiagramTypeManager'));
        return $form;
    }
}
