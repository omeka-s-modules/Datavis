/**
 * This diagram type will consume a dataset in the following format:
 * [{label: "{YYYY-MM-DDTHH:MM:SS}", value: {int}}]
 * It will also read the sample rate from the dataset data.
 */
Datavis.addDiagramType('histogram_time_series', (div, dataset, datasetData, diagramData, blockData) => {

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
    div.style.maxWidth = `${width + margin.left + margin.right}px`
    const svg = d3.select(div)
        .append('svg')
            .attr('viewBox', `0 0 ${width + margin.left + margin.right} ${height + margin.top + margin.bottom}`)
            .append('g')
                .attr('transform', `translate(${margin.left},${margin.top})`);

    dataset.map(d => {
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

    // Set the x and y scales.
    const x = d3.scaleTime()
        .range([0, width])
        .domain(d3.extent(dataset, d => d.datetime));
    const y = d3.scaleLinear()
        .range([height, 0])
        .domain([0, d3.max(dataset, d => d.value)]);

    // Add the X axis.
    const xGroup = svg.append('g')
        .attr('transform', `translate(0, ${height})`)
        .style('font-size', '14px')
        .call(d3.axisBottom(x));
    // Adjust the label position.
    const labels = xGroup.selectAll('text')
        .data(dataset)
        .attr('transform', 'translate(-10,0)rotate(-45)')
        .style('text-anchor', 'end');

    // Add the Y axis.
    const yGroup = svg.append('g')
        .style('font-size', '14px')
        .call(d3.axisLeft(y));

    // Add the tooltip div.
    const tooltip = d3.select(div)
        .append('div')
        .attr('class', 'tooltip');

    // Add the bars.
    svg.selectAll('bar')
        .data(dataset)
        .enter()
        .append('rect')
            .attr('x', d => x(d.datetime))
            .attr('y', d => y(d.value))
            .attr('width', (width / dataset.length) - 1)
            .attr('height', d => height - y(d.value))
            .attr('fill', '#69b3a2')
            .style('cursor', 'crosshair')
            .on('mousemove', (e, d) => {
                tooltip.style('display', 'inline-block')
                    .style('left', `${e.pageX + 2}px`)
                    .style('top', `${e.pageY + 2}px`)
                    .html(`${d.label_long ? d.label_long : d.label}: ${Number(d.value).toLocaleString()}`);
            })
            .on('mouseout', (e, d) => {
                tooltip.style('display', 'none');
            });
});
