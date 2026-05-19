define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop.order.log/index' + location.search,
                    add_url: 'shop.order.log/add',
                    edit_url: 'shop.order.log/edit',
                    del_url: 'shop.order.log/del',
                    multi_url: 'shop.order.log/multi',
                    import_url: 'shop.order.log/import',
                    table: 'shop_order_log',
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
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'order_id', title: __('Order_id')},
                        {field: 'order_no', title: __('Order_no'), operate: 'LIKE'},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'admin_id', title: __('Admin_id')},
                        {field: 'action', title: __('Action'), operate: 'LIKE'},
                        {field: 'from_status', title: __('From_status'), operate: 'LIKE', formatter: Table.api.formatter.status},
                        {field: 'to_status', title: __('To_status'), operate: 'LIKE', formatter: Table.api.formatter.status},
                        {field: 'memo', title: __('Memo'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
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
