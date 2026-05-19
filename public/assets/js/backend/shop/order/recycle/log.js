define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop.order.recycle.log/index' + location.search,
                    add_url: 'shop.order.recycle.log/add',
                    edit_url: 'shop.order.recycle.log/edit',
                    del_url: 'shop.order.recycle.log/del',
                    multi_url: 'shop.order.recycle.log/multi',
                    import_url: 'shop.order.recycle.log/import',
                    table: 'shop_order_recycle_log',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'recycle_id', title: __('Recycle_id')},
                        {field: 'order_id', title: __('Order_id')},
                        {field: 'order_no', title: __('Order_no'), operate: 'LIKE'},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'admin_id', title: __('Admin_id')},
                        {field: 'action', title: __('Action'), searchList: {"recycle":__('Action recycle'),"approve":__('Action approve'),"reject":__('Action reject'),"restore":__('Action restore'),"delete":__('Action delete')}, formatter: Table.api.formatter.normal},
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
