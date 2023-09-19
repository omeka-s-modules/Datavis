window.addEventListener('DOMContentLoaded', (e) => {
    Datavis.renderDiagrams();
});

const Datavis = {
    diagramTypes: {},

    /**
     * Render all diagrams on the page.
     */
    renderDiagrams: () => {
        document.querySelectorAll(`.datavis-diagram`).forEach(div => {
            Datavis.renderDiagram(div);
        });
    },

    /**
     * Render a diagram on the page.
     *
     * @param object div The diagram container div
     */
    renderDiagram: div => {
        const diagramType = div.dataset.diagramType;
        if (diagramType in Datavis.diagramTypes) {
            // Fetch the dataset from the endpoint then call the function that
            // is responsible for rendering the diagram.
            d3.json(div.dataset.datasetUrl).then(dataset => {
                Datavis.diagramTypes[diagramType](
                    div,
                    dataset,
                    JSON.parse(div.dataset.datasetData),
                    JSON.parse(div.dataset.diagramData),
                    JSON.parse(div.dataset.blockData)
                );
            });
        }
    },

    /**
     * Add a diagram type.
     *
     * @param string diagramType The diagram type
     * @param function callback The callback that renders the diagram
     */
    addDiagramType: (diagramType, callback) => {
        Datavis.diagramTypes[diagramType] = callback;
    },
};
