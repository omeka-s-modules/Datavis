document.addEventListener('DOMContentLoaded', function(event) {
    // Handle diagram type change.
    const datasetType = document.getElementById('o-module-datavis:dataset_type');
    const diagramType = document.getElementById('o-module-datavis:diagram_type');
    const diagramElements = document.getElementById('o-module-datavis:diagram_data');
    const diagramElementsUrl = new URL(diagramElements.dataset.diagramElementsUrl);
    diagramType.addEventListener('focus', e => {
        if ('' !== diagramType.value) {
            alert(diagramElements.dataset.diagramChangeConfirm);
        }
    });
    diagramType.addEventListener('change', e => {
        diagramType.blur();
        if ('' === diagramType.value) {
            diagramElements.innerHTML = '';
            return;
        }
        diagramElementsUrl.searchParams.set('dataset_type', datasetType.value);
        diagramElementsUrl.searchParams.set('diagram_type', diagramType.value);
        fetch(diagramElementsUrl, {'method': 'POST'})
            .then(response => {
                if (!response.ok) {
                  throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(data => {
                diagramElements.innerHTML = data;
            })
            .catch(error => {
                diagramElements.innerHTML = error;
            });
    });
    // Close page action menu if it is open and the user clicks outside it.
    document.addEventListener('click', e => {
        if (null === e.target.closest('#page-action-menu')) {
            const button = document.querySelector('#page-action-menu .collapse');
            if (button) {
                button.click()
            }
        }
    });
});
