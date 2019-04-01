@extends('common')
@section('sidebar')
    <div>记录总数概览:{{$host->quantity}} <a href="/rank/{{$host->host_id}}">关键词排名</a></div>

    <table id="table"></table>
    <div id="loading">
        <img id="load_img" src="/img/loading_red.gif">
        <span id="status">正在查询第1页...</span>
    </div>

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

        function load(sucsess_fun) {
            var url = '/url/' + host_id;
            ajax_get(url, sucsess_fun, {last: last});
        }

        function consult_status() {
            $('.no-records-found').remove()
            var url = '/status/' + host_id;
            var func = function (response) {
                if (response.status == 0) {
                    $("#load_img").remove()
                    //$("#loading").fadeOut()
                    table.bootstrapTable('refresh');
                    $("#status").text('查询完成');
                    clearInterval(interval_handle);

                } else {
                    var text = "正在查询第" + response.status + "页..."
                    $("#status").text(text);

                }
                update_table();


            }
            ajax_get(url, func, {last: last});


        }

        function init_table() {
            var func = function (response) {
                last = response.last_id;
                table = $('#table').bootstrapTable({
                    columns: [{
                        field: 'id',
                        title: ' ID'
                    }, {
                        field: 'url',
                        title: 'url'
                    }, {
                        field: 'keyword',
                        title: '关键词'
                    }, {
                        field: 'http_code',
                        title: 'http_code'
                    }],
                    data: JSON.parse(response.urls)
                })
            }
            load(func);
            $('.no-records-found').remove()
        }

        function update_table() {
            func = function (response) {
                last = response.last_id;
                urls = JSON.parse(response.urls);
                for (i = 0; i < urls.length; i++) {
                    append_table_row(urls[i]);
                }

            }
            load(func);
        }

        function append_table_row(data) {
            var templete = "<tr><td>" + data.id + "</td><td>" + data.url + "</td><td>" + data.keyword + "</td><td>" + data.http_code + "</td></tr>";
            $("#table").append(templete);
        }


        $(document).ready(function () {
                init_table()
                interval_handle = setInterval(consult_status, 30000)
            }
        )


    </script>


@endsection