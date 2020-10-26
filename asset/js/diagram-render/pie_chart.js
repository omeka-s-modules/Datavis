/**
 * This diagram type will consume a dataset in the following format:
 * [{label: {string}, value: {int}}]
 */
Datavis.addDiagramType('pie_chart', div => {

    const datasetData = Datavis.getDatasetData(div);
    const diagramData = Datavis.getDiagramData(div);
    const blockData = Datavis.getBlockData(div);

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

    // Add the tooltip div.
    const tooltip = d3.select(div)
        .append('div')
        .attr('class', 'tooltip');

    const color = d3.scaleOrdinal(['#4daf4a','#377eb8','#ff7f00','#984ea3','#e41a1c']);
    const arcGenerator = d3.arc().innerRadius(0).outerRadius(radius);

    // Parse the data.
    d3.json(div.dataset.datasetUrl).then(data => {

        // Sort the data.
        data.sort((b, a) => {
            return a.value - b.value;
        });

        // Calculate slice positions.
        const pie = d3.pie().value(d => d.value.value);
        const dataReady = pie(d3.entries(data));

        // Add the slices.
        svg.selectAll('slices')
            .data(dataReady)
            .enter()
            .append('path')
                .attr('d', arcGenerator)
                .attr('fill', d => color(d.data.key))
                .attr('stroke', 'black')
                .style('stroke-width', '1px')
                .style('opacity', 0.7)
                .on('mousemove', (e, d) => {
                    tooltip.style('display', 'inline-block')
                        .style('left', `${e.pageX}px`)
                        .style('top', `${e.pageY - 90}px`)
                        .style('opacity', 0.8)
                        .html(`${d.data.value.label_long ? d.data.value.label_long : d.data.value.label}<br>${Number(d.data.value.value).toLocaleString()}`);
                })
                .on('mouseout', (e, d) => {
                    tooltip.style('display', 'none');
                });

        // Add the labels to the slices.
        svg.selectAll('slices')
            .data(dataReady)
            .enter()
            .append('text')
                .text(d => d.data.value.label)
                .attr('transform', d => `translate(${arcGenerator.centroid(d)})`)
                .style('text-anchor', 'middle')
                .style('font-size', 14);
    });
});
