/* eslint-disable @typescript-eslint/no-explicit-any */
import * as d3 from 'd3';

/**
 * D3 division org chart. Ported from the legacy `resources/assets/js/org-chart.js`
 * — layout math and rendering are unchanged; the DOM-id wiring and data fetch are
 * replaced by an imperative API the React wrapper drives.
 */

const LAYOUT = {
    NODE_WIDTH: 180,
    NODE_HEIGHT: 38,
    LEADER_BOX_HEIGHT: 70,
    LOGO_SIZE: 60,
    MAX_COLS: 4,
    PLATOON_COLS: 2,
    ROW_SPACING: 90,
    PLATOON_ROW_SPACING: 110,
    COL_SPACING: 48,
    MOBILE_BREAKPOINT: 768,
    MOBILE_NODE_WIDTH: 150,
    MOBILE_LOGO_SIZE: 40,
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
    COLLAPSE_INDICATOR_SMALL: '12px',
};

export interface OrgNode {
    id: string;
    name: string;
    type: string;
    logo?: string | null;
    description?: string | null;
    clanId?: number;
    rankName?: string;
    rankColor?: string;
    handle?: string | null;
    leader?: { clanId: number; name: string; rankName: string; rankColor: string; handle?: string | null };
    children?: OrgNode[];
}

export interface SearchMatch {
    id: string;
    name: string;
    rankName?: string;
    rankColor?: string;
    handle?: string | null;
}

export interface OrgChartHandle {
    zoomIn(): void;
    zoomOut(): void;
    resetView(): void;
    expandAll(): void;
    collapseAll(): void;
    toggleHandles(): boolean;
    exportPng(): void;
    setSearch(term: string): SearchMatch[];
    focusNode(id: string): void;
    resize(): void;
    destroy(): void;
}

function themeColors() {
    const s = getComputedStyle(document.documentElement);
    const v = (name: string, fallback: string) => s.getPropertyValue(name).trim() || fallback;
    return {
        bg: v('--card', '#0f1113'),
        bgDark: v('--background', '#08090b'),
        text: v('--foreground', '#e7e9ec'),
        textMuted: v('--muted-foreground', '#868b94'),
        accent: v('--primary', '#e11d2e'),
        glow: v('--primary-glow', 'rgba(225,29,46,0.32)'),
        border: v('--border-strong', 'rgba(255,255,255,0.12)'),
        light: document.documentElement.dataset.theme === 'light',
    };
}

function shiftToward(hex: string, target: number, amount: number) {
    const mix = (c: number) => Math.round(c + (target - c) * amount);
    const r = mix(parseInt(hex.slice(1, 3), 16));
    const g = mix(parseInt(hex.slice(3, 5), 16));
    const b = mix(parseInt(hex.slice(5, 7), 16));
    return { r, g, b };
}

/** A rank-tinted fill that stays legible on either theme surface. */
function rankBg(hex: string, opacity = 0.15) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
}

function rankFill(hex: string, colors: any, opacity = 0.15) {
    if (!colors.light) return rankBg(hex, opacity);
    const { r, g, b } = shiftToward(hex, 255, 0.78);
    return `rgb(${r}, ${g}, ${b})`;
}

function rankInk(hex: string, colors: any) {
    return colors.light ? colors.text : hex;
}

function truncate(str: string | null | undefined, len: number) {
    if (!str) return '';
    return str.length > len ? str.substring(0, len - 1) + '…' : str;
}

export function createOrgChart(svgEl: SVGSVGElement, data: OrgNode): OrgChartHandle {
    const container = svgEl.parentElement as HTMLElement;

    let isMobile = window.innerWidth < LAYOUT.MOBILE_BREAKPOINT;
    const collapsed = new Set<string>();
    let showHandles = false;
    let searchTerm = '';
    let root: any;

    const nodeWidth = () => (isMobile ? LAYOUT.MOBILE_NODE_WIDTH : LAYOUT.NODE_WIDTH);
    const logoSize = () => (isMobile ? LAYOUT.MOBILE_LOGO_SIZE : LAYOUT.LOGO_SIZE);
    const platoonCols = () => (isMobile ? 1 : LAYOUT.PLATOON_COLS);

    const svg = d3.select(svgEl);
    const g = svg.append('g');
    const zoom = d3
        .zoom<SVGSVGElement, unknown>()
        .scaleExtent([0.2, 2])
        .on('zoom', (event: any) => g.attr('transform', event.transform));
    svg.call(zoom as any);

    function sized() {
        const width = container.getBoundingClientRect().width;
        const height = Math.max(560, window.innerHeight - 300);
        svg.attr('width', width).attr('height', height);
        return { width, height };
    }
    sized();

    function countChildMembers(d: OrgNode): number {
        let count = 0;
        d.children?.forEach((child) => {
            if (child.type === 'member') count++;
            else if (child.children) count += countChildMembers(child);
        });
        return count;
    }

    function getPlatoonAboveHeight(d: OrgNode) {
        let above = 14;
        if (d.description) above += 16;
        if (d.logo) above += logoSize() + 20;
        return above;
    }

    function nodeHeight(d: any) {
        if (d.data.type === 'platoon') return LAYOUT.LEADER_BOX_HEIGHT + getPlatoonAboveHeight(d.data) + 40;
        if (d.data.type === 'squad') return LAYOUT.LEADER_BOX_HEIGHT + 18;
        return LAYOUT.NODE_HEIGHT;
    }

    const maxCols = (d: any) => (d.data.type === 'division' ? 1 : LAYOUT.MAX_COLS);
    const rowSpacing = (parentType: string) =>
        parentType === 'platoon' ? LAYOUT.PLATOON_ROW_SPACING : LAYOUT.ROW_SPACING;

    function calcDimensions(node: any) {
        const w = nodeWidth();
        if (!node.children || node.children.length === 0) {
            node._width = w + LAYOUT.COL_SPACING;
            node._height = nodeHeight(node);
            return;
        }
        node.children.forEach(calcDimensions);

        if (node.data.type === 'division') {
            const leadershipGroup = node.children.find((c: any) => c.data.type === 'leadership-group');
            const platoons = node.children.filter((c: any) => c.data.type === 'platoon');
            const leadershipHeight = leadershipGroup ? leadershipGroup._height : 0;
            let platoonsWidth = 0;
            let platoonsHeight = 0;
            const cols = platoonCols();
            const numRows = Math.ceil(platoons.length / cols);
            for (let r = 0; r < numRows; r++) {
                const rp = platoons.slice(r * cols, (r + 1) * cols);
                platoonsWidth = Math.max(platoonsWidth, rp.reduce((s: number, p: any) => s + p._width, 0));
                platoonsHeight += Math.max(...rp.map((p: any) => p._height)) + LAYOUT.ROW_SPACING;
            }
            node._width = Math.max(leadershipGroup?._width || 0, platoonsWidth) + LAYOUT.COL_SPACING;
            node._height = nodeHeight(node) + leadershipHeight + platoonsHeight + LAYOUT.ROW_SPACING;
            return;
        }

        const cols = maxCols(node);
        const numRows = Math.ceil(node.children.length / cols);
        const spacing = rowSpacing(node.data.type);
        let totalWidth = 0;
        let totalHeight = spacing;
        for (let r = 0; r < numRows; r++) {
            const rc = node.children.slice(r * cols, Math.min(r * cols + cols, node.children.length));
            totalWidth = Math.max(totalWidth, rc.reduce((s: number, c: any) => s + c._width, 0));
            totalHeight += Math.max(...rc.map((c: any) => c._height)) + (r < numRows - 1 ? spacing / 2 : 0);
        }
        node._width = Math.max(w + LAYOUT.COL_SPACING, totalWidth);
        node._height = nodeHeight(node) + totalHeight;
    }

    function layoutTree(node: any, x: number, y: number) {
        node.x = x;
        node.y = y;
        if (!node.children || node.children.length === 0) return;

        if (node.data.type === 'division') {
            const leadershipGroup = node.children.find((c: any) => c.data.type === 'leadership-group');
            const platoons = node.children.filter((c: any) => c.data.type === 'platoon');
            let currentY = y + LAYOUT.ROW_SPACING;
            if (leadershipGroup) {
                layoutTree(leadershipGroup, x, currentY);
                currentY += leadershipGroup._height + LAYOUT.ROW_SPACING;
            }
            const cols = platoonCols();
            const numRows = Math.ceil(platoons.length / cols);
            for (let r = 0; r < numRows; r++) {
                const rp = platoons.slice(r * cols, (r + 1) * cols);
                const rowWidth = rp.reduce((s: number, p: any) => s + p._width, 0);
                let currentX = x - rowWidth / 2;
                rp.forEach((p: any) => {
                    layoutTree(p, currentX + p._width / 2, currentY);
                    currentX += p._width;
                });
                currentY += Math.max(...rp.map((p: any) => p._height)) + LAYOUT.ROW_SPACING;
            }
            return;
        }

        const cols = maxCols(node);
        const numRows = Math.ceil(node.children.length / cols);
        const spacing = rowSpacing(node.data.type);
        let currentY = y + spacing;
        for (let r = 0; r < numRows; r++) {
            const rc = node.children.slice(r * cols, Math.min(r * cols + cols, node.children.length));
            const rowWidth = rc.reduce((s: number, c: any) => s + c._width, 0);
            let currentX = x - rowWidth / 2;
            let maxRowHeight = 0;
            rc.forEach((child: any) => {
                layoutTree(child, currentX + child._width / 2, currentY);
                currentX += child._width;
                maxRowHeight = Math.max(maxRowHeight, child._height);
            });
            currentY += maxRowHeight + spacing / 2;
        }
    }

    function filterCollapsed(node: any) {
        const build = (source: any): any => {
            const nd = { ...source.data };
            if (source.children && !collapsed.has(source.data.id)) {
                nd._children = source.children.map(build);
            }
            nd._x0 = source.x0 || 0;
            nd._y0 = source.y0 || 0;
            return nd;
        };
        const copy = d3.hierarchy(build(node), (d: any) => d._children);
        copy.each((d: any) => {
            d.x0 = d.data._x0;
            d.y0 = d.data._y0;
        });
        return copy;
    }

    function isHighlighted(data: any) {
        if (!searchTerm) return false;
        const t = searchTerm.toLowerCase();
        return (
            data.name?.toLowerCase().includes(t) ||
            data.handle?.toLowerCase().includes(t) ||
            data.rankName?.toLowerCase().includes(t) ||
            data.leader?.name?.toLowerCase().includes(t) ||
            data.leader?.handle?.toLowerCase().includes(t) ||
            false
        );
    }

    const shouldShowHandle = (handle?: string | null) => {
        if (showHandles) return true;
        if (!searchTerm || !handle) return false;
        return handle.toLowerCase().includes(searchTerm.toLowerCase());
    };

    function toggleNode(d: any) {
        collapsed.has(d.data.id) ? collapsed.delete(d.data.id) : collapsed.add(d.data.id);
        update(d);
    }

    // --- rendering -------------------------------------------------------------

    function renderLeaderContent(ng: any, data: any, colors: any, opts: any = {}) {
        const fontSize = opts.fontSize || FONT.LEADER_NAME_SMALL;
        const handleFontSize = opts.handleFontSize || FONT.HANDLE_SMALL;
        const displayHandle = shouldShowHandle(data.handle);
        ng.append('text')
            .attr('y', displayHandle && data.handle ? -8 : 0)
            .attr('text-anchor', 'middle')
            .attr('fill', rankInk(data.rankColor, colors))
            .attr('font-size', fontSize)
            .attr('font-weight', '600')
            .style('pointer-events', 'none')
            .text(data.rankName);
        if (displayHandle && data.handle) {
            ng.append('text')
                .attr('y', 8)
                .attr('text-anchor', 'middle')
                .attr('fill', colors.textMuted)
                .attr('font-size', handleFontSize)
                .style('pointer-events', 'none')
                .text(data.handle);
        }
    }

    function renderTBA(ng: any, colors: any, fontSize: string) {
        ng.append('text')
            .attr('y', 0)
            .attr('text-anchor', 'middle')
            .attr('fill', colors.textMuted)
            .attr('font-size', fontSize)
            .attr('font-style', 'italic')
            .style('pointer-events', 'none')
            .text('TBA');
    }

    function renderIndicator(ng: any, isCollapsed: boolean, x: number, y: number, colors: any, fontSize: string) {
        ng.append('text')
            .attr('x', x)
            .attr('y', y)
            .attr('text-anchor', 'middle')
            .attr('fill', colors.textMuted)
            .attr('font-size', fontSize)
            .attr('font-weight', 'bold')
            .style('pointer-events', 'none')
            .text(isCollapsed ? '+' : '−');
    }

    function renderCount(ng: any, count: number, x: number, y: number, colors: any) {
        if (count > 0) {
            ng.append('text')
                .attr('x', x)
                .attr('y', y)
                .attr('text-anchor', 'middle')
                .attr('fill', colors.textMuted)
                .attr('font-size', '10px')
                .style('pointer-events', 'none')
                .text(`${count} member${count !== 1 ? 's' : ''}`);
        }
    }

    function renderNode(ng: any, d: any, colors: any) {
        const hl = isHighlighted(d.data);
        ng.classed('highlighted', hl);
        paintNode(ng, d, colors);
        if (!hl) return;

        const box = ng.select('rect');
        if (box.empty()) return;
        const bx = +box.attr('x');
        const by = +box.attr('y');
        ng.append('rect')
            .attr('x', bx - 3)
            .attr('y', by - 3)
            .attr('width', +box.attr('width') + 6)
            .attr('height', +box.attr('height') + 6)
            .attr('rx', 8)
            .attr('fill', 'none')
            .attr('stroke', colors.accent)
            .attr('stroke-width', 2.5)
            .style('pointer-events', 'none')
            .style('filter', `drop-shadow(0 0 6px ${colors.glow})`);
    }

    function paintNode(ng: any, d: any, colors: any) {
        const type = d.data.type;
        const w = nodeWidth();

        if (type === 'division') {
            if (d.data.logo) {
                ng.append('image')
                    .attr('x', -logoSize() / 2)
                    .attr('y', -logoSize() - 20)
                    .attr('width', logoSize())
                    .attr('height', logoSize())
                    .attr('href', d.data.logo)
                    .style('pointer-events', 'none');
            }
            ng.append('text')
                .attr('y', d.data.logo ? 6 : 0)
                .attr('text-anchor', 'middle')
                .attr('fill', colors.accent)
                .attr('font-size', FONT.DIVISION_NAME)
                .attr('font-weight', '700')
                .attr('letter-spacing', '1px')
                .text(d.data.name.toUpperCase());
            return;
        }

        if (type === 'leadership-group') {
            ng.append('text')
                .attr('y', 6)
                .attr('text-anchor', 'middle')
                .attr('fill', colors.text)
                .attr('font-size', FONT.PLATOON_NAME)
                .attr('font-weight', '600')
                .attr('letter-spacing', '2px')
                .text('DIVISION LEADERSHIP');
            return;
        }

        if (type === 'co' || type === 'xo') {
            const height = LAYOUT.NODE_HEIGHT;
            ng.append('rect')
                .attr('x', -w / 2)
                .attr('y', -height / 2)
                .attr('width', w)
                .attr('height', height)
                .attr('rx', 6)
                .attr('fill', rankFill(d.data.rankColor, colors, 0.15))
                .attr('stroke', d.data.rankColor)
                .attr('stroke-width', type === 'co' ? 2 : 1)
                .attr('stroke-opacity', type === 'co' ? 0.8 : 0.5);
            renderLeaderContent(ng, d.data, colors, { fontSize: FONT.LEADER_NAME });
            ng.append('text')
                .attr('y', -height / 2 - 6)
                .attr('text-anchor', 'middle')
                .attr('fill', colors.textMuted)
                .attr('font-size', FONT.LABEL)
                .attr('letter-spacing', '0.5px')
                .style('pointer-events', 'none')
                .text(type === 'co' ? 'COMMANDING OFFICER' : 'EXECUTIVE OFFICER');
            return;
        }

        if (type === 'platoon') {
            const data = d.data;
            const height = LAYOUT.LEADER_BOX_HEIGHT;
            const isCollapsible = data.children?.length > 0;
            const isCollapsed = collapsed.has(data.id);
            let cursor = -height / 2 - 8;
            if (data.description) {
                ng.append('text')
                    .attr('y', cursor)
                    .attr('text-anchor', 'middle')
                    .attr('fill', colors.textMuted)
                    .attr('font-size', FONT.SQUAD_NAME)
                    .text(truncate(data.description, 30));
                cursor -= 16;
            } else {
                cursor -= 6;
            }
            ng.append('text')
                .attr('y', cursor)
                .attr('text-anchor', 'middle')
                .attr('fill', colors.accent)
                .attr('font-size', FONT.PLATOON_NAME)
                .attr('font-weight', '700')
                .attr('letter-spacing', '1px')
                .text(data.name.toUpperCase());
            if (data.logo) {
                cursor -= 20;
                ng.append('image')
                    .attr('x', -logoSize() / 2)
                    .attr('y', cursor - logoSize())
                    .attr('width', logoSize())
                    .attr('height', logoSize())
                    .attr('href', data.logo)
                    .style('pointer-events', 'none');
            }
            const leaderColor = data.leader ? data.leader.rankColor : null;
            ng.append('rect')
                .attr('x', -w / 2)
                .attr('y', -height / 2)
                .attr('width', w)
                .attr('height', height)
                .attr('rx', 6)
                .attr('fill', leaderColor ? rankFill(leaderColor, colors, 0.15) : colors.bg)
                .attr('stroke', leaderColor || colors.accent)
                .attr('stroke-width', 1)
                .attr('stroke-opacity', 0.5)
                .style('cursor', isCollapsible ? 'pointer' : 'default')
                .on('click', () => isCollapsible && toggleNode(d));
            if (data.leader) renderLeaderContent(ng, data.leader, colors, { fontSize: FONT.LEADER_NAME_SMALL });
            else renderTBA(ng, colors, FONT.LEADER_NAME_SMALL);
            ng.append('text')
                .attr('y', height / 2 - 8)
                .attr('text-anchor', 'middle')
                .attr('fill', colors.textMuted)
                .attr('font-size', FONT.LABEL)
                .attr('letter-spacing', '0.5px')
                .style('pointer-events', 'none')
                .text('PLATOON LEADER');
            if (isCollapsible) {
                renderIndicator(ng, isCollapsed, w / 2 - 14, -height / 2 + 16, colors, FONT.COLLAPSE_INDICATOR);
                if (isCollapsed) renderCount(ng, countChildMembers(data), 0, height / 2 + 18, colors);
            }
            return;
        }

        if (type === 'squad') {
            const data = d.data;
            const height = LAYOUT.LEADER_BOX_HEIGHT;
            const isCollapsible = data.children?.length > 0;
            const isCollapsed = collapsed.has(data.id);
            const leaderColor = data.leader ? data.leader.rankColor : null;
            ng.append('text')
                .attr('y', -height / 2 - 12)
                .attr('text-anchor', 'middle')
                .attr('fill', colors.text)
                .attr('font-size', FONT.SQUAD_NAME)
                .attr('font-weight', '600')
                .attr('letter-spacing', '0.5px')
                .text(data.name);
            ng.append('rect')
                .attr('x', -w / 2)
                .attr('y', -height / 2)
                .attr('width', w)
                .attr('height', height)
                .attr('rx', 4)
                .attr('fill', leaderColor ? rankFill(leaderColor, colors, 0.12) : colors.bgDark)
                .attr('stroke', leaderColor || colors.border)
                .attr('stroke-opacity', 0.4)
                .style('cursor', isCollapsible ? 'pointer' : 'default')
                .on('click', () => isCollapsible && toggleNode(d));
            if (data.leader)
                renderLeaderContent(ng, data.leader, colors, { fontSize: '11px', handleFontSize: FONT.HANDLE_SMALL });
            else renderTBA(ng, colors, '11px');
            ng.append('text')
                .attr('y', height / 2 - 8)
                .attr('text-anchor', 'middle')
                .attr('fill', colors.textMuted)
                .attr('font-size', FONT.LABEL_SMALL)
                .attr('letter-spacing', '0.5px')
                .style('pointer-events', 'none')
                .text('SQUAD LEADER');
            if (isCollapsible) {
                renderIndicator(ng, isCollapsed, w / 2 - 12, -height / 2 + 14, colors, FONT.COLLAPSE_INDICATOR_SMALL);
                if (isCollapsed) renderCount(ng, countChildMembers(data), 0, height / 2 + 16, colors);
            }
            return;
        }

        // member
        const data = d.data;
        const height = LAYOUT.NODE_HEIGHT;
        const displayHandle = shouldShowHandle(data.handle);
        ng.append('rect')
            .attr('x', -w / 2)
            .attr('y', -height / 2)
            .attr('width', w)
            .attr('height', height)
            .attr('rx', 4)
            .attr('fill', rankFill(data.rankColor, colors, 0.25))
            .attr('stroke', data.rankColor)
            .attr('stroke-opacity', 0.4);
        ng.append('text')
            .attr('y', displayHandle && data.handle ? -6 : 4)
            .attr('text-anchor', 'middle')
            .attr('fill', rankInk(data.rankColor, colors))
            .attr('font-size', FONT.MEMBER_NAME)
            .attr('font-weight', '600')
            .style('pointer-events', 'none')
            .text(data.rankName);
        if (displayHandle && data.handle) {
            ng.append('text')
                .attr('y', 12)
                .attr('text-anchor', 'middle')
                .attr('fill', colors.text)
                .attr('font-size', FONT.HANDLE)
                .style('pointer-events', 'none')
                .text(truncate(data.handle, 18));
        }
    }

    function update(source: any) {
        const colors = themeColors();
        const duration = 300;
        const filteredRoot = filterCollapsed(root);
        calcDimensions(filteredRoot);
        layoutTree(filteredRoot, 0, 0);
        const nodes = filteredRoot.descendants();

        const node = g.selectAll<SVGGElement, any>('.node').data(nodes, (d: any) => d.data.id);

        const nodeEnter = node
            .enter()
            .append('g')
            .attr('class', (d: any) => `node node-${d.data.type}`)
            .attr('transform', `translate(${source.x0 ?? 0},${source.y0 ?? 0})`)
            .style('opacity', 0);

        nodeEnter.each(function (this: SVGGElement, d: any) {
            renderNode(d3.select(this), d, colors);
        });
        node.each(function (this: SVGGElement, d: any) {
            const sel = d3.select(this);
            sel.selectAll('*').remove();
            renderNode(sel, d, colors);
        });

        nodeEnter
            .merge(node as any)
            .transition()
            .duration(duration)
            .attr('transform', (d: any) => `translate(${d.x},${d.y})`)
            .style('opacity', 1);

        node.exit()
            .transition()
            .duration(duration)
            .attr('transform', `translate(${source.x ?? 0},${source.y ?? 0})`)
            .style('opacity', 0)
            .remove();

        nodes.forEach((d: any) => {
            d.x0 = d.x;
            d.y0 = d.y;
        });
    }

    function centerTree() {
        if (!root) return;
        const bounds = (g.node() as SVGGElement).getBBox();
        const { width, height } = svg.node()!.getBoundingClientRect();
        const scale = Math.min(width / (bounds.width + 100), height / (bounds.height + 100), 1);
        const x = width / 2 - (bounds.x + bounds.width / 2) * scale;
        svg.transition()
            .duration(500)
            .call(zoom.transform as any, d3.zoomIdentity.translate(x, 80).scale(scale));
    }

    function collapseAll() {
        root.descendants().forEach((d: any) => {
            if ((d.data.type === 'platoon' || d.data.type === 'squad') && d.data.children?.length > 0) {
                collapsed.add(d.data.id);
            }
        });
    }

    function expandToHighlighted() {
        if (!searchTerm || !root) return;
        root.descendants().forEach((d: any) => {
            if (isHighlighted(d.data)) {
                let parent = d.parent;
                while (parent) {
                    collapsed.delete(parent.data.id);
                    parent = parent.parent;
                }
            }
            d.data.children?.forEach((child: any) => {
                if (child.type === 'member' && isHighlighted(child)) collapsed.delete(d.data.id);
            });
        });
    }

    function findMatches(): SearchMatch[] {
        if (!searchTerm || !root) return [];
        const t = searchTerm.toLowerCase();
        const results: SearchMatch[] = [];
        root.descendants().forEach((d: any) => {
            const data = d.data;
            if (['member', 'co', 'xo'].includes(data.type)) {
                if (
                    data.name?.toLowerCase().includes(t) ||
                    data.handle?.toLowerCase().includes(t) ||
                    data.rankName?.toLowerCase().includes(t)
                ) {
                    results.push({
                        id: data.id,
                        name: data.name,
                        rankName: data.rankName,
                        rankColor: data.rankColor,
                        handle: data.handle,
                    });
                }
            }
            if ((data.type === 'platoon' || data.type === 'squad') && data.leader) {
                if (data.leader.name?.toLowerCase().includes(t) || data.leader.handle?.toLowerCase().includes(t)) {
                    results.push({
                        id: data.id,
                        name: data.leader.name,
                        rankName: data.leader.rankName,
                        rankColor: data.leader.rankColor,
                        handle: data.leader.handle,
                    });
                }
            }
        });
        return results;
    }

    function focusNode(id: string) {
        const target = root.descendants().find((d: any) => d.data.id === id);
        if (!target) return;
        let parent = target.parent;
        while (parent) {
            collapsed.delete(parent.data.id);
            parent = parent.parent;
        }
        update(root);
        setTimeout(() => {
            const sel = g.selectAll<SVGGElement, any>('.node').filter((d: any) => d.data.id === id);
            if (sel.empty()) return;
            const td: any = sel.datum();
            if (typeof td?.x !== 'number') return;
            const { width, height } = svg.node()!.getBoundingClientRect();
            svg.transition()
                .duration(500)
                .call(zoom.transform as any, d3.zoomIdentity.translate(width / 2 - td.x, height / 2 - td.y).scale(1));
        }, 350);
    }

    function render() {
        root = d3.hierarchy(data as any);
        root.x0 = 0;
        root.y0 = 0;
        root.descendants().forEach((d: any) => {
            if ((d.data.type === 'platoon' || d.data.type === 'squad') && d.data.children?.length > 0) {
                collapsed.add(d.data.id);
            }
        });
        update(root);
        centerTree();
    }
    render();

    let resizeTimer: number | undefined;
    const onResize = () => {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(() => {
            isMobile = window.innerWidth < LAYOUT.MOBILE_BREAKPOINT;
            sized();
            if (root) {
                update(root);
                centerTree();
            }
        }, 250);
    };
    window.addEventListener('resize', onResize);

    return {
        zoomIn: () => svg.transition().call(zoom.scaleBy as any, 1.3),
        zoomOut: () => svg.transition().call(zoom.scaleBy as any, 0.7),
        resetView: () => centerTree(),
        expandAll: () => {
            collapsed.clear();
            update(root);
        },
        collapseAll: () => {
            collapseAll();
            update(root);
        },
        toggleHandles: () => {
            showHandles = !showHandles;
            update(root);
            return showHandles;
        },
        exportPng: () => {
            const bounds = (g.node() as SVGGElement).getBBox();
            const pad = 40;
            const cloned = svgEl.cloneNode(true) as SVGSVGElement;
            const clonedG = cloned.querySelector('g')!;
            cloned.setAttribute('width', String(bounds.width + pad * 2));
            cloned.setAttribute('height', String(bounds.height + pad * 2));
            clonedG.setAttribute('transform', `translate(${-bounds.x + pad}, ${-bounds.y + pad})`);
            const bg = themeColors().bgDark;
            cloned.style.backgroundColor = bg;
            const svgString = new XMLSerializer().serializeToString(cloned);
            const url = URL.createObjectURL(new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' }));
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const scale = 2;
                canvas.width = (bounds.width + pad * 2) * scale;
                canvas.height = (bounds.height + pad * 2) * scale;
                const ctx = canvas.getContext('2d')!;
                ctx.scale(scale, scale);
                ctx.fillStyle = bg;
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0);
                const a = document.createElement('a');
                a.href = canvas.toDataURL('image/png');
                a.download = 'org-chart.png';
                a.click();
                URL.revokeObjectURL(url);
            };
            img.src = url;
        },
        setSearch: (term: string) => {
            searchTerm = term.trim();
            if (root) {
                if (searchTerm) expandToHighlighted();
                update(root);
            }
            return searchTerm ? findMatches() : [];
        },
        focusNode,
        resize: onResize,
        destroy: () => {
            window.removeEventListener('resize', onResize);
            svg.selectAll('*').remove();
        },
    };
}
