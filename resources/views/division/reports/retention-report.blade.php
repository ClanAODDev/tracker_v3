@extends('application.base')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large"/>
        @endslot
        @slot ('heading')
            Member Retention
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('retention-report', $division) !!}

        <div class="panel">
            <div class="panel-body">
                <div class="flot-chart">
                    <div class="flot-chart-content" id="flot-line-chart"
                         data-recruits="{{ $recruits }}"
                         data-removals="{{ $removals }}"
                         data-population="{{ $population }}"
                    ></div>
                </div>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <i class="fas fa-dot-circle text-success"></i> - Members Recruited
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="fas fa-dot-circle text-warning"></i> - Members Removed
                    </div>

                    <div class="col-md-4 text-center">
                        <i class="fas fa-dot-circle text-info"></i> - Population
                    </div>
                </div>
            </div>
        </div>

        <hr/>

        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-filled">
                    <div class="panel-body">
                        <form>
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="start">Start date</label>
                                        <input type="date" class="form-control" name="start"
                                               id="start" placeholder="yyyy-mm-dd"
                                               value="{{ $range['start'] }}">
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="end">End date</label>
                                        <input type="date" class="form-control"
                                               name="end" id="end" placeholder="yyyy-mm-dd"
                                               value="{{ $range['end'] }}">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-default pull-right">Submit</button>
                        </form>
                    </div>
                </div>

            </div>
            <div class="col-md-6">
                <div class="panel panel-filled">
                    <div class="panel-heading">
                        Recruiting Counts
                        <span class="pull-right">{{ $totalRecruitCount }}</span>
                    </div>
                    @foreach ($members as $item)

                        @if (is_object($item['member']))
                            <li class="list-group-item">
                                <a href="{{ route('member', $item['member']->getUrlParams()) }}">
                                    {{ $item['member']->present()->rankName }}
                                </a>
                                <span class="pull-right badge">{{ $item['recruits'] }}</span>
                            </li>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        !function (r) {
            function o(r, o, e, i) {
                var s = 'categories' == o.xaxis.options.mode, n = 'categories' == o.yaxis.options.mode;
                if (s || n) {
                    var a = i.format;
                    if (!a) {
                        var t = o;
                        if ((a = []).push({x: !0, number: !0, required: !0}), a.push({
                            y: !0,
                            number: !0,
                            required: !0
                        }), t.bars.show || t.lines.show && t.lines.fill) {
                            var u = !!(t.bars.show && t.bars.zero || t.lines.show && t.lines.zero);
                            a.push({
                                y: !0,
                                number: !0,
                                required: !1,
                                defaultValue: 0,
                                autoscale: u
                            }), t.bars.horizontal && (delete a[a.length - 1].y, a[a.length - 1].x = !0);
                        }
                        i.format = a;
                    }
                    for (var f = 0; f < a.length; ++f) a[f].x && s && (a[f].number = !1), a[f].y && n && (a[f].number = !1);
                }
            }

            function e(r) {
                var o = [];
                for (var e in r.categories) {
                    var i = r.categories[e];
                    i >= r.min && i <= r.max && o.push([i, e]);
                }
                return o.sort(function (r, o) {
                    return r[0] - o[0];
                }), o;
            }

            function i(o, i, s) {
                if ('categories' == o[i].options.mode) {
                    if (!o[i].categories) {
                        var n = {}, a = o[i].options.categories || {};
                        if (r.isArray(a)) for (var t = 0; t < a.length; ++t) n[a[t]] = t; else for (var u in a) n[u] = a[u];
                        o[i].categories = n;
                    }
                    o[i].options.ticks || (o[i].options.ticks = e), function (r, o, e) {
                        for (var i = r.points, s = r.pointsize, n = r.format, a = o.charAt(0), t = function (r) {
                            var o = -1;
                            for (var e in r) r[e] > o && (o = r[e]);
                            return o + 1;
                        }(e), u = 0; u < i.length; u += s) if (null != i[u]) for (var f = 0; f < s; ++f) {
                            var c = i[u + f];
                            null != c && n[f][a] && (c in e || (e[c] = t, ++t), i[u + f] = e[c]);
                        }
                    }(s, i, o[i].categories);
                }
            }

            function s(r, o, e) {
                i(o, 'xaxis', e), i(o, 'yaxis', e);
            }

            r.plot.plugins.push({
                init: function (r) {
                    r.hooks.processRawData.push(o), r.hooks.processDatapoints.push(s);
                },
                options: {xaxis: {categories: null}, yaxis: {categories: null}},
                name: 'categories',
                version: '1.0'
            });
        }(jQuery);
    </script>


    <script>
        // Flot charts data and options
        let data1 = $('#flot-line-chart').data('recruits'),
            data2 = $('#flot-line-chart').data('removals'),
            data3 = $('#flot-line-chart').data('population'),
            chartUsersOptions = {

                series: {

                    points: {
                        show: true,
                        radius: 2,
                        symbol: 'circle'
                    },

                    splines: {
                        show: true,
                        tension: 0.4,
                        lineWidth: 1,
                        fill: .10,
                    }
                },

                grid: {
                    tickColor: '#404652',
                    borderWidth: 1,
                    color: '#000',
                    borderColor: '#404652',
                },

                comment: {
                    show: true,
                },

                tooltip: false,

                tooltippage: {
                    show: true,
                    content: '%y members'
                },

                xaxis: {
                    mode: 'categories',
                    tickLength: 0,
                },

                colors: ['#1bbf89', '#f7af3e', '#0F83C9']
            };

        $.plot($('#flot-line-chart'), [data1, data2, data3], chartUsersOptions);

        $(window).resize(function () {
            $.plot($('#flot-line-chart'), [data1, data2, data3], chartUsersOptions);
        });
    </script>

@stop