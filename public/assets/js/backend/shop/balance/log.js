define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/balance/log/index' + location.search,
                    add_url: 'shop/balance/log/add',
                    edit_url: 'shop/balance/log/edit',
                    del_url: 'shop/balance/log/del',
                    multi_url: 'shop/balance/log/multi',
                    import_url: 'shop/balance/log/import',
                    table: 'shop_balance_log',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                fixedColumns: true,
                fixedRightNumber: 1,
                searchFormVisible: true,
                search:false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {
                            field: 'user_id',
                            title: __('User_name'),
                            operate: '=',
                            addClass: 'selectpage',
                            extend: "data-source='shop/user/index' data-field='nickname'",
                            formatter: function (value, row) {
                                return (row.user && (row.user.nickname || row.user.username)) || row.user_id || '-';
                            }
                        },
                        {field: 'type', title: __('Type'), searchList: {"recharge":__('Type recharge'),"pay":__('Type pay'),"refund":__('Type refund'),"recycle":__('Type recycle'),"withdraw":__('Type withdraw'),"withdraw_reject":__('Type withdraw_reject'),"freeze":__('Type freeze'),"unfreeze":__('Type unfreeze'),"adjust":__('Type adjust')}, formatter: Table.api.formatter.normal},
                        {
                            field: 'order.order_no',
                            title: __('Order_no'),
                            operate: false,
                            formatter: function (value, row) {
                                return (row.order && row.order.order_no) || '-';
                            }
                        },
                        {
                            field: 'recharge.recharge_no',
                            title: __('Recharge_no'),
                            operate: false,
                            formatter: function (value, row) {
                                return (row.recharge && row.recharge.recharge_no) || '-';
                            }
                        },
                        {
                            field: 'withdraw.withdraw_no',
                            title: __('Withdraw_no'),
                            operate: false,
                            formatter: function (value, row) {
                                return (row.withdraw && row.withdraw.withdraw_no) || '-';
                            }
                        },
                        {
                            field: 'refund.refund_no',
                            title: __('Refund_no'),
                            operate: false,
                            formatter: function (value, row) {
                                return (row.refund && row.refund.refund_no) || '-';
                            }
                        },
                        {field: 'money', title: __('Money'), operate:false},
                        {field: 'before', title: __('Before'), operate:false},
                        {field: 'after', title: __('After'), operate:false},
                        {field: 'memo', title: __('Memo'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
