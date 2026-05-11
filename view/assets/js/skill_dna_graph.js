
// Skill DNA Graph (Visual Ontology) using D3.js
document.addEventListener('DOMContentLoaded', async () => {
    const graphContainer = document.getElementById('skill-dna-graph');
    if (!graphContainer) return;

    // Check if D3 is loaded
    if (typeof d3 === 'undefined') {
        graphContainer.innerHTML = '<p class="text-danger">Erreur: D3.js n\'est pas chargé.</p>';
        return;
    }

    try {
        const res = await fetch('/aptus_first_official_version/view/backoffice/api_veille_ai.php?action=get_skill_dna');
        const result = await res.json();
        
        if (result.success && result.data.nodes.length > 0) {
            const data = result.data;
            const width = graphContainer.clientWidth || 800;
            const height = 400;

            // Clear loading
            graphContainer.innerHTML = '';

            const svg = d3.select("#skill-dna-graph").append("svg")
                .attr("width", width)
                .attr("height", height).attr("viewBox", `0 0 ${width} ${height}`).attr("preserveAspectRatio", "xMidYMid meet");

            const simulation = d3.forceSimulation(data.nodes)
                .force("link", d3.forceLink(data.links).id(d => d.id).distance(100))
                .force("charge", d3.forceManyBody().strength(-300))
                .force("center", d3.forceCenter(width / 2, height / 2))
                .force("x", d3.forceX(width / 2).strength(0.1))
                .force("y", d3.forceY(height / 2).strength(0.1));

            const link = svg.append("g")
                .attr("class", "links")
                .selectAll("line")
                .data(data.links)
                .enter().append("line")
                .attr("stroke", "#999")
                .attr("stroke-opacity", 0.6)
                .attr("stroke-width", d => Math.sqrt(d.value));

            const node = svg.append("g")
                .attr("class", "nodes")
                .selectAll("g")
                .data(data.nodes)
                .enter().append("g")
                .call(d3.drag()
                    .on("start", dragstarted)
                    .on("drag", dragged)
                    .on("end", dragended));

            node.append("circle")
                .attr("r", d => 5 + (d.value * 2))
                .attr("fill", "#4f46e5");

            node.append("text")
                .text(d => d.id)
                .attr('x', 10)
                .attr('y', 3)
                .style("font-size", "12px")
                .style("font-family", "Arial, sans-serif");

            node.append("title")
                .text(d => d.id + " (" + d.value + " mentions)");

            simulation.on("tick", () => {
                link
                    .attr("x1", d => d.source.x)
                    .attr("y1", d => d.source.y)
                    .attr("x2", d => d.target.x)
                    .attr("y2", d => d.target.y);

                node
                    .attr("transform", d => `translate(${d.x},${d.y})`);
            });

            function dragstarted(event, d) {
                if (!event.active) simulation.alphaTarget(0.3).restart();
                d.fx = d.x;
                d.fy = d.y;
            }

            function dragged(event, d) {
                d.fx = event.x;
                d.fy = event.y;
            }

            function dragended(event, d) {
                if (!event.active) simulation.alphaTarget(0);
                d.fx = null;
                d.fy = null;
            }
        } else {
            graphContainer.innerHTML = '<p>Pas assez de données pour gà©nà©rer le graphe.</p>';
        }
    } catch (e) {
        console.error("Skill DNA Error:", e);
        graphContainer.innerHTML = '<p>Erreur lors du chargement du graphe.</p>';
    }
});
