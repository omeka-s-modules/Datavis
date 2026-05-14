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

    /**
     * Parses a strict ISO 8601 date-time string into a native Date object.
     *
     * EXPECTED FORMAT: "[-]YYYY-MM-DDTHH:mm:ss[Z|±HH:mm]" (Time and seconds are required).
     *
     * Bypasses native/library limitations with these specific behaviors:
     * 1. Fixes D3 Crash: Avoids `d3.timeParse` failures on negative signs.
     * 2. Fixes Native BCE Crash: Parses negative years where `new Date()` fails.
     * 3. Enforces UTC: Assumes UTC for offsetless strings; applies offset shifts otherwise.
     * 4. Historical Chronology: Adds +1 to years <= 0 to match historical BCE timelines.
     * 5. Bypasses 20th-Century Bug: Uses `setUTCFullYear` to preserve years 0000–0099.
     *
     * NOTE: When the TC39 Temporal API reaches broad support, this function can likely be
     *       simplified significantly with `Temporal.ZonedDateTime.from(isoString)`, though
     *       the BCE year adjustment (point 4) will remain necessary.
     *
     * @param {string} isoString - Expected format: [-]YYYY-MM-DDTHH:mm:ss[Z|±HH:mm]
     * @returns {Date} Native Date object normalised to UTC.
     */
    parseISOString: isoString => {
        const match = isoString.match(/^(-?\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|[+-]\d{2}:\d{2})?$/);
        if (!match) return new Date(NaN);

        const [, y, mo, d, h, min, s, offset] = match;
        let year = parseInt(y, 10);
        if (year <= 0) year += 1;

        const date = new Date(0);
        date.setUTCFullYear(year, parseInt(mo, 10) - 1, parseInt(d, 10));
        date.setUTCHours(parseInt(h, 10), parseInt(min, 10), parseInt(s, 10), 0);

        if (offset && offset !== 'Z') {
            const sign = offset.startsWith('+') ? 1 : -1;
            const [oh, om] = offset.slice(1).split(':').map(n => parseInt(n, 10));
            date.setTime(date.getTime() - sign * (oh * 60 + om) * 60000);
        }

        return date;
    },

    /**
     * Formats a native Date object as a locale-aware string.
     *
     * Wraps `Intl.DateTimeFormat` with one behavioral addition:
     * - BCE Era Label: Automatically appends `era: 'short'` for negative years (year <= 0),
     *   producing labels like "500 BCE". Has no effect on CE dates.
     *
     * @param {Date} date - A native Date object, e.g. as returned by `parseISOString`.
     * @param {Object} options - Standard `Intl.DateTimeFormat` options.
     * @returns {string} Locale-aware formatted date string.
     */
    formatDateTime: (date, options) => {
        const opts = date.getUTCFullYear() <= 0 ? { ...options, era: 'short' } : options;
        return new Intl.DateTimeFormat([], opts).format(date);
    }
};
