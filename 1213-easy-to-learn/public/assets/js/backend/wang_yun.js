define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'wang_yun/index' + location.search,
                    //add_url: 'wang_yun/add',
                    edit_url: 'wang_yun/edit',
                    //del_url: 'wang_yun/del',
                    multi_url: 'wang_yun/multi',
                    import_url: 'wang_yun/import',
                    table: 'wang_yun',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                search:false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'wu_xing_name', title: __('Wu_xing_name'), operate: 'LIKE'},
                        {field: 'fang_wei', title: __('Fang_wei'), operate: 'LIKE'},
                        {field: 'color', title: __('Color'), operate: 'LIKE'},
                        {field: 'num', title: __('Num'), operate: 'LIKE'},
                        {field: 'shi_pin', title: __('Shi_pin'), operate: 'LIKE'},
                        {field: 'ji_jie', title: __('Ji_jie'), operate: 'LIKE'},
                        {field: 'zhu_yi', title: __('Zhu_yi'), operate: 'LIKE'},
                        {field: 'time', title: __('Time'), operate: 'LIKE'},
                        {field: 'kou_wei', title: __('Kou_wei'), operate: 'LIKE'},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
