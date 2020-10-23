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
            Datavis.diagramTypes[diagramType](div);
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

    /**
     * Get dataset data.
     *
     * @param object div The diagram container div
     * @return object
     */
    getDatasetData: div => JSON.parse(div.dataset.datasetData),

    /**
     * Get diagram data.
     *
     * @param object div The diagram container div
     * @return object
     */
    getDiagramData: div => JSON.parse(div.dataset.diagramData),

    /**
     * Get block data.
     *
     * @param object div The diagram container div
     * @return object
     */
    getBlockData: div => JSON.parse(div.dataset.blockData)
};
