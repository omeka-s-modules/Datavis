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
    const links = dataset.links.map(d => ({...d}));
    const nodes = dataset.nodes.map(d => ({...d}));

    // Create a simulation with several forces.
    const simulation = d3.forceSimulation(nodes)
        .force("link", d3.forceLink(links).id(d => d.id))
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
    const link = svg.append("g")
        .attr("stroke", "#999")
        .attr("stroke-opacity", 0.5)
        .selectAll()
        .data(links)
        .join("line")
        .attr("stroke-width", 1.5);

    link.append("title")
        .text(d => d.link_label);

    // Add a circle for each node.
    const node = svg.append("g")
        .attr("stroke", "#fff")
        .attr("stroke-width", 1.5)
        .selectAll()
        .data(nodes)
        .join("circle")
        .attr("r", 10)
        .attr("fill", d => color(d.group_id))
        .on('click', (e, d) => handleNodeClick(e, d));

    // Add a drag behavior.
    node.call(d3.drag()
        .on("start", dragStarted)
        .on("drag", dragged)
        .on("end", dragEnded));

    // Set the position attributes of links and nodes each time the simulation ticks.
    function ticked() {
        link.attr("x1", d => d.source.x)
            .attr("y1", d => d.source.y)
            .attr("x2", d => d.target.x)
            .attr("y2", d => d.target.y);
        node.attr("cx", d => d.x)
            .attr("cy", d => d.y);

        // Auto-zoom with the expanding graph if there's no user interaction
        // (i.e. zoom and pan). We do this by changing the zoom as the force
        // expands beyond the bounds of the svg while cooling down.
        // @see https://stackoverflow.com/a/49993035
        if (!userInteracted) {
            const xExtent = d3.extent(node.data(), function(d) {return d.x;});
            const yExtent = d3.extent(node.data(), function(d) {return d.y;});
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

    // Get all linked data of the passed node.
    function getLinked(d) {
        return dataset.links.reduce((linked, l) => {
            if (l.target === d.id) {
                linked.links.push(l);
                linked.nodes.push(l.source);
            } else if (l.source === d.id) {
                linked.links.unshift(l);
                linked.nodes.push(l.target);
            }
            return linked;
        }, {links: [], nodes: [d.id]});
    }

    // Enable zoom and pan.
    function zoomed(event) {
        // Detect user interaction. We need this to determine whether we need to
        // turn off the initial auto-zoom in ticked().
        if (!userInteracted && event.sourceEvent && ['MouseEvent', 'WheelEvent'].includes(event.sourceEvent.constructor.name)) {
            userInteracted = true;
        }
        link.attr("transform", event.transform);
        node.attr("transform", event.transform);
    }
    const zoom = d3.zoom().on("zoom", zoomed);
    svg.call(zoom);

    // Add the tooltip div.
    const tooltipDiv = document.createElement('div');
    tooltipDiv.classList.add('tooltip');
    div.appendChild(tooltipDiv);

    // Handle closing the tooltip.
    div.addEventListener('click', (event) => {
        const closeDiv = event.target.closest('.close-tooltip');
        if (!closeDiv) return;
        tooltipDiv.style.display = 'none';
    }, true);

    // Handle a node click.
    function handleNodeClick(e, d) {
            // Highlight this node and linked nodes.
            const linked = getLinked(d);
            node.attr('stroke', d => {
                return linked.nodes.includes(d.id) ? '#000' : '#fff';
            });
            node.attr('stroke-width', d => {
                return linked.nodes.includes(d.id) ? 2.5 : 1.5;
            });
            // Build the tooltip content.
            const contentDiv = document.createElement('div');
            const labelDiv = document.createElement('div');
            const groupDiv = document.createElement('div');
            const commentDiv = document.createElement('div');
            const linksTable = document.createElement('table');
            const closeDiv = document.createElement('div');
            // Add the label div.
            if (d.label && d.url) {
                const a = document.createElement('a');
                a.appendChild(document.createTextNode(d.label));
                a.title = d.label;
                a.href = d.url;
                a.target = '_blank';
                labelDiv.appendChild(a);
                contentDiv.appendChild(labelDiv);
            } else if (d.label)  {
                labelDiv.appendChild(document.createTextNode(d.label));
                contentDiv.appendChild(labelDiv);
            }
            // Add the group div.
            if (d.group_id && d.group_label) {
                groupDiv.appendChild(document.createTextNode(d.group_label));
                groupDiv.style.color = color(d.group_id);
                contentDiv.appendChild(groupDiv);
            }
            // Add the comment div.
            if (d.comment) {
                commentDiv.innerHTML = d.comment;
                contentDiv.appendChild(commentDiv);
            }
            // Add the links table.
            if (linked.links.length) {
                linked.links.forEach(l => {
                    const linkTr = document.createElement('tr');
                    // Add source node cell.
                    const sourceTd = document.createElement('td');
                    const sourceLink = document.createElement('a');
                    sourceLink.title = l.source_label;
                    sourceLink.href = l.source_url;
                    sourceLink.target = '_blank';
                    sourceLink.appendChild(document.createTextNode(l.source_label));
                    sourceTd.appendChild(sourceLink);
                    linkTr.appendChild(sourceTd);
                    // Add link cell.
                    const linkTd = document.createElement('td');
                    linkTd.appendChild(document.createTextNode(l.link_label));
                    linkTr.appendChild(linkTd);
                    // Add target node cell.
                    const targetTd = document.createElement('td');
                    const targetLink = document.createElement('a');
                    targetLink.title = l.target_label;
                    targetLink.href = l.target_url;
                    targetLink.target = '_blank';
                    targetLink.appendChild(document.createTextNode(l.target_label));
                    targetTd.appendChild(targetLink);
                    linkTr.appendChild(targetTd);
                    linksTable.appendChild(linkTr);
                });
                contentDiv.appendChild(linksTable);
            }
            // Add the close div.
            closeDiv.appendChild(document.createTextNode('✕'));
            closeDiv.classList.add('close-tooltip');
            closeDiv.style.position = 'absolute';
            closeDiv.style.right = 0;
            closeDiv.style.top = 0;
            closeDiv.style.cursor = 'default';
            contentDiv.appendChild(closeDiv);
            // Position and display the tooltip.
            tooltipDiv.style.display = 'inline-block';
            tooltipDiv.style.left = `${e.pageX + 6}px`;
            tooltipDiv.style.top = `${e.pageY + 6}px`;
            tooltipDiv.innerHTML = contentDiv.outerHTML;
    }

});
