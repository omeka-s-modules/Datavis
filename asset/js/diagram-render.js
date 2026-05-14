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
     * Parses an ISO 8601 date-time string into a native Date object normalised to UTC.
     *
     * Expected format: [-]YYYY-MM-DDTHH:mm:ss[Z|±HH:mm] (time and seconds are required).
     *
     * Specific behaviors:
     * 1. Negative years: parsed manually because `new Date()` fails on them.
     * 2. UTC enforcement: offsetless strings are treated as UTC; offset strings are shifted.
     * 3. Historical chronology: adds +1 to years < 0 to match historical BCE year numbering.
     *    Year 0000 (a PHP DatePeriod artifact for 1 BCE) is left unadjusted at UTC year 0.
     * 4. Years 0000–0099: `setUTCFullYear` is used to avoid the native Date 0-99 year bug.
     *
     * NOTE: When the TC39 Temporal API reaches broad support, this function can likely be
     *       simplified, though the BCE year adjustment (point 3) will remain necessary.
     *
     * @param {string} isoString
     * @returns {Date} Date object normalised to UTC.
     */
    parseISOString: isoString => {
        const match = isoString.match(/^(-?\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|[+-]\d{2}:\d{2})?$/);
        if (!match) return new Date(NaN);

        const [, y, mo, d, h, min, s, offset] = match;
        let year = parseInt(y, 10);
        if (year < 0) year += 1;

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
     * Wraps `Intl.DateTimeFormat` with two behavioral additions:
     * - UTC display: enforces `timeZone: 'UTC'` to match the UTC-normalised dates
     *   produced by `parseISOString`.
     * - BCE era label: appends `era: 'short'` for BCE dates (UTC year <= 0),
     *   producing labels like "500 BC".
     *
     * @param {Date} date - A Date object, e.g. as returned by `parseISOString`.
     * @param {Object} options - Standard `Intl.DateTimeFormat` options.
     * @returns {string} Locale-aware formatted date string.
     */
    formatDateTime: (date, options) => {
        const opts = { timeZone: 'UTC', ...options };
        if (date.getUTCFullYear() <= 0) opts.era = 'short';
        return new Intl.DateTimeFormat([], opts).format(date);
    }
};
