define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/order/index' + location.search,
                    add_url: 'shop/order/add',
                    edit_url: 'shop/order/edit',
                    del_url: 'shop/order/del',
                    multi_url: 'shop/order/multi',
                    import_url: 'shop/order/import',
                    table: 'shop_order',
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
                        {field: 'order_no', title: __('Order_no'), operate: 'LIKE'},
                        {
                            field: 'user.nickname',
                            title: __('User_name'),
                            operate: false,
                            formatter: function (value, row) {
                                return (row.user && (row.user.nickname || row.user.username)) || row.user_id || '-';
                            }
                        },
                        {field: 'status', title: __('Status'), searchList: {"unpaid":__('Status unpaid'),"paid":__('Status paid'),"shipped":__('Status shipped'),"completed":__('Status completed'),"returned":__('Status returned'),"cancelled":__('Status cancelled'),"refunding":__('Status refunding'),"refunded":__('Status refunded'),"recycled":__('Status recycled')}, formatter: Table.api.formatter.status},
                        {field: 'pay_type', title: __('Pay_type'), searchList: {"balance":__('Pay_type balance')}, formatter: Table.api.formatter.normal},
                        {field: 'pay_status', title: __('Pay_status'), searchList: {"unpaid":__('Pay_status unpaid'),"paid":__('Pay_status paid'),"refunded":__('Pay_status refunded')}, formatter: Table.api.formatter.status},
                        {field: 'freight_amount', title: __('Freight_amount'), operate:'BETWEEN'},
                        {field: 'pay_amount', title: __('Pay_amount'), operate:'BETWEEN'},
                        {field: 'total_quantity', title: __('Total_quantity')},
                        {field: 'admin_remark', title: __('Admin_remark'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'detail',
                                    text: __('Order detail'),
                                    title: __('Order detail'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-list-alt',
                                    url: 'shop/order/detail'
                                },
                                {
                                    name: 'ship',
                                    text: __('Ship order'),
                                    title: __('Ship order'),
                                    classname: 'btn btn-xs btn-primary btn-ajax',
                                    icon: 'fa fa-truck',
                                    url: 'shop/order/ship',
                                    confirm: __('Are you sure you want to ship this order?'),
                                    refresh: true,
                                    visible: function (row) {
                                        return row.pay_status === 'paid' && row.status === 'paid';
                                    }
                                },
                                {
                                    name: 'complete',
                                    text: __('Complete order'),
                                    title: __('Complete order'),
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    icon: 'fa fa-check',
                                    url: 'shop/order/complete',
                                    confirm: __('Are you sure you want to complete this order?'),
                                    refresh: true,
                                    visible: function (row) {
                                        return row.pay_status === 'paid' && row.status === 'shipped';
                                    }
                                },
                                {
                                    name: 'returnorder',
                                    text: __('Return order'),
                                    title: __('Return order'),
                                    classname: 'btn btn-xs btn-danger btn-dialog',
                                    icon: 'fa fa-reply',
                                    url: 'shop/order/returnorder',
                                    refresh: true,
                                    visible: function (row) {
                                        return row.pay_status === 'paid' && row.status === 'completed' && row.recycle_status !== 'pending' && row.recycle_status !== 'approved' && row.recycle_status !== 'recycled';
                                    }
                                },
                                {
                                    name: 'recycle',
                                    text: __('Recycle order'),
                                    title: __('Recycle order'),
                                    classname: 'btn btn-xs btn-warning btn-ajax',
                                    icon: 'fa fa-recycle',
                                    url: 'shop/order/recycle',
                                    confirm: __('Are you sure you want to recycle this completed order?'),
                                    refresh: true,
                                    visible: function (row) {
                                        return row.status === 'completed' && row.recycle_status !== 'pending' && row.recycle_status !== 'approved' && row.recycle_status !== 'recycled';
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'shop/order/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '140px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'shop/order/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'shop/order/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
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
        returnorder: function () {
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
