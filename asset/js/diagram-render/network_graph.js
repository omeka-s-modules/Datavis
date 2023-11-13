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
Datavis.addDiagramType('network_graph', (div, dataset, datasetData, diagramData, blockData) => {

    let userInteracted = false;

    // Set the dimensions of the diagram.
    const width = diagramData.width ? parseInt(diagramData.width) : 700;
    const height = diagramData.height ? parseInt(diagramData.height) : 700;

    div.style.maxWidth = `${width}px`

    // Specify the color scale.
    const color = d3.scaleOrdinal(d3.schemeCategory10);

    // The force simulation mutates nodes and links, so create a copy so that
    // re-evaluating this cell produces the same result.
    const datasetLinks = dataset.links.map(link => ({...link}));
    const datasetNodes = dataset.nodes.map(node => ({...node}));

    // Get the tooltip.
    const tooltip = Datavis.ItemRelationships.getTooltip(div);

    // Create a simulation with several forces.
    const simulation = d3.forceSimulation(datasetNodes)
        .force("link", d3.forceLink(datasetLinks).id(node => node.id))
        .force("charge", d3.forceManyBody())
        .force("center", d3.forceCenter(width / 2, height / 2))
        .on("tick", ticked);

    // Create the SVG container.
    const svg = d3.select(div)
        .append('svg')
        .attr("width", width)
        .attr("height", height)
        .attr("viewBox", [0, 0, width, height])
        .attr("style", "max-width: 100%; height: auto;");

    // Add a line for each link.
    const links = svg.append("g")
        .attr("stroke", "#999")
        .attr("stroke-opacity", 0.5)
        .selectAll()
        .data(datasetLinks)
        .join("line")
        .attr("stroke-width", 1.5);

    links.append("title")
        .text(d => d.link_label);

    // Add a circle for each node.
    const nodes = svg.append("g")
        .attr("stroke", "#fff")
        .attr("stroke-width", 1.5)
        .selectAll()
        .data(datasetNodes)
        .join("circle")
        .attr("r", 10)
        .attr("fill", node => color(node.group_id))
        .call(g => g.append('title').text(node => node.label))
        .on('click', (event, node) => {
            const linked = Datavis.ItemRelationships.getLinked(node, dataset.links);
            const contentDiv = Datavis.ItemRelationships.getTooltipContent(node, linked, color);
            // Highlight this node, linked nodes, and links between them.
            nodes.attr('stroke', d => {
                return linked.nodes.includes(d.id) ? '#000' : '#fff';
            });
            nodes.attr('stroke-width', d => {
                return linked.nodes.includes(d.id) ? 2.5 : 1.5;
            });
            links.attr("stroke", l => {
                return (l.source.id === node.id || l.target.id === node.id) ? '#333' : '#999';
            });
            links.attr("stroke-opacity", l => {
                return (l.source.id === node.id || l.target.id === node.id) ? 1 : 0.5;
            });
            // Reset the tooltip's position.
            tooltip.style('transform', 'translate(0)');
            tooltip.position.x = 0;
            tooltip.position.y = 0;
            // Position and display the tooltip.
            tooltip.style('display', 'inline-block');
            tooltip.style('left', `${event.pageX + 6}px`);
            tooltip.style('top', `${event.pageY + 6}px`);
            tooltip.html(contentDiv.outerHTML);
        });

    // Add a drag behavior.
    nodes.call(d3.drag()
        .on("start", dragStarted)
        .on("drag", dragged)
        .on("end", dragEnded));

    // Set the position attributes of links and nodes each time the simulation ticks.
    function ticked() {
        links.attr("x1", link => link.source.x)
            .attr("y1", link => link.source.y)
            .attr("x2", link => link.target.x)
            .attr("y2", link => link.target.y);
        nodes.attr("cx", node => node.x)
            .attr("cy", node => node.y);

        // Auto-zoom with the expanding graph if there's no user interaction
        // (i.e. zoom and pan). We do this by changing the zoom as the force
        // expands beyond the bounds of the svg while cooling down.
        // @see https://stackoverflow.com/a/49993035
        if (!userInteracted) {
            const xExtent = d3.extent(nodes.data(), node => node.x);
            const yExtent = d3.extent(nodes.data(), node => node.y);
            const xScale = width / (xExtent[1] - xExtent[0]);
            const yScale = height / (yExtent[1] - yExtent[0]);
            const minScale = Math.min(xScale, yScale);
            if (minScale < 1) {
                const transform = d3.zoomIdentity
                    .translate(width / 2, height / 2)
                    .scale(minScale)
                    .translate(-(xExtent[0] + xExtent[1]) / 2, -(yExtent[0] + yExtent[1]) / 2)
                svg.call(zoom.transform, transform);
            }
        }
    }

    // Reheat the simulation when drag starts, and fix the subject position.
    function dragStarted(event) {
        if (!event.active) simulation.alphaTarget(0.3).restart();
        event.subject.fx = event.subject.x;
        event.subject.fy = event.subject.y;
    }

    // Update the subject (dragged node) position during drag.
    function dragged(event) {
        event.subject.fx = event.x;
        event.subject.fy = event.y;
    }

    // Restore the target alpha so the simulation cools after dragging ends.
    // Unfix the subject position now that it’s no longer being dragged.
    function dragEnded(event) {
        if (!event.active) simulation.alphaTarget(0);
        event.subject.fx = null;
        event.subject.fy = null;
    }

    // Enable zoom and pan.
    function zoomed(event) {
        // Detect user interaction. We need this to determine whether we need to
        // turn off the initial auto-zoom in ticked().
        if (!userInteracted && event.sourceEvent && ['MouseEvent', 'WheelEvent'].includes(event.sourceEvent.constructor.name)) {
            userInteracted = true;
        }
        links.attr("transform", event.transform);
        nodes.attr("transform", event.transform);
    }
    const zoom = d3.zoom().on("zoom", zoomed);
    svg.call(zoom);
});
