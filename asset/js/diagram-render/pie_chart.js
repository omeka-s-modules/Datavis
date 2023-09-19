/**
 * This diagram type will consume a dataset in the following format:
 * [{label: {string}, value: {int}}]
 */
Datavis.addDiagramType('pie_chart', (div, dataset, datasetData, diagramData, blockData) => {

    // Set the dimensions and margins of the chart.
    const width = diagramData.width ? parseInt(diagramData.width) : 700;
    const height = diagramData.height ? parseInt(diagramData.height) : 700;
    const margin = diagramData.margin ? parseInt(diagramData.margin) : 30;
    const radius = Math.min(width, height) / 2 - margin;

    div.style.maxWidth = `${width}px`
    const svg = d3.select(div)
        .append('svg')
            .attr('viewBox', `0 0 ${width} ${height}`)
            .append('g')
                .attr('transform', `translate(${width/2}, ${height/2})`);

    // Sort the data.
    dataset.sort((b, a) => {
        return a.value - b.value;
    });

    // Calculate slice positions.
    const pie = d3.pie().value(d => d.value.value);
    const datasetReady = pie(d3.entries(dataset));

    // Add the tooltip div.
    const tooltip = d3.select(div)
        .append('div')
        .attr('class', 'tooltip');

    const color = d3.scaleOrdinal(d3.schemeSet3);
    const arcGenerator = d3.arc().innerRadius(0).outerRadius(radius);

    // Add the slices.
    svg.selectAll('slices')
        .data(datasetReady)
        .enter()
        .append('path')
            .attr('d', arcGenerator)
            .attr('fill', d => color(d.data.key))
            .attr('stroke', 'black')
            .style('stroke-width', '1px')
            .style('cursor', 'crosshair')
            .on('mousemove', (e, d) => {
                tooltip.style('display', 'inline-block')
                    .style('left', `${e.pageX + 2}px`)
                    .style('top', `${e.pageY + 2}px`)
                    .html(`${d.data.value.label_long ? d.data.value.label_long : d.data.value.label}: ${Number(d.data.value.value).toLocaleString()}`);
            })
            .on('mouseout', (e, d) => {
                tooltip.style('display', 'none');
            });

    // Add the labels to the slices.
    svg.selectAll('slices')
        .data(datasetReady)
        .enter()
        .append('text')
            .text(d => d.data.value.label)
            .attr('transform', d => `translate(${arcGenerator.centroid(d)})`)
            .style('text-anchor', 'middle')
            .style('font-size', 14);
});
