/**
 * This diagram type will consume a dataset in the following format:
 * {
 *     label: "{YYYY-MM-DDTHH:MM:SS}",
 *     value: {int}
 * }
 * It will also read the sample rate from the dataset data.
 */
Datavis.addDiagramType('line_chart_time_scale', div => {

    const datasetData = Datavis.getDatasetData(div);
    const diagramData = Datavis.getDiagramData(div);
    const blockData = Datavis.getBlockData(div);

    // Set the dimensions and margins of the chart.
    let width = diagramData.width ? parseInt(diagramData.width) : 700;
    let height = diagramData.height ? parseInt(diagramData.height) : 700;
    const margin = {
        top: diagramData.margin_top ? parseInt(diagramData.margin_top) : 30,
        right: diagramData.margin_right ? parseInt(diagramData.margin_right) : 30,
        bottom: diagramData.margin_bottom ? parseInt(diagramData.margin_bottom) : 100,
        left: diagramData.margin_left ? parseInt(diagramData.margin_left) : 60
    };
    width = width - margin.left - margin.right;
    height = height - margin.top - margin.bottom;

    // Add the svg.
    const svg = d3.select(div)
        .append('svg')
            .attr('width', width + margin.left + margin.right)
            .attr('height', height + margin.top + margin.bottom)
        .append('g')
            .attr('transform', `translate(${margin.left},${margin.top})`);

    // Add the cursor and text that snaps to the line.
    const cursor = svg.append('g')
        .append('circle')
            .attr('stroke', 'black')
            .attr('r', 8)
            .style('fill', 'none')
            .style('display', 'none');
    // Add the tooltip div.
    const tooltip = d3.select(div)
        .append('div')
        .attr('class', 'tooltip');

    // Set the curve type. Note that we limit the curve types to those where the
    // curve intersects all points on the chart.
    // @see https://github.com/d3/d3-shape#curves
    let curveType;
    switch (diagramData.line_type) {
        case 'monotonex':
            curveType = d3.curveMonotoneX;
            break;
        case 'natural':
            curveType = d3.curveNatural;
            break;
        case 'step':
            curveType = d3.curveStep;
            break;
        case 'stepafter':
            curveType = d3.curveStepAfter;
            break;
        case 'stepbefore':
            curveType = d3.curveStepBefore;
            break;
        case 'linear':
        default:
            curveType = d3.curveLinear;
    }

    // Set the x and y scales.
    const x = d3.scaleTime().range([0, width]);
    const y = d3.scaleLinear().range([height, 0]);

    // Parse the data.
    d3.json(div.dataset.datasetUrl).then(data => {

        data.map(d => {
            // Set the Date object needed by d3.
            d.datetime = d3.timeParse('%Y-%m-%dT%H:%M:%S')(d.label);
            // Format the label according to sample rate.
            let options;
            switch (datasetData.sample_rate) {
                case '10_years':
                case '5_years':
                case '1_year':
                    options = {year: 'numeric'};
                    break;
                case '6_months':
                case '1_month':
                    options = {year: 'numeric', month: 'long'};
                    break;
                case '7_days':
                case '1_day':
                    options = {year: 'numeric', month: 'long', day: 'numeric'};
                    break;
                case '1_hour':
                    options = {year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit'};
                    break;
                case '1_minute':
                    options = {year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'};
                    break;
                case '1_second':
                default:
                    options = {year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit'}
            }
            d.label = new Intl.DateTimeFormat([], options).format(d.datetime)
            return d;
        });

        // Set the x and y domains.
        x.domain(d3.extent(data, d => d.datetime));
        y.domain([0, d3.max(data, d => d.value)]);

        // Add the X axis.
        const xGroup = svg.append('g')
            .attr('transform', `translate(0, ${height})`)
            .style('font-size', '14px')
            .call(d3.axisBottom(x));
        // Adjust the label position.
        const labels = xGroup.selectAll('text')
            .data(data)
            .attr('transform', 'translate(-10,0)rotate(-45)')
            .style('text-anchor', 'end');

        // Add the Y axis.
        const yGroup = svg.append('g')
            .style('font-size', '14px')
            .call(d3.axisLeft(y));

        if ('points' !== diagramData.plot_type) {
            // Add the line.
            svg.append('path')
                .datum(data)
                .attr('fill', 'none')
                .attr('stroke', 'steelblue')
                .attr('stroke-width', 1.5)
                .attr('d', d3.line()
                    .x(d => x(d.datetime))
                    .y(d => y(d.value))
                    .curve(curveType)
                );
        }
        if ('line' !== diagramData.plot_type) {
            svg
              .append('g')
              .selectAll('dot')
              .data(data)
              .enter()
              .append('circle')
                .attr('cx', d => x(d.datetime))
                .attr('cy', d => y(d.value))
                .attr('r', 3)
                .attr('fill', 'steelblue');
        }

        // Add the overlay rectangle that enables mouse position.
        const bisect = d3.bisector(d => d.datetime).left;
        svg.append('rect')
            .attr('width', width)
            .attr('height', height)
            .style('fill', 'none')
            .style('pointer-events', 'all')
            .on('mouseover', () => {
                cursor.style('display', 'inline-block');
                tooltip.style('display', 'none')
            })
            .on('mousemove', (e) => {
                const x0 = x.invert(Math.round(d3.pointer(e)[0]));
                const thisData = data[bisect(data, x0, 0)];
                cursor
                    .attr('cx', x(thisData.datetime))
                    .attr('cy', y(thisData.value));
                tooltip.style('display', 'inline-block')
                    .style('left', `${e.pageX}px`)
                    .style('top', `${e.pageY - 90}px`)
                    .style('opacity', 0.8)
                    .html(`${thisData.label}<br>${Number(thisData.value).toLocaleString()}`);
            })
            .on('mouseout', () => {
                cursor.style('display', 'none');
                tooltip.style('display', 'none')
            });
    });
});
