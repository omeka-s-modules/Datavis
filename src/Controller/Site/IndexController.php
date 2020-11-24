<?php
namespace Datavis\Controller\Site;

use DateTime;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function datasetAction()
    {
        $vis = $this->api()->read('datavis_visualizations', $this->params('id'))->getContent();

        $dateFormat = 'D, d M Y H:i:s \G\M\T';
        $lastModified = $vis->datasetModified()->format($dateFormat);
        $useCached = false;

        // Tell the browser to use the cached version if the dataset has not
        // been modified since the last time it was cached.
        $request = $this->getRequest();
        $requestHeaders = $request->getHeaders();
        if ($requestHeaders->has('If-Modified-Since')) {
            $ifModifiedSince = DateTime::createFromFormat($dateFormat, $requestHeaders->get('If-Modified-Since'));
            if ($lastModified > $ifModifiedSince) {
                $useCached = true;
            }
        }

        // Set up the response depening on request.
        $response = $this->getResponse();
        $responseHeaders = $response->getHeaders();
        $responseHeaders->addHeaderLine('Cache-Control: no-cache');
        if ($useCached) {
            // Tell the browser to use cached version using 304 Not Modified.
            $response->setStatusCode(304);
        } else {
            // Send the first payload and tell the browser to cache it.
            $responseHeaders->addHeaderLine('Content-Type: application/json');
            $responseHeaders->addHeaderLine(sprintf('Last-Modified: %s', $lastModified));
            $dataset = $this->params()->fromQuery('pretty_print')
                ? json_encode($vis->dataset(), JSON_PRETTY_PRINT)
                : json_encode($vis->dataset());
            $response->setContent($dataset);
        }
        return $response;
    }

    public function diagramAction()
    {
        $vis = $this->api()->read('datavis_visualizations', $this->params('id'))->getContent();

        $view = new ViewModel;
        $view->setVariable('vis', $vis);
        return $view;
    }
}
