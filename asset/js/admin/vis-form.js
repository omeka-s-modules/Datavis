document.addEventListener('DOMContentLoaded', function(event) {
    // Handle diagram type change.
    const datasetType = document.getElementById('o-module-datavis:dataset_type');
    const diagramType = document.getElementById('o-module-datavis:diagram_type');
    const diagramElements = document.getElementById('o-module-datavis:diagram_data');
    const diagramElementsUrl = new URL(diagramElements.dataset.diagramElementsUrl);
    diagramType.addEventListener('focus', e => {
        // Save previous value on focus for change confirmation.
        diagramType.dataset.previousValue = diagramType.value;
    });
    diagramType.addEventListener('change', e => {
        if ('' !== diagramType.dataset.previousValue) {
            if (!confirm(diagramElements.dataset.diagramChangeConfirm)) {
                // Revert to the previous value and do nothing.
                diagramType.value = diagramType.dataset.previousValue;
                return;
            }
        }
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
