@extends('common')
@section('sidebar')
    <div><a href="/keyword/{{$host->host_id}}">关键词排名</a></div>

    <table id="table" class="table table-bordered table-hover table-striped" style="word-break:break-all; word-wrap:break-all;"></table>

    <script>
        var host_id = "{{$host_id}}";
        var last = 1;
        var table = {};

        function ajax_get(url, sucsess_fun, data={}) {
            $.ajax({
                url: url,
                type: "get",
                data: data,
                success: function (response) {
                    sucsess_fun(response)

                },
                error: function (xhr) {
                }
            });
        }


        function consult_status() {
            $('.no-records-found').remove()
            var url = '/status/' + host_id;
            var func = function (response) {
                if (response.status == 1) {
                    clearInterval(interval_handle);

                } else {
                    table.bootstrapTable('refresh');

                }


            }
            ajax_get(url, func);


        }

        function init_table() {
            var url = '/url/' + host_id;

            table = $('#table').bootstrapTable({
                method: "get",
                url: url,
                search: true,
                pagination: false,
                columns: [{
                    title: 'id',
                    formatter: function (value, row, index) {
                        return index + 1;
                    }
                }, {
                    field: 'url',
                    title: 'url'
                }, {
                    field: 'keyword',
                    title: '关键词'
                }, {
                    field: 'http_code',
                    title: 'http_code'
                },
                    {
                        field: 'title',
                        title: '标题'
                    },

                    {
                        field: 'description',
                        title: '描述'
                    },

                    {
                        field: 'first_keyword',
                        title: '第一个关键词'
                    },

                    {
                        field: 'rank',
                        title: '排名'
                    },
                    {
                        field: 'snapshot_date',
                        title: '快照时间'
                    },

                ]
            })
        }


        $(document).ready(function () {
                init_table()
                interval_handle = setInterval(consult_status, 2000)
            }
        )


    </script>


@endsection