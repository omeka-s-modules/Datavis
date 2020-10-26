/**
 * This diagram type will consume a dataset in the following format:
 * [{label: {string}, value: {int}}]
 */
Datavis.addDiagramType('column_chart', div => {

    const datasetData = Datavis.getDatasetData(div);
    const diagramData = Datavis.getDiagramData(div);
    const blockData = Datavis.getBlockData(div);

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

    // Add the tooltip div.
    const tooltip = d3.select(div)
        .append('div')
        .attr('class', 'tooltip');

    // Set the x and y scales.
    const x = d3.scaleBand().range([0, width]).padding(0.2);
    const y = d3.scaleLinear().range([height, 0]);

    // Parse the data.
    d3.json(div.dataset.datasetUrl).then(data => {

        // Sort the data.
        data.sort((b, a) => {
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

        // Set the x and y domains.
        const maxValue = Math.max(...data.map(d => d.value));
        x.domain(data.map(d => d.label));
        y.domain([0, maxValue]);

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
        svg.append('g')
            .style('font-size', '14px')
            .call(d3.axisLeft(y));

        // Add the bars.
        svg.selectAll('bar')
            .data(data)
            .enter()
            .append('rect')
                .attr('x', d => x(d.label))
                .attr('y', d => y(d.value))
                .attr('width', x.bandwidth())
                .attr('height', d => height - y(d.value))
                .attr('fill', '#69b3a2')
                .on('mousemove', (e, d) => {
                    tooltip.style('display', 'inline-block')
                        .style('left', `${e.pageX}px`)
                        .style('top', `${e.pageY - 90}px`)
                        .style('opacity', 0.8)
                        .html(`${d.label_long ? d.label_long : d.label}<br>${Number(d.value).toLocaleString()}`);
                })
                .on('mouseout', (e, d) => {
                    tooltip.style('display', 'none');
                });

        // Enable label links. Note that the data must include a "url" key.
        labels.on('click', (e, d) => {
            if (d.url) {
                window.location.href = d.url;
            }
        });
    })
});
