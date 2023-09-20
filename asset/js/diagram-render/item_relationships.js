/**
 * Extend the Datavis object to include functionality needed by diagrams that
 * consume the item_relationships dataset.
 */
Datavis.ItemRelationships = {

    /**
     * Get all links related to the passed node.
     *
     * @param object node
     * @param object links
     * @return object
     */
    getLinked: (node, links) => {
        return links.reduce((linked, link) => {
            if (link.target === node.id) {
                linked.links.push(link);
                linked.nodes.push(link.source);
            } else if (link.source === node.id) {
                linked.links.unshift(link);
                linked.nodes.push(link.target);
            }
            return linked;
        }, {links: [], nodes: [node.id]});
    },

    /**
     * Set and get the tooltip.
     *
     * @param DOMObject div
     * @return D3Object
     */
    getTooltip: div => {
        // Add the tooltip.
        const tooltip = d3.select(div)
            .append('div')
            .attr('class', 'tooltip');
        tooltip.on('click', event => {
            const closeDiv = event.target.closest('.close-tooltip');
            if (!closeDiv) return;
            tooltip.style('display', 'none');
        });
        tooltip.classed('draggable', true);
        tooltip.position = {x: 0, y: 0};
        interact(tooltip.node()).draggable({
            listeners: {
                move (event) {
                    tooltip.position.x += event.dx;
                    tooltip.position.y += event.dy;
                    event.target.style.transform = `translate(${tooltip.position.x}px, ${tooltip.position.y}px)`;
                },
            }
        });
        return tooltip;
    },

    /**
     * Get the content of the tooltip.
     *
     * @param object node
     * @param object linked All links related to the pa
     * @param function color A D3 function that gets a color
     * @return DOMObject
     */
    getTooltipContent: (node, linked, color) => {
        // Build the tooltip content.
        const contentDiv = document.createElement('div');
        const labelDiv = document.createElement('div');
        const groupDiv = document.createElement('div');
        const commentDiv = document.createElement('div');
        const linksTable = document.createElement('table');
        const closeDiv = document.createElement('div');
        // Add the label div.
        if (node.label && node.url) {
            const a = document.createElement('a');
            a.appendChild(document.createTextNode(node.label));
            a.title = node.label;
            a.href = node.url;
            a.target = '_blank';
            labelDiv.appendChild(a);
            contentDiv.appendChild(labelDiv);
        } else if (node.label)  {
            labelDiv.appendChild(document.createTextNode(node.label));
            contentDiv.appendChild(labelDiv);
        }
        // Add the group div.
        if (node.group_id && node.group_label) {
            groupDiv.appendChild(document.createTextNode(node.group_label));
            groupDiv.style.color = color(node.group_id);
            contentDiv.appendChild(groupDiv);
        }
        // Add the comment div.
        if (node.comment) {
            commentDiv.innerHTML = node.comment;
            contentDiv.appendChild(commentDiv);
        }
        // Add the links table.
        if (linked.links.length) {
            linked.links.forEach(link => {
                const linkTr = document.createElement('tr');
                // Add source node cell.
                const sourceTd = document.createElement('td');
                const sourceLink = document.createElement('a');
                sourceLink.title = link.source_label;
                sourceLink.href = link.source_url;
                sourceLink.target = '_blank';
                sourceLink.appendChild(document.createTextNode(link.source_label));
                sourceTd.appendChild(sourceLink);
                linkTr.appendChild(sourceTd);
                // Add link cell.
                const linkTd = document.createElement('td');
                linkTd.appendChild(document.createTextNode(link.link_label));
                linkTr.appendChild(linkTd);
                // Add target node cell.
                const targetTd = document.createElement('td');
                const targetLink = document.createElement('a');
                targetLink.title = link.target_label;
                targetLink.href = link.target_url;
                targetLink.target = '_blank';
                targetLink.appendChild(document.createTextNode(link.target_label));
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
        return contentDiv;
    },

};
