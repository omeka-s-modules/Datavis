/**
 * This diagram type will consume a dataset in the following format:
 * {
 *   nodes: [
 *     {
 *        id: <int>,
 *        label: <string>,
 *        comment: <string>,
 *        url: <string>,
 *        group_id: <int>,
 *        group_label: <string>
 *     }
 *   ],
 *   links: [
 *     {
 *       source: <int>,
 *       source_label: <string>,
 *       source_url: <string>,
 *       target: <int>,
 *       target_label: <string>,
 *       target_url: <string>,
 *       link_id: <int>,
 *       link_label: <string>,
 *     }
 *   ]
 * }
 *
 * @see https://observablehq.com/@d3/force-directed-graph/2
 */
Datavis.addDiagramType('arc_vertical', (div, dataset, datasetData, diagramData, blockData) => {

    const datasetLinks = dataset.links;
    const datasetNodes = dataset.nodes;

    const orders = new Map([]);
    orders.set('by_label', datasetNodes.sort((a, b) => a.label.localeCompare(b.label)).map(d => d.id));
    orders.set('by_group', datasetNodes.sort((a, b) => a.group_id - b.group_id).map(d => d.id));

    // Specify the chart’s dimensions.
    const width = diagramData.width ? parseInt(diagramData.width) : 800;
    const marginTop = diagramData.margin_top ? parseInt(diagramData.margin_top) : 30;
    const marginBottom = diagramData.margin_bottom ? parseInt(diagramData.margin_bottom) : 30;
    const marginLeft = diagramData.margin_left ? parseInt(diagramData.margin_left) : 200;
    const order = diagramData.order ? diagramData.order : 'by_group';
    const step = diagramData.step ? parseInt(diagramData.step) : 14;
    const labelFontSize = diagramData.label_font_size ? diagramData.label_font_size : 'medium';
    const height = (datasetNodes.length - 1) * step + marginTop + marginBottom;

    // Get the tooltip.
    const tooltip = Datavis.ItemRelationships.getTooltip(div);

    // The function to get the current position.
    const y = d3.scalePoint(orders.get(order), [marginTop, height - marginBottom]);

    // The current position, indexed by id. Will be interpolated.
    const Y = new Map(datasetNodes.map(node => [node.id, y(node.id)]));

    // A color scale for the nodes and links.
    const color = d3.scaleOrdinal()
        .domain(datasetNodes.map(node => node.group_id).sort(d3.ascending))
        .range(d3.schemeCategory10)
        .unknown("#aaa");

    const groups = new Map(datasetNodes.map(node => [node.id, node.group_id]));

    // Create the SVG container.
    const svg = d3.select(div)
        .append("svg")
        .attr("width", width)
        .attr("height", height)
        .attr("viewBox", [0, 0, width, height])
        .attr("style", "max-width: 100%; height: auto;");

    // Add an arc for each link.
    function arc(link) {
        const y1 = Y.get(link.source);
        const y2 = Y.get(link.target);
        const r = Math.abs(y2 - y1) / 2;
        return `M${marginLeft},${y1}A${r},${r} 0,0,${y1 < y2 ? 1 : 0} ${marginLeft},${y2}`;
    }
    const path = svg.insert("g", "*")
        .attr("fill", "none")
        .attr("stroke-opacity", 0.6)
        .attr("stroke-width", 1.5)
        .selectAll("path")
            .data(datasetLinks)
            .join("path")
                .attr("stroke", link => color(groups.get(link.source)))
                .attr("d", arc);

    // Add a text label and a dot for each node.
    const label = svg.append("g")
        .attr("font-family", "sans-serif")
        .attr("font-size", labelFontSize)
        .attr("text-anchor", "end")
        .selectAll("g")
            .data(datasetNodes)
            .join("g")
                .attr("transform", node => `translate(${marginLeft},${Y.get(node.id)})`)
                .call(g => g.append('title')
                    .text(node => node.label))
                .call(g => g.append("text")
                    .attr("x", -10)
                    .attr("dy", "0.35em")
                    .attr("fill", node => d3.lab(color(node.group_id)).darker(2))
                    .text(node => node.label))
                .call(g => g.append("circle")
                    .attr("r", 4)
                    .attr("fill", node => color(node.group_id)));

    // Add invisible rects that update the class of the elements on mouseover.
    let clickedNode = null;
    label.append("rect")
        .attr("fill", "none")
        .attr("width", marginLeft + 40)
        .attr("height", step)
        .attr("x", -marginLeft)
        .attr("y", -step / 2)
        .attr("fill", "none")
        .attr("pointer-events", "all")
        .on('mouseover', (event, node) => {
            label.classed("hovered", n => n === node);
        })
        .on('click', (event, node) => {
            if (clickedNode && clickedNode.id === node.id) {
                // Turn off label/path highlighting.
                svg.classed("hover", false);
                label.classed("primary", false);
                label.classed("secondary", false);
                path.classed("primary", false).order();
                clickedNode = null;
                return;
            }
            clickedNode = node;
            // Turn on label/path highlighting.
            svg.classed("hover", true);
            label.classed("primary", n => n === node);
            label.classed("secondary", n => {
                return datasetLinks.some(l => {
                    return (n.id === l.source && node.id == l.target) || (n.id === l.target && node.id === l.source);
                });
            });
            path.classed("primary", l => l.source === node.id || l.target === node.id).filter(".primary").raise();
            // Prepare the tooltip.
            const linked = Datavis.ItemRelationships.getLinked(node, dataset.links);
            const contentDiv = Datavis.ItemRelationships.getTooltipContent(node, linked, color);
            // Reset the tooltip's position.
            tooltip.style('transform', 'translate(0)');
            tooltip.position.x = 0;
            tooltip.position.y = 0;
            // Position and display the tooltip.
            tooltip.style('display', 'inline-block');
            tooltip.style('left', `${event.pageX + 10}px`);
            tooltip.style('top', `${event.pageY + 10}px`);
            tooltip.html(contentDiv.outerHTML);
        });

    // Add styles for the hover interaction.
    svg.append("style").text(`
        .hover text { fill: #aaa; }
        .hover g.primary text { font-weight: bold; fill: green; }
        .hover g.secondary text { fill: #333; }
        .hover g.hovered text { fill: #333; }
        .hover path { stroke: #ccc; }
        .hover path.primary { stroke: #333; }
    `);

});
