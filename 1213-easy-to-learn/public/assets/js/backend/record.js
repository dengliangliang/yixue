define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'record/index' + location.search,
                    add_url: 'record/add',
                    //edit_url: 'record/edit',
                    //del_url: 'record/del',
                    multi_url: 'record/multi',
                    import_url: 'record/import',
                    table: 'record',
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
                        //{field: 'user_id', title: __('User_id')},
                        {field: 'user.username', title: __('客户ID'), operate: 'LIKE'},
                        {field: 'area.shortname', title: __('城市')},
                        //{field: 'user_name', title: __('User_name'), operate: 'LIKE'},
                        {field: 'gender', title: __('Gender'), searchList: {"0":__('Gender 0'),"1":__('Gender 1')}, formatter: Table.api.formatter.normal},
                        {field: 'yang_li_date', title: __('Yang_li_date'), operate: 'LIKE'},
                        {field: 'yin_li_date', title: __('阴历'), operate: 'LIKE'},
                        {field: 'hour', title: __('时辰')},

                        {field: 'minute', title: __('Minute')},
                        {field: 'max_wu_xing', title: __('五行最多'), operate: 'LIKE'},
                        {field: 'min_wu_xing', title: __('五行最少'), operate: 'LIKE'},
                        {field: 'result', title: __('是否完成测算'), operate: 'LIKE'},
                        {field: 'createtime', title: __('测算时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        //{field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        //{field: 'zhen_hour', title: __('Zhen_hour')},
                        //{field: 'zhen_minute', title: __('Zhen_minute')},
                        //{field: 'zhen_yang_day', title: __('Zhen_yang_day'), operate: 'LIKE'},

                        //{field: 'ju_ben_gan_zhi', title: __('Ju_ben_gan_zhi'), operate: 'LIKE'},
                        //{field: 'merchantId', title: __('MerchantId'), operate: 'LIKE'},
                        //{field: 'activityCode', title: __('ActivityCode'), operate: 'LIKE'},
                        //{field: 'agentCode', title: __('AgentCode'), operate: 'LIKE'},
                        //{field: 'customerNo', title: __('CustomerNo'), operate: 'LIKE'},


                        //{field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
