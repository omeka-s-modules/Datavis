<?php
namespace Datavis\Controller\SiteAdmin;

use Datavis\Api\Representation\DatavisVisRepresentation;
use Datavis\Form;
use Datavis\Job\GenerateDataset;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Form\ConfirmForm;
use Omeka\Stdlib\Message;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
    }

    public function browseAction()
    {
        $this->setBrowseDefaults('created');
        $response = $this->api()->search('datavis_visualizations', $this->params()->fromQuery());
        $this->paginator($response->getTotalResults(), $this->params()->fromQuery('page'));
        $vises = $response->getContent();

        $view = new ViewModel;
        $view->setVariable('vises', $vises);
        return $view;
    }

    public function addDatasetTypeAction()
    {
        $form = $this->getForm(Form\DatasetTypeForm::class);

        if ($this->getRequest()->isPost()) {
            $post = $this->params()->fromPost();
            $form->setData($post);
            if ($form->isValid()) {
                $this->messenger()->addSuccess('Configure your visualization below.'); // @translate
                return $this->redirect()->toRoute(null, ['action' => 'add'], ['query' => ['dataset_type' => $post['o-module-datavis:dataset_type']]], true);
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        return $view;
    }

    public function addAction()
    {
        $datasetTypeName = $this->params()->fromQuery('dataset_type');
        $diagramTypeName = $this->params()->fromPost('o-module-datavis:diagram_type');

        if (!in_array($datasetTypeName, $this->datavis()->getDatasetTypeNames())) {
            // The dataset type is invalid.
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        $form = $this->getForm(Form\DatavisForm::class, [
            'dataset_type' => $datasetTypeName,
            'diagram_type' => $diagramTypeName,
            'site' => $this->currentSite(),
        ]);

        $datasetTypeLabel = $this->datavis()->getDatasetType($datasetTypeName)->getLabel();
        $form->get('dataset_type')->setValue($this->translate($datasetTypeLabel));

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            $form->setData($postData);
            if ($form->isValid()) {
                $formData = $form->getData();
                // Prepare form data for creation.
                $formData['o:site'] = ['o:id' => $this->currentSite()->id()];
                $response = $this->api($form)->create('datavis_visualizations', $formData);
                if ($response) {
                    $vis = $response->getContent();
                    $this->messenger()->addSuccess('Successfully added your visualization.'); // @translate
                    if (isset($postData['generate_dataset'])) {
                        $this->dispatchDatasetGeneration($vis);
                    }
                    if (isset($postData['submit_save_remain'])) {
                        return $this->redirect()->toRoute(null, ['action' => 'edit', 'id' => $vis->id()], true);
                    } else {
                        return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
                    }
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('datasetType', $this->datavis()->getDatasetType($datasetTypeName));
        return $view;
    }

    public function editAction()
    {
        $vis = $this->api()->read('datavis_visualizations', $this->params('id'))->getContent();
        $datasetTypeName = $vis->datasetType();
        $diagramTypeName = $this->params()->fromPost('o-module-datavis:diagram_type') ?? $vis->diagramType();

        $form = $this->getForm(Form\DatavisForm::class, [
            'dataset_type' => $datasetTypeName,
            'diagram_type' => $diagramTypeName,
            'site' => $this->currentSite(),
        ]);

        $datasetTypeLabel = $this->datavis()->getDatasetType($datasetTypeName)->getLabel();
        $form->get('dataset_type')->setValue($this->translate($datasetTypeLabel));

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            $form->setData($postData);
            if ($form->isValid()) {
                $formData = $form->getData();
                // Prepare form data for creation.
                $formData['o:site'] = ['o:id' => $this->currentSite()->id()];
                $response = $this->api($form)->update('datavis_visualizations', $vis->id(), $formData);
                if ($response) {
                    $this->messenger()->addSuccess('Successfully edited your visualization.'); // @translate
                    if (isset($postData['generate_dataset'])) {
                        $this->dispatchDatasetGeneration($vis);
                    }
                    if (isset($postData['submit_save_remain'])) {
                        return $this->redirect()->toRoute(null, ['action' => 'edit'], true);
                    } else {
                        return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
                    }
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        } else {
            $data = $vis->getJsonLd();
            $form->setData($data);
        }

        $view = new ViewModel;
        $view->setVariable('vis', $vis);
        $view->setVariable('form', $form);
        $view->setVariable('datasetType', $this->datavis()->getDatasetType($vis->datasetType()));
        return $view;
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isPost()) {
            $vis = $this->api()->read('datavis_visualizations', $this->params('id'))->getContent();
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $response = $this->api($form)->delete('datavis_visualizations', $vis->id());
                if ($response) {
                    $this->messenger()->addSuccess('Successfully deleted your visualization.'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
    }

    public function getDiagramFieldsetAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        $form = $this->getForm(Form\DatavisForm::class, [
            'dataset_type' => $this->params()->fromQuery('dataset_type'),
            'diagram_type' => $this->params()->fromQuery('diagram_type'),
            'site' => $this->currentSite(),
        ]);

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('form', $form);
        return $view;
    }

    protected function dispatchDatasetGeneration(DatavisVisRepresentation $vis)
    {
        $job = $this->jobDispatcher()->dispatch(
            GenerateDataset::class,
            ['datavis_vis_id' => $vis->id()]
        );
        $message = new Message(
            'Generating dataset. This may take a while. %s', // @translate
            sprintf(
                '<a href="%s">%s</a>',
                htmlspecialchars($this->url()->fromRoute('admin/id', ['controller' => 'job', 'id' => $job->getId()])),
                $this->translate('See this job for progress.')
            ));
        $message->setEscapeHtml(false);
        $this->messenger()->addSuccess($message);
    }
}
