define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop.order.delivery/index' + location.search,
                    add_url: 'shop.order.delivery/add',
                    edit_url: 'shop.order.delivery/edit',
                    del_url: 'shop.order.delivery/del',
                    multi_url: 'shop.order.delivery/multi',
                    import_url: 'shop.order.delivery/import',
                    table: 'shop_order_delivery',
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
                        {field: 'delivery_no', title: __('Delivery_no'), operate: 'LIKE'},
                        {field: 'order_id', title: __('Order_id')},
                        {field: 'order_no', title: __('Order_no'), operate: 'LIKE'},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'delivery_type', title: __('Delivery_type'), searchList: {"express":__('Delivery_type express'),"manual":__('Delivery_type manual'),"virtual":__('Delivery_type virtual')}, formatter: Table.api.formatter.normal},
                        {field: 'express_company', title: __('Express_company'), operate: 'LIKE'},
                        {field: 'express_no', title: __('Express_no'), operate: 'LIKE'},
                        {field: 'receiver_name', title: __('Receiver_name'), operate: 'LIKE'},
                        {field: 'receiver_mobile', title: __('Receiver_mobile'), operate: 'LIKE'},
                        {field: 'receiver_address', title: __('Receiver_address'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'remark', title: __('Remark'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'admin_id', title: __('Admin_id')},
                        {field: 'status', title: __('Status'), searchList: {"shipped":__('Status shipped'),"received":__('Status received'),"cancelled":__('Status cancelled')}, formatter: Table.api.formatter.status},
                        {field: 'shiptime', title: __('Shiptime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'receivetime', title: __('Receivetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
