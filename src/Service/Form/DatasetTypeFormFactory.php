<?php
namespace Datavis\Service\Form;

use Datavis\Form\DatasetTypeForm;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class DatasetTypeFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $form = new DatasetTypeForm(null, $options);
        $form->setDatasetTypeManager($services->get('Datavis\DatasetTypeManager'));
        return $form;
    }
}
