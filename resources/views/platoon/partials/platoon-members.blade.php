<div class="panel panel-filled">
    <div class='panel-body border-bottom'>
        <div id='playerFilter'></div>
    </div>
    <div class="table-responsive">

        <table class='table table-striped table-hover members-table'>
            <thead>
            <tr>
                <th class='col-hidden'><strong>Rank Id</strong></th>
                <th class='col-hidden'><strong>Last Login Date</strong></th>
                <th><strong>Member</strong></th>
                <th class='nosearch text-center'><strong>Rank</strong></th>
                <th class='text-center hidden-xs hidden-sm'><strong>Joined</strong></th>
                <th class='text-center'><strong>Last Activity</strong></th>
                <th class='text-center'>
                    <string>Last Promoted</string>
                </th>
            </tr>
            </thead>

            <tbody>

            @foreach($members as $member)
                <tr role="row">
                    <td class="col-hidden">{{ $member->rank_id }}</td>
                    <td class="col-hidden">{{ $member->last_activity }}</td>
                    <td class="">{!! $member->present()->nameWithIcon !!} <a
                                href="{{ route('member', $member->clan_id) }}"><i
                                    class="fa fa-search text-muted pull-right" title="View profile"></i></a></td>
                    <td class="text-center">{{ $member->rank->abbreviation }}</td>
                    <td class="text-center hidden-xs hidden-sm">{{ $member->join_date }}</td>
                    <td class="text-center">
                        <span class="{{ getActivityClass($member->last_activity, $division) }}">{{ $member->present()->lastActive }}</span>
                    </td>
                    <td class="text-center">{{ $member->last_promoted }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    var Platoon = Platoon || {};

    (function ($) {

        Platoon = {

            setup: function () {
                this.handleMembers();
            },

            handleMembers: function () {

                var platoonNum = parseInt($('.platoon-number').text()),
                    formattedDate = new Date(),
                    d = formattedDate.getDate(),
                    m = (formattedDate.getMonth() + 1),
                    y = formattedDate.getFullYear(),
                    nowDate = y + "-" + m + "-" + d,
                    selected = new Array();

                $('table.members-table').DataTable({
                    "autoWidth": true,
                    "sDom": 'T<"clear">tfrip',
                    "order": [],
                    "columnDefs": [{
                        "targets": 'no-search',
                        "searchable": false
                    }, {
                        "targets": 'col-hidden',
                        "visible": false,
                        "searchable": false
                    }, {
                        // sort rank by rank id
                        "iDataSort": 0,
                        "aTargets": [3]
                    }, {
                        // sort activity by last login date
                        "iDataSort": 1,
                        "aTargets": [5]
                    }],
                    stateSave: false,
                    paging: false,
                    "bServerSide": false,
                    "drawCallback": function (settings) {
                        $("#member-footer").empty();
                        $("#members-table_info").contents().appendTo("#member-footer");
                    },

                    "oTableTools": {
                        "sRowSelect": "multi",
                        "sSwfPath": "/swf/copy_csv_xls_pdf.swf",
                        "aButtons": [{

                            "sExtends": "text",
                            "fnSelect": function (nButton, oConfig, nRow) {
                                console.log($(nRow).data('id') + " clicked")
                            },
                            "sExtends": "collection",
                            "sButtonText": "",
                            "mColumns": "visible",
                            "aButtons": ["select_all", "select_none", {
                                "sExtends": "pdf",
                                "sPdfOrientation": "landscape",
                                "sFileName": "AOD Plt " + platoonNum + "_" + nowDate + ".pdf",
                                "mColumns": "visible"
                            }, {
                                "sExtends": "csv",
                                "sFileName": "AOD Plt " + platoonNum + "_" + nowDate + ".csv",
                                "mColumns": "visible"
                            }],
                            "bSelectedOnly": true
                        }]
                    }
                });


                $(".dataTables_info").remove();
                $(".dataTables_filter input").appendTo("#playerFilter").removeClass('input-sm');
                $("#playerFilter input").attr({
                    "placeholder": "Search Players",
                    "class": "form-control input-lg"
                });
                $(".dataTables_filter label").remove();

                $(".DTTT_container .DTTT_button").removeClass('DTTT_button').remove();
                $(".DTTT_container").appendTo('.download-area').remove();

                $(".DTTT_container a").addClass('btn btn-xs btn-info tool').attr('title', 'Download table data').text("Export").css('margin-top', '5px').remove();

                $(".no-sort").removeClass("sorting");

            },
        }
    })(jQuery);

    Platoon.setup();

</script>