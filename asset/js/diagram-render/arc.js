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
Datavis.addDiagramType('arc', (div, dataset, datasetData, diagramData, blockData) => {

    const links = dataset.links;
    const nodes = dataset.nodes;

    const orders = new Map([]);
    orders.set('by_label', nodes.sort((a, b) => a.label.localeCompare(b.label)).map(d => d.id));
    orders.set('by_group', nodes.sort((a, b) => a.group_id - b.group_id).map(d => d.id));

    // Specify the chart’s dimensions.
    const width = diagramData.width ? parseInt(diagramData.width) : 800;
    const step = diagramData.step ? parseInt(diagramData.step) : 14;
    const marginTop = diagramData.margin_top ? parseInt(diagramData.margin_top) : 30;
    const marginRight = diagramData.margin_right ? parseInt(diagramData.margin_right) : 30;
    const marginBottom = diagramData.margin_bottom ? parseInt(diagramData.margin_bottom) : 30;
    const marginLeft = diagramData.margin_left ? parseInt(diagramData.margin_left) : 200;
    const height = (nodes.length - 1) * step + marginTop + marginBottom;
    const y = d3.scalePoint(orders.get('by_group'), [marginTop, height - marginBottom]);

    // A color scale for the nodes and links.
    const color = d3.scaleOrdinal()
        .domain(nodes.map(d => d.group_id).sort(d3.ascending))
        .range(d3.schemeCategory10)
        .unknown("#aaa");

    const groups = new Map(nodes.map(d => [d.id, d.group_id]));

    // Create the SVG container.
    const svg = d3.select(div)
        .append("svg")
        .attr("width", width)
        .attr("height", height)
        .attr("viewBox", [0, 0, width, height])
        .attr("style", "max-width: 100%; height: auto;");

    // The current position, indexed by id. Will be interpolated.
    const Y = new Map(nodes.map(d => [d.id, y(d.id)]));

    // Add an arc for each link.
    function arc(d) {
        const y1 = Y.get(d.source);
        const y2 = Y.get(d.target);
        const r = Math.abs(y2 - y1) / 2;
        return `M${marginLeft},${y1}A${r},${r} 0,0,${y1 < y2 ? 1 : 0} ${marginLeft},${y2}`;
    }
    const path = svg.insert("g", "*")
        .attr("fill", "none")
        .attr("stroke-opacity", 0.6)
        .attr("stroke-width", 1.5)
        .selectAll("path")
            .data(links)
            .join("path")
                .attr("stroke", d => {
                    console.log(d);
                    return color(groups.get(d.source))
                })
                .attr("d", arc);

    // Add a text label and a dot for each node.
    const label = svg.append("g")
        .attr("font-family", "sans-serif")
        .attr("font-size", 10)
        .attr("text-anchor", "end")
        .selectAll("g")
            .data(nodes)
            .join("g")
                .attr("transform", d => `translate(${marginLeft},${Y.get(d.id)})`)
                .call(g => g.append("text")
                    .attr("x", -10)
                    .attr("dy", "0.35em")
                    .attr("fill", d => d3.lab(color(d.group_id)).darker(2))
                    .text(d => d.label))
                .call(g => g.append("circle")
                    .attr("r", 4)
                    .attr("fill", d => color(d.group_id)));

    // Add invisible rects that update the class of the elements on mouseover.
    label.append("rect")
        .attr("fill", "none")
        .attr("width", marginLeft + 40)
        .attr("height", step)
        .attr("x", -marginLeft)
        .attr("y", -step / 2)
        .attr("fill", "none")
        .attr("pointer-events", "all")
        .on("pointerenter", (event, d) => {
            svg.classed("hover", true);
            label.classed("primary", n => n === d);
            label.classed("secondary", n => links.some(({source, target}) => (
                n.id === source && d.id == target || n.id === target && d.id === source
            )));
            path.classed("primary", l => l.source === d.id || l.target === d.id).filter(".primary").raise();
        })
        .on("pointerout", () => {
            svg.classed("hover", false);
            label.classed("primary", false);
            label.classed("secondary", false);
            path.classed("primary", false).order();
        });

    // Add styles for the hover interaction.
    svg.append("style").text(`
        .hover text { fill: #aaa; }
        .hover g.primary text { font-weight: bold; fill: #333; }
        .hover g.secondary text { fill: #333; }
        .hover path { stroke: #ccc; }
        .hover path.primary { stroke: #333; }
    `);

});
