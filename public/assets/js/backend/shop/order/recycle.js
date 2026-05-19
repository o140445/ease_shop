define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop.order.recycle/index' + location.search,
                    add_url: 'shop.order.recycle/add',
                    edit_url: 'shop.order.recycle/edit',
                    del_url: 'shop.order.recycle/del',
                    multi_url: 'shop.order.recycle/multi',
                    import_url: 'shop.order.recycle/import',
                    table: 'shop_order_recycle',
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
                        {field: 'order_no', title: __('Order_no'), operate: 'LIKE'},
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
                        {field: 'status', title: __('Status'), searchList: {"pending":__('Status pending'),"approved":__('Status approved'),"rejected":__('Status rejected'),"recycled":__('Status recycled'),"restored":__('Status restored'),"deleted":__('Status deleted')}, formatter: Table.api.formatter.status},

                        // {field: 'order_status_text', title: __('Order_status'), operate: false},
                        // {field: 'pay_status_text', title: __('Pay_status'), operate: false},
                        {field: 'pay_amount', title: __('Pay_amount'),  operate: false},
                        {field: 'recycle_amount', title: __('Recycle_amount'),  operate: false},
                        {field: 'total_quantity', title: __('Total_quantity'), operate: false},
                        {field: 'memo', title: __('Memo'),table: table, class: 'autocontent', formatter: Table.api.formatter.content, operate: false},
                        {field: 'recycletime', title: __('Recycletime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'audittime', title: __('Audittime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'restoretime', title: __('Restoretime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'approve',
                                    text: __('Approve recycle'),
                                    title: __('Approve recycle'),
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    icon: 'fa fa-check',
                                    url: 'shop.order.recycle/approve',
                                    confirm: __('Are you sure you want to approve this recycle order?'),
                                    refresh: true,
                                    visible: function (row) {
                                        return row.status === 'pending';
                                    }
                                },
                                {
                                    name: 'reject',
                                    text: __('Reject recycle'),
                                    title: __('Reject recycle'),
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    icon: 'fa fa-times',
                                    url: 'shop.order.recycle/reject',
                                    confirm: __('Are you sure you want to reject this recycle order?'),
                                    refresh: true,
                                    visible: function (row) {
                                        return row.status === 'pending';
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
                url: 'shop.order.recycle/recyclebin' + location.search,
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
                                    url: 'shop.order.recycle/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'shop.order.recycle/destroy',
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
