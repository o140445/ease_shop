define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/recharge/index' + location.search,
                    add_url: 'shop/recharge/add',
                    edit_url: 'shop/recharge/edit',
                    del_url: 'shop/recharge/del',
                    multi_url: 'shop/recharge/multi',
                    import_url: 'shop/recharge/import',
                    table: 'shop_recharge',
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
                        {field: 'recharge_no', title: __('Recharge_no'), operate: 'LIKE'},
                        {
                            field: 'user.nickname',
                            title: __('User_name'),
                            operate: false,
                            formatter: function (value, row) {
                                return (row.user && (row.user.nickname || row.user.username)) || row.user_id || '-';
                            }
                        },
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'give_money', title: __('Give_money'), operate:'BETWEEN'},
                        {field: 'pay_money', title: __('Pay_money'), operate:'BETWEEN'},
                        {field: 'pay_type', title: __('Pay_type'), searchList: {"offline":__('Pay_type offline'),"admin":__('Pay_type admin')}, formatter: Table.api.formatter.normal},
                        {field: 'pay_status', title: __('Pay_status'), searchList: {"unpaid":__('Pay_status unpaid'),"paid":__('Pay_status paid'),"cancelled":__('Pay_status cancelled')}, formatter: Table.api.formatter.status},
                        {field: 'voucher', title: __('Voucher'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'remark', title: __('Remark'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'admin_id', title: __('Admin_id')},
                        {field: 'admin_remark', title: __('Admin_remark'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'paidtime', title: __('Paidtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
                                    text: __('Approve'),
                                    title: __('Approve'),
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    icon: 'fa fa-check',
                                    url: 'shop/recharge/approve',
                                    confirm: __('Are you sure you want to approve this recharge request?'),
                                    refresh: true,
                                    visible: function (row) {
                                        return row.pay_status === 'unpaid';
                                    }
                                },
                                {
                                    name: 'reject',
                                    text: __('Reject'),
                                    title: __('Reject'),
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    icon: 'fa fa-times',
                                    url: 'shop/recharge/reject',
                                    confirm: __('Are you sure you want to reject this recharge request?'),
                                    refresh: true,
                                    visible: function (row) {
                                        return row.pay_status === 'unpaid';
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
                url: 'shop/recharge/recyclebin' + location.search,
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
                                    url: 'shop/recharge/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'shop/recharge/destroy',
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
