import * as d3 from 'd3';

const config = window.orgChartConfig || {};

const LAYOUT = {
    NODE_WIDTH: 180,
    NODE_HEIGHT: 38,
    LEADER_BOX_HEIGHT: 70,
    PLATOON_EXTRA_HEIGHT: 115,
    SQUAD_EXTRA_HEIGHT: 18,
    LOGO_SIZE: 60,
    LOGO_GAP: 22,
    MAX_COLS: 4,
    PLATOON_COLS: 2,
    ROW_SPACING: 90,
    PLATOON_ROW_SPACING: 110,
    COL_SPACING: 24,
    MOBILE_BREAKPOINT: 768,
    MOBILE_NODE_WIDTH: 150,
    MOBILE_LOGO_SIZE: 40
};

const FONT = {
    DIVISION_NAME: '18px',
    PLATOON_NAME: '14px',
    SQUAD_NAME: '12px',
    LEADER_NAME: '13px',
    LEADER_NAME_SMALL: '12px',
    MEMBER_NAME: '13px',
    HANDLE: '11px',
    HANDLE_SMALL: '10px',
    LABEL: '9px',
    LABEL_SMALL: '8px',
    COLLAPSE_INDICATOR: '14px',
    COLLAPSE_INDICATOR_SMALL: '12px'
};

let svg, g, zoom, root;
let collapsed = new Set();
let showHandles = false;
let isMobile = false;
let searchTerm = '';

function getThemeColors() {
    const styles = getComputedStyle(document.documentElement);
    return {
        bg: styles.getPropertyValue('--color-bg-panel').trim() || '#1e2a31',
        bgDark: styles.getPropertyValue('--color-bg-dark').trim() || '#15202b',
        text: styles.getPropertyValue('--color-text').trim() || '#dee5ed',
        textMuted: styles.getPropertyValue('--color-text-muted').trim() || '#949ba2',
        primary: styles.getPropertyValue('--color-primary').trim() || '#0F83C9',
        accent: styles.getPropertyValue('--color-accent').trim() || '#f7af3e',
        border: styles.getPropertyValue('--color-border').trim() || 'rgba(255,255,255,0.08)'
    };
}

function getRankBackground(hexColor, opacity = 0.15) {
    const r = parseInt(hexColor.slice(1, 3), 16);
    const g = parseInt(hexColor.slice(3, 5), 16);
    const b = parseInt(hexColor.slice(5, 7), 16);
    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
}

function getNodeWidth() {
    return isMobile ? LAYOUT.MOBILE_NODE_WIDTH : LAYOUT.NODE_WIDTH;
}

function getLogoSize() {
    return isMobile ? LAYOUT.MOBILE_LOGO_SIZE : LAYOUT.LOGO_SIZE;
}

function getPlatoonCols() {
    return isMobile ? 1 : LAYOUT.PLATOON_COLS;
}

function getNodeHeight(d) {
    if (d.data.type === 'platoon') {
        const descExtra = d.data.description ? 18 : 0;
        return LAYOUT.LEADER_BOX_HEIGHT + LAYOUT.PLATOON_EXTRA_HEIGHT + descExtra;
    }
    if (d.data.type === 'squad') {
        return LAYOUT.LEADER_BOX_HEIGHT + LAYOUT.SQUAD_EXTRA_HEIGHT;
    }
    return LAYOUT.NODE_HEIGHT;
}

function getRowSpacing(parentType) {
    if (parentType === 'platoon') return LAYOUT.PLATOON_ROW_SPACING;
    return LAYOUT.ROW_SPACING;
}

function countDescendantMembers(node) {
    let count = 0;
    if (node.data.children) {
        node.data.children.forEach(child => {
            if (child.type === 'member') {
                count++;
            } else if (child.children) {
                count += countChildMembers(child);
            }
        });
    }
    return count;
}

function countChildMembers(data) {
    let count = 0;
    if (data.children) {
        data.children.forEach(child => {
            if (child.type === 'member') {
                count++;
            } else if (child.children) {
                count += countChildMembers(child);
            }
        });
    }
    return count;
}

function checkMobile() {
    isMobile = window.innerWidth < LAYOUT.MOBILE_BREAKPOINT;
}

function initChart() {
    checkMobile();

    const container = d3.select('.org-chart-container');
    const width = container.node().getBoundingClientRect().width;
    const height = Math.max(600, window.innerHeight - 250);

    svg = d3.select('#org-chart')
        .attr('width', width)
        .attr('height', height);

    g = svg.append('g');

    zoom = d3.zoom()
        .scaleExtent([0.2, 2])
        .on('zoom', (event) => g.attr('transform', event.transform));

    svg.call(zoom);

    loadData();
    setupControls();
    setupThemeObserver();
    setupResizeHandler();
    setupSearch();
}

function loadData() {
    d3.json(config.dataUrl)
        .then(data => {
            d3.select('.org-chart-loading').style('display', 'none');
            root = d3.hierarchy(data);
            root.x0 = 0;
            root.y0 = 0;

            root.descendants().forEach(d => {
                if ((d.data.type === 'platoon' || d.data.type === 'squad') && d.data.children?.length > 0) {
                    collapsed.add(d.data.id);
                }
            });

            update(root);
            centerTree();
        })
        .catch(error => {
            d3.select('.org-chart-loading')
                .html('<i class="fa fa-exclamation-triangle text-danger"></i> Failed to load data');
            console.error('Failed to load org chart data:', error);
        });
}

function getMaxCols(node) {
    if (node.data.type === 'division') return 1;
    return LAYOUT.MAX_COLS;
}

function calculateSubtreeDimensions(node) {
    const nodeWidth = getNodeWidth();

    if (!node.children || node.children.length === 0) {
        node._width = nodeWidth + LAYOUT.COL_SPACING;
        node._height = getNodeHeight(node);
        return;
    }

    node.children.forEach(child => calculateSubtreeDimensions(child));

    if (node.data.type === 'division') {
        const leadershipGroup = node.children.find(c => c.data.type === 'leadership-group');
        const platoons = node.children.filter(c => c.data.type === 'platoon');

        let leadershipHeight = leadershipGroup ? leadershipGroup._height : 0;

        let platoonsWidth = 0;
        let platoonsHeight = 0;
        const platoonCols = getPlatoonCols();
        const numRows = Math.ceil(platoons.length / platoonCols);

        for (let row = 0; row < numRows; row++) {
            const rowPlatoons = platoons.slice(row * platoonCols, (row + 1) * platoonCols);
            const rowWidth = rowPlatoons.reduce((sum, p) => sum + p._width, 0);
            const rowHeight = Math.max(...rowPlatoons.map(p => p._height));
            platoonsWidth = Math.max(platoonsWidth, rowWidth);
            platoonsHeight += rowHeight + LAYOUT.ROW_SPACING;
        }

        node._width = Math.max(leadershipGroup?._width || 0, platoonsWidth) + LAYOUT.COL_SPACING;
        node._height = getNodeHeight(node) + leadershipHeight + platoonsHeight + LAYOUT.ROW_SPACING;
        return;
    }

    const maxCols = getMaxCols(node);
    const numRows = Math.ceil(node.children.length / maxCols);
    const spacing = getRowSpacing(node.data.type);
    let totalWidth = 0;
    let totalHeight = spacing;

    for (let row = 0; row < numRows; row++) {
        const startIdx = row * maxCols;
        const endIdx = Math.min(startIdx + maxCols, node.children.length);
        const rowChildren = node.children.slice(startIdx, endIdx);

        const rowWidth = rowChildren.reduce((sum, child) => sum + child._width, 0);
        const rowHeight = Math.max(...rowChildren.map(child => child._height));

        totalWidth = Math.max(totalWidth, rowWidth);
        totalHeight += rowHeight + (row < numRows - 1 ? spacing / 2 : 0);
    }

    node._width = Math.max(nodeWidth + LAYOUT.COL_SPACING, totalWidth);
    node._height = getNodeHeight(node) + totalHeight;
}

function layoutTree(node, x, y) {
    node.x = x;
    node.y = y;

    if (!node.children || node.children.length === 0) return;

    if (node.data.type === 'division') {
        const leadershipGroup = node.children.find(c => c.data.type === 'leadership-group');
        const platoons = node.children.filter(c => c.data.type === 'platoon');

        let currentY = y + LAYOUT.ROW_SPACING;

        if (leadershipGroup) {
            layoutTree(leadershipGroup, x, currentY);
            currentY += leadershipGroup._height + LAYOUT.ROW_SPACING;
        }

        const platoonCols = getPlatoonCols();
        const numRows = Math.ceil(platoons.length / platoonCols);

        for (let row = 0; row < numRows; row++) {
            const rowPlatoons = platoons.slice(row * platoonCols, (row + 1) * platoonCols);
            const rowWidth = rowPlatoons.reduce((sum, p) => sum + p._width, 0);
            const rowHeight = Math.max(...rowPlatoons.map(p => p._height));

            let currentX = x - rowWidth / 2;
            rowPlatoons.forEach(p => {
                layoutTree(p, currentX + p._width / 2, currentY);
                currentX += p._width;
            });

            currentY += rowHeight + LAYOUT.ROW_SPACING;
        }

        return;
    }

    const maxCols = getMaxCols(node);
    const numRows = Math.ceil(node.children.length / maxCols);
    const spacing = getRowSpacing(node.data.type);
    let currentY = y + spacing;

    for (let row = 0; row < numRows; row++) {
        const startIdx = row * maxCols;
        const endIdx = Math.min(startIdx + maxCols, node.children.length);
        const rowChildren = node.children.slice(startIdx, endIdx);
        const rowWidth = rowChildren.reduce((sum, child) => sum + child._width, 0);

        let currentX = x - rowWidth / 2;
        let maxRowHeight = 0;

        rowChildren.forEach(child => {
            const childX = currentX + child._width / 2;
            layoutTree(child, childX, currentY);
            currentX += child._width;
            maxRowHeight = Math.max(maxRowHeight, child._height);
        });

        currentY += maxRowHeight + spacing / 2;
    }
}

function update(source) {
    const colors = getThemeColors();
    const duration = 300;

    const filteredRoot = filterCollapsed(root);
    calculateSubtreeDimensions(filteredRoot);
    layoutTree(filteredRoot, 0, 0);

    const nodes = filteredRoot.descendants();

    const node = g.selectAll('.node')
        .data(nodes, d => d.data.id);

    const nodeEnter = node.enter()
        .append('g')
        .attr('class', d => `node node-${d.data.type}`)
        .attr('transform', `translate(${source.x0},${source.y0})`)
        .style('opacity', 0);

    nodeEnter.each(function(d) {
        renderNode(d3.select(this), d, colors);
    });

    node.each(function(d) {
        const sel = d3.select(this);
        sel.selectAll('*').remove();
        renderNode(sel, d, colors);
    });

    nodeEnter.merge(node)
        .transition()
        .duration(duration)
        .attr('transform', d => `translate(${d.x},${d.y})`)
        .style('opacity', 1);

    node.exit()
        .transition()
        .duration(duration)
        .attr('transform', `translate(${source.x},${source.y})`)
        .style('opacity', 0)
        .remove();

    nodes.forEach(d => {
        d.x0 = d.x;
        d.y0 = d.y;
    });
}

function renderNode(nodeGroup, d, colors) {
    const type = d.data.type;
    const isHighlighted = isNodeHighlighted(d.data);

    nodeGroup.classed('highlighted', isHighlighted);

    if (type === 'division') {
        renderDivisionNode(nodeGroup, d.data, colors);
    } else if (type === 'leadership-group') {
        renderLeadershipGroup(nodeGroup, d, colors);
    } else if (type === 'co' || type === 'xo') {
        renderLeaderNode(nodeGroup, d.data, colors, type);
    } else if (type === 'platoon') {
        renderPlatoonNode(nodeGroup, d, colors);
    } else if (type === 'squad') {
        renderSquadNode(nodeGroup, d, colors);
    } else if (type === 'member') {
        renderMemberNode(nodeGroup, d.data, colors);
    }
}

function isNodeHighlighted(data) {
    if (!searchTerm) return false;
    const term = searchTerm.toLowerCase();

    if (data.name?.toLowerCase().includes(term)) return true;
    if (data.handle?.toLowerCase().includes(term)) return true;
    if (data.rankName?.toLowerCase().includes(term)) return true;
    if (data.leader?.name?.toLowerCase().includes(term)) return true;
    if (data.leader?.handle?.toLowerCase().includes(term)) return true;

    return false;
}

function renderDivisionNode(nodeGroup, data, colors) {
    const logoSize = getLogoSize();

    if (data.logo) {
        nodeGroup.append('image')
            .attr('x', -logoSize / 2)
            .attr('y', -logoSize - 20)
            .attr('width', logoSize)
            .attr('height', logoSize)
            .attr('href', data.logo)
            .style('pointer-events', 'none');
    }

    nodeGroup.append('text')
        .attr('y', data.logo ? 6 : 0)
        .attr('text-anchor', 'middle')
        .attr('fill', colors.accent)
        .attr('font-size', FONT.DIVISION_NAME)
        .attr('font-weight', '700')
        .attr('letter-spacing', '1px')
        .text(data.name.toUpperCase());
}

function renderLeadershipGroup(nodeGroup, d, colors) {
    nodeGroup.append('text')
        .attr('y', 6)
        .attr('text-anchor', 'middle')
        .attr('fill', colors.text)
        .attr('font-size', FONT.PLATOON_NAME)
        .attr('font-weight', '600')
        .attr('letter-spacing', '2px')
        .text('DIVISION LEADERSHIP');
}

function renderLeaderNode(nodeGroup, data, colors, type) {
    const width = getNodeWidth();
    const height = LAYOUT.NODE_HEIGHT;
    const rankColor = data.rankColor;
    const bgColor = getRankBackground(rankColor, 0.15);

    nodeGroup.append('rect')
        .attr('x', -width / 2)
        .attr('y', -height / 2)
        .attr('width', width)
        .attr('height', height)
        .attr('rx', 6)
        .attr('fill', bgColor)
        .attr('stroke', rankColor)
        .attr('stroke-width', type === 'co' ? 2 : 1)
        .attr('stroke-opacity', type === 'co' ? 0.8 : 0.5);

    renderLeaderContent(nodeGroup, data, colors, { fontSize: FONT.LEADER_NAME });

    const positionLabel = type === 'co' ? 'Commanding Officer' : 'Executive Officer';
    nodeGroup.append('text')
        .attr('y', -height / 2 - 6)
        .attr('text-anchor', 'middle')
        .attr('fill', colors.textMuted)
        .attr('font-size', FONT.LABEL)
        .attr('letter-spacing', '0.5px')
        .style('pointer-events', 'none')
        .text(positionLabel.toUpperCase());
}

function shouldShowHandle(handle) {
    if (showHandles) return true;
    if (!searchTerm || !handle) return false;
    return handle.toLowerCase().includes(searchTerm.toLowerCase());
}

function renderLeaderContent(nodeGroup, data, colors, options = {}) {
    const fontSize = options.fontSize || FONT.LEADER_NAME_SMALL;
    const handleFontSize = options.handleFontSize || FONT.HANDLE_SMALL;
    const nameYOffset = options.nameYOffset || 0;
    const handleYOffset = options.handleYOffset || 8;
    const rankColor = data.rankColor;
    const displayHandle = shouldShowHandle(data.handle);

    nodeGroup.append('text')
        .attr('y', displayHandle && data.handle ? nameYOffset - 8 : nameYOffset)
        .attr('text-anchor', 'middle')
        .attr('fill', rankColor)
        .attr('font-size', fontSize)
        .attr('font-weight', '600')
        .style('pointer-events', 'none')
        .text(data.rankName);

    if (displayHandle && data.handle) {
        nodeGroup.append('text')
            .attr('y', handleYOffset)
            .attr('text-anchor', 'middle')
            .attr('fill', colors.textMuted)
            .attr('font-size', handleFontSize)
            .style('pointer-events', 'none')
            .text(data.handle);
    }
}

function renderCollapsibleIndicator(nodeGroup, isCollapsed, x, y, colors, fontSize) {
    nodeGroup.append('text')
        .attr('x', x)
        .attr('y', y)
        .attr('text-anchor', 'middle')
        .attr('fill', colors.textMuted)
        .attr('font-size', fontSize)
        .attr('font-weight', 'bold')
        .style('pointer-events', 'none')
        .text(isCollapsed ? '+' : '−');
}

function renderMemberCount(nodeGroup, count, x, y, colors) {
    if (count > 0) {
        nodeGroup.append('text')
            .attr('x', x)
            .attr('y', y)
            .attr('text-anchor', 'middle')
            .attr('fill', colors.textMuted)
            .attr('font-size', '10px')
            .style('pointer-events', 'none')
            .text(`${count} member${count !== 1 ? 's' : ''}`);
    }
}

function renderPlatoonNode(nodeGroup, d, colors) {
    const data = d.data;
    const width = getNodeWidth();
    const hasLeader = !!data.leader;
    const height = LAYOUT.LEADER_BOX_HEIGHT;
    const isCollapsible = data.children?.length > 0;
    const isCollapsed = collapsed.has(data.id);
    const logoSize = getLogoSize();
    const memberCount = countChildMembers(data);

    if (data.logo) {
        nodeGroup.append('image')
            .attr('x', -logoSize / 2)
            .attr('y', -height / 2 - logoSize - LAYOUT.LOGO_GAP)
            .attr('width', logoSize)
            .attr('height', logoSize)
            .attr('href', data.logo)
            .style('pointer-events', 'none');
    }

    const hasDescription = !!data.description;
    const descriptionOffset = hasDescription ? 16 : 0;
    const logoOffset = data.logo ? 16 : 14;

    nodeGroup.append('text')
        .attr('y', -height / 2 - logoOffset - descriptionOffset)
        .attr('text-anchor', 'middle')
        .attr('fill', colors.accent)
        .attr('font-size', FONT.PLATOON_NAME)
        .attr('font-weight', '700')
        .attr('letter-spacing', '1px')
        .text(data.name.toUpperCase());

    if (hasDescription) {
        nodeGroup.append('text')
            .attr('y', -height / 2 - logoOffset + 2)
            .attr('text-anchor', 'middle')
            .attr('fill', colors.textMuted)
            .attr('font-size', FONT.SQUAD_NAME)
            .text(truncate(data.description, 30));
    }

    const leaderColor = hasLeader ? data.leader.rankColor : null;
    const bgColor = leaderColor ? getRankBackground(leaderColor, 0.15) : colors.bg;
    const strokeColor = leaderColor || colors.accent;

    nodeGroup.append('rect')
        .attr('x', -width / 2)
        .attr('y', -height / 2)
        .attr('width', width)
        .attr('height', height)
        .attr('rx', 6)
        .attr('fill', bgColor)
        .attr('stroke', strokeColor)
        .attr('stroke-width', 1)
        .attr('stroke-opacity', 0.5)
        .style('cursor', isCollapsible ? 'pointer' : 'default')
        .on('click', () => {
            if (isCollapsible) toggleNode(d);
        });

    if (hasLeader) {
        renderLeaderContent(nodeGroup, data.leader, colors, {
            fontSize: FONT.LEADER_NAME_SMALL,
            handleFontSize: FONT.HANDLE_SMALL
        });
    } else {
        renderTBAText(nodeGroup, colors, FONT.LEADER_NAME_SMALL);
    }

    nodeGroup.append('text')
        .attr('y', height / 2 - 8)
        .attr('text-anchor', 'middle')
        .attr('fill', colors.textMuted)
        .attr('font-size', FONT.LABEL)
        .attr('letter-spacing', '0.5px')
        .style('pointer-events', 'none')
        .text('PLATOON LEADER');

    if (isCollapsible) {
        renderCollapsibleIndicator(
            nodeGroup,
            isCollapsed,
            width / 2 - 14,
            -height / 2 + 16,
            colors,
            FONT.COLLAPSE_INDICATOR
        );

        if (isCollapsed) {
            renderMemberCount(nodeGroup, memberCount, 0, height / 2 + 18, colors);
        }
    }
}

function renderSquadNode(nodeGroup, d, colors) {
    const data = d.data;
    const width = getNodeWidth();
    const hasLeader = !!data.leader;
    const height = LAYOUT.LEADER_BOX_HEIGHT;
    const isCollapsible = data.children?.length > 0;
    const isCollapsed = collapsed.has(data.id);
    const memberCount = countChildMembers(data);

    const leaderColor = hasLeader ? data.leader.rankColor : null;
    const bgColor = leaderColor ? getRankBackground(leaderColor, 0.12) : colors.bgDark;
    const strokeColor = leaderColor || colors.border;

    nodeGroup.append('text')
        .attr('y', -height / 2 - 12)
        .attr('text-anchor', 'middle')
        .attr('fill', colors.text)
        .attr('font-size', FONT.SQUAD_NAME)
        .attr('font-weight', '600')
        .attr('letter-spacing', '0.5px')
        .text(data.name);

    nodeGroup.append('rect')
        .attr('x', -width / 2)
        .attr('y', -height / 2)
        .attr('width', width)
        .attr('height', height)
        .attr('rx', 4)
        .attr('fill', bgColor)
        .attr('stroke', strokeColor)
        .attr('stroke-opacity', 0.4)
        .style('cursor', isCollapsible ? 'pointer' : 'default')
        .on('click', () => {
            if (isCollapsible) toggleNode(d);
        });

    if (hasLeader) {
        renderLeaderContent(nodeGroup, data.leader, colors, {
            fontSize: '11px',
            handleFontSize: FONT.HANDLE_SMALL,
            fontWeight: '500'
        });
    } else {
        renderTBAText(nodeGroup, colors, '11px');
    }

    nodeGroup.append('text')
        .attr('y', height / 2 - 8)
        .attr('text-anchor', 'middle')
        .attr('fill', colors.textMuted)
        .attr('font-size', FONT.LABEL_SMALL)
        .attr('letter-spacing', '0.5px')
        .style('pointer-events', 'none')
        .text('SQUAD LEADER');

    if (isCollapsible) {
        renderCollapsibleIndicator(
            nodeGroup,
            isCollapsed,
            width / 2 - 12,
            -height / 2 + 14,
            colors,
            FONT.COLLAPSE_INDICATOR_SMALL
        );

        if (isCollapsed) {
            renderMemberCount(nodeGroup, memberCount, 0, height / 2 + 16, colors);
        }
    }
}

function renderTBAText(nodeGroup, colors, fontSize) {
    nodeGroup.append('text')
        .attr('y', 0)
        .attr('text-anchor', 'middle')
        .attr('fill', colors.textMuted)
        .attr('font-size', fontSize)
        .attr('font-style', 'italic')
        .style('pointer-events', 'none')
        .text('TBA');
}

function renderMemberNode(nodeGroup, data, colors) {
    const width = getNodeWidth();
    const height = LAYOUT.NODE_HEIGHT;
    const rankColor = data.rankColor;
    const bgColor = getRankBackground(rankColor, 0.25);
    const displayHandle = shouldShowHandle(data.handle);

    nodeGroup.append('rect')
        .attr('x', -width / 2)
        .attr('y', -height / 2)
        .attr('width', width)
        .attr('height', height)
        .attr('rx', 4)
        .attr('fill', bgColor)
        .attr('stroke', rankColor)
        .attr('stroke-opacity', 0.4);

    nodeGroup.append('text')
        .attr('y', displayHandle && data.handle ? -6 : 4)
        .attr('text-anchor', 'middle')
        .attr('fill', rankColor)
        .attr('font-size', FONT.MEMBER_NAME)
        .attr('font-weight', '600')
        .style('pointer-events', 'none')
        .text(data.rankName);

    if (displayHandle && data.handle) {
        nodeGroup.append('text')
            .attr('y', 12)
            .attr('text-anchor', 'middle')
            .attr('fill', colors.text)
            .attr('font-size', FONT.HANDLE)
            .style('pointer-events', 'none')
            .text(truncate(data.handle, 18));
    }
}

function filterCollapsed(node) {
    function buildFilteredData(source) {
        const nodeData = { ...source.data };

        if (source.children && !collapsed.has(source.data.id)) {
            nodeData._children = source.children.map(child => buildFilteredData(child));
        }

        nodeData._x0 = source.x0 || 0;
        nodeData._y0 = source.y0 || 0;

        return nodeData;
    }

    const filteredData = buildFilteredData(node);
    const copy = d3.hierarchy(filteredData, d => d._children);

    copy.each(d => {
        d.x0 = d.data._x0;
        d.y0 = d.data._y0;
    });

    return copy;
}

function toggleNode(d) {
    if (collapsed.has(d.data.id)) {
        collapsed.delete(d.data.id);
    } else {
        collapsed.add(d.data.id);
    }
    update(d);
}

function collapseAll() {
    root.descendants().forEach(d => {
        if ((d.data.type === 'platoon' || d.data.type === 'squad') && d.data.children?.length > 0) {
            collapsed.add(d.data.id);
        }
    });
}

function centerTree() {
    if (!root) return;

    const bounds = g.node().getBBox();
    const containerWidth = svg.node().getBoundingClientRect().width;
    const containerHeight = svg.node().getBoundingClientRect().height;

    const scale = Math.min(
        containerWidth / (bounds.width + 100),
        containerHeight / (bounds.height + 100),
        1
    );

    const x = containerWidth / 2 - (bounds.x + bounds.width / 2) * scale;
    const y = 80;

    svg.transition()
        .duration(500)
        .call(zoom.transform, d3.zoomIdentity.translate(x, y).scale(scale));
}

function setupControls() {
    d3.select('#zoom-in').on('click', () => svg.transition().call(zoom.scaleBy, 1.3));
    d3.select('#zoom-out').on('click', () => svg.transition().call(zoom.scaleBy, 0.7));
    d3.select('#zoom-reset').on('click', () => centerTree());

    d3.select('#expand-all').on('click', () => {
        collapsed.clear();
        update(root);
    });

    d3.select('#collapse-all').on('click', () => {
        collapseAll();
        update(root);
    });

    d3.select('#toggle-handles').on('click', function() {
        showHandles = !showHandles;
        d3.select(this).classed('active', showHandles);
        update(root);
    });

    d3.select('#export-png').on('click', exportToPng);
}

function setupSearch() {
    const searchInput = d3.select('#org-chart-search');
    const resultsContainer = d3.select('#search-results');
    if (searchInput.empty()) return;

    let debounceTimer;
    searchInput.on('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            searchTerm = this.value.trim();
            if (root) {
                if (searchTerm) {
                    expandToHighlighted();
                    const results = findMatchingNodes();
                    showSearchResults(results);
                } else {
                    hideSearchResults();
                }
                update(root);
            }
        }, 300);
    });

    searchInput.on('focus', function() {
        if (searchTerm && root) {
            const results = findMatchingNodes();
            showSearchResults(results);
        }
    });

    d3.select('#clear-search').on('click', function() {
        searchInput.property('value', '');
        searchTerm = '';
        hideSearchResults();
        if (root) {
            collapseAll();
            update(root);
            centerTree();
        }
    });

    document.addEventListener('click', function(e) {
        const searchWrapper = document.querySelector('.org-chart-search-wrapper');
        if (searchWrapper && !searchWrapper.contains(e.target)) {
            hideSearchResults();
        }
    });
}

function findMatchingNodes() {
    if (!searchTerm || !root) return [];

    const results = [];
    const term = searchTerm.toLowerCase();

    root.descendants().forEach(d => {
        const data = d.data;

        if (data.type === 'member' || data.type === 'co' || data.type === 'xo') {
            if (data.name?.toLowerCase().includes(term) ||
                data.handle?.toLowerCase().includes(term) ||
                data.rankName?.toLowerCase().includes(term)) {
                results.push({ node: d, data: data });
            }
        }

        if ((data.type === 'platoon' || data.type === 'squad') && data.leader) {
            if (data.leader.name?.toLowerCase().includes(term) ||
                data.leader.handle?.toLowerCase().includes(term)) {
                results.push({ node: d, data: data.leader });
            }
        }
    });

    return results;
}

function showSearchResults(results) {
    const container = d3.select('#search-results');
    container.html('');

    if (results.length === 0) {
        container.append('div')
            .attr('class', 'search-no-results')
            .text('No members found');
        container.classed('show', true);
        return;
    }

    results.forEach(result => {
        const item = container.append('div')
            .attr('class', 'search-result-item')
            .on('click', () => {
                centerOnNode(result.node);
                hideSearchResults();
            });

        item.append('span')
            .attr('class', 'result-rank')
            .style('color', result.data.rankColor)
            .text(result.data.rank || '');

        item.append('span')
            .attr('class', 'result-name')
            .text(result.data.name);

        if (result.data.handle) {
            item.append('span')
                .attr('class', 'result-handle')
                .text(result.data.handle);
        }
    });

    container.classed('show', true);
}

function hideSearchResults() {
    d3.select('#search-results').classed('show', false);
}

function centerOnNode(node) {
    if (!node) return;

    let parent = node.parent;
    while (parent) {
        collapsed.delete(parent.data.id);
        parent = parent.parent;
    }
    update(root);

    setTimeout(() => {
        const targetNodeGroup = g.selectAll('.node').filter(d => d.data.id === node.data.id);

        if (!targetNodeGroup.empty()) {
            const targetData = targetNodeGroup.datum();
            if (targetData && typeof targetData.x === 'number' && typeof targetData.y === 'number') {
                const containerWidth = svg.node().getBoundingClientRect().width;
                const containerHeight = svg.node().getBoundingClientRect().height;
                const scale = 1;
                const x = containerWidth / 2 - targetData.x * scale;
                const y = containerHeight / 2 - targetData.y * scale;

                svg.transition()
                    .duration(500)
                    .call(zoom.transform, d3.zoomIdentity.translate(x, y).scale(scale));
            }
        }
    }, 350);
}

function expandToHighlighted() {
    if (!searchTerm || !root) return;

    root.descendants().forEach(d => {
        if (isNodeHighlighted(d.data)) {
            let parent = d.parent;
            while (parent) {
                collapsed.delete(parent.data.id);
                parent = parent.parent;
            }
        }

        if (d.data.children) {
            d.data.children.forEach(child => {
                if (child.type === 'member' && isNodeHighlighted(child)) {
                    collapsed.delete(d.data.id);
                }
            });
        }
    });
}

function centerOnHighlighted() {
    if (!searchTerm || !root) return;

    const highlightedNode = g.select('.node.highlighted');
    if (highlightedNode.empty()) return;

    const nodeData = highlightedNode.datum();
    if (!nodeData) return;

    const containerWidth = svg.node().getBoundingClientRect().width;
    const containerHeight = svg.node().getBoundingClientRect().height;

    const scale = 1;
    const x = containerWidth / 2 - nodeData.x * scale;
    const y = containerHeight / 2 - nodeData.y * scale;

    svg.transition()
        .duration(500)
        .call(zoom.transform, d3.zoomIdentity.translate(x, y).scale(scale));
}

function setupThemeObserver() {
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'data-theme' && root) {
                    update(root);
                }
            });
        });
        observer.observe(document.documentElement, { attributes: true });
    }
}

function setupResizeHandler() {
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const wasMobile = isMobile;
            checkMobile();

            const container = d3.select('.org-chart-container');
            const width = container.node().getBoundingClientRect().width;
            const height = Math.max(600, window.innerHeight - 250);

            svg.attr('width', width).attr('height', height);

            if (root) {
                update(root);
                centerTree();
            }
        }, 250);
    });
}

function exportToPng() {
    if (!svg) return;

    const svgElement = svg.node();
    const bounds = g.node().getBBox();
    const padding = 40;

    const clonedSvg = svgElement.cloneNode(true);
    const clonedG = clonedSvg.querySelector('g');

    clonedSvg.setAttribute('width', bounds.width + padding * 2);
    clonedSvg.setAttribute('height', bounds.height + padding * 2);
    clonedG.setAttribute('transform', `translate(${-bounds.x + padding}, ${-bounds.y + padding})`);

    clonedSvg.style.backgroundColor = getComputedStyle(document.documentElement)
        .getPropertyValue('--color-bg-dark').trim() || '#15202b';

    const serializer = new XMLSerializer();
    const svgString = serializer.serializeToString(clonedSvg);
    const svgBlob = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(svgBlob);

    const img = new Image();
    img.onload = function() {
        const canvas = document.createElement('canvas');
        const scale = 2;
        canvas.width = (bounds.width + padding * 2) * scale;
        canvas.height = (bounds.height + padding * 2) * scale;

        const ctx = canvas.getContext('2d');
        ctx.scale(scale, scale);
        ctx.fillStyle = getComputedStyle(document.documentElement)
            .getPropertyValue('--color-bg-dark').trim() || '#15202b';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0);

        const pngUrl = canvas.toDataURL('image/png');
        const downloadLink = document.createElement('a');
        downloadLink.href = pngUrl;
        downloadLink.download = 'org-chart.png';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);

        URL.revokeObjectURL(url);
    };
    img.src = url;
}

function truncate(str, len) {
    if (!str) return '';
    return str.length > len ? str.substring(0, len - 1) + '…' : str;
}

document.addEventListener('DOMContentLoaded', initChart);
