/**
 * This diagram type will consume a dataset in the following format:
 * [{label: {string}, value: {int}}]
 */
Datavis.addDiagramType('bar_chart', (div, dataset, datasetData, diagramData, blockData) => {

    // Set the dimensions and margins of the graph.
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

    // Sort the dataset.
    dataset.sort((b, a) => {
        switch (diagramData.order) {
            case 'label_desc':
                return a.label.localeCompare(b.label);
                break;
            case 'label_asc':
                return b.label.localeCompare(a.label);
                break;
            case 'value_desc':
                return a.value - b.value;
                break;
            case 'value_asc':
            default:
                return b.value - a.value;
        }
    });

    // Set the x and y scales.
    const x = d3.scaleLinear()
        .range([0, width])
        .domain([0, Math.max(...dataset.map(d => d.value))]);
    const y = d3.scaleBand()
        .range([0, height]).padding(0.2)
        .domain(dataset.map(d => d.label));

    // Add the X axis.
    const xGroup = svg.append('g')
        .attr('transform', `translate(0, ${height})`)
        .style('font-size', '14px')
        .call(d3.axisBottom(x));
    xGroup.selectAll('text')
        .attr('transform', 'translate(-10,0)rotate(-45)')
        .style('text-anchor', 'end');

    // Add the Y axis.
    const yGroup = svg.append('g')
        .style('font-size', '14px')
        .call(d3.axisLeft(y));
    const labels = yGroup.selectAll('text').data(dataset);

    // Add the tooltip div.
    const tooltip = d3.select(div)
        .append('div')
        .attr('class', 'tooltip');

    // Add the bars.
    svg.selectAll('bar')
        .data(dataset)
        .enter()
        .append('rect')
            .attr('x', x(0))
            .attr('y', d => y(d.label))
            .attr('width', d => x(d.value))
            .attr('height', y.bandwidth())
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

    // Enable label links. Note that the dataset must include a "url" key.
    labels.on('click', (e, d) => {
        if (d.url) {
            window.location.href = d.url;
        }
    });
});
