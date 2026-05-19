define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/refund/index' + location.search,
                    add_url: 'shop/refund/add',
                    edit_url: 'shop/refund/edit',
                    del_url: 'shop/refund/del',
                    multi_url: 'shop/refund/multi',
                    import_url: 'shop/refund/import',
                    table: 'shop_refund',
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
                        {field: 'refund_no', title: __('Refund_no'), operate: 'LIKE'},
                        {field: 'order_no', title: __('Order_no'), operate: 'LIKE'},
                        {
                            field: 'user.nickname',
                            title: __('User_name'),
                            operate: false,
                            formatter: function (value, row) {
                                return (row.user && (row.user.nickname || row.user.username)) || row.user_id || '-';
                            }
                        },
                        {field: 'product_id', title: __('Product_id')},
                        {field: 'type', title: __('Type'), searchList: {"refund":__('Type refund'),"return_refund":__('Type return_refund')}, formatter: Table.api.formatter.normal},
                        {field: 'reason', title: __('Reason'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'apply_money', title: __('Apply_money'), operate:'BETWEEN'},
                        {field: 'refund_money', title: __('Refund_money'), operate:'BETWEEN'},
                        {field: 'quantity', title: __('Quantity')},
                        {field: 'status', title: __('Status'), searchList: {"pending":__('Status pending'),"approved":__('Status approved'),"rejected":__('Status rejected'),"returned":__('Status returned'),"refunded":__('Status refunded'),"cancelled":__('Status cancelled')}, formatter: Table.api.formatter.status},
                        {field: 'audit_remark', title: __('Audit_remark'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'refund_remark', title: __('Refund_remark'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'applytime', title: __('Applytime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'audittime', title: __('Audittime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'returntime', title: __('Returntime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'refundtime', title: __('Refundtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
                                    text: __('Approve refund'),
                                    title: __('Approve refund'),
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    icon: 'fa fa-check',
                                    url: 'shop/refund/approve',
                                    confirm: __('Are you sure you want to approve and refund this request to balance?'),
                                    refresh: true,
                                    visible: function (row) {
                                        return row.status === 'pending';
                                    }
                                },
                                {
                                    name: 'reject',
                                    text: __('Reject refund'),
                                    title: __('Reject refund'),
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    icon: 'fa fa-times',
                                    url: 'shop/refund/reject',
                                    confirm: __('Are you sure you want to reject this refund request?'),
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
                url: 'shop/refund/recyclebin' + location.search,
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
                                    url: 'shop/refund/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'shop/refund/destroy',
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
