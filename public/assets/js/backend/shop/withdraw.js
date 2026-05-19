define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/withdraw/index' + location.search,
                    add_url: 'shop/withdraw/add',
                    edit_url: 'shop/withdraw/edit',
                    del_url: 'shop/withdraw/del',
                    multi_url: 'shop/withdraw/multi',
                    import_url: 'shop/withdraw/import',
                    table: 'shop_withdraw',
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
                        {field: 'withdraw_no', title: __('Withdraw_no'), operate: 'LIKE'},
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
                        {field: 'status', title: __('Status'), searchList: {"pending":__('Status pending'),"approved":__('Status approved'),"rejected":__('Status rejected'),"paid":__('Status paid'),"cancelled":__('Status cancelled')}, formatter: Table.api.formatter.status},

                        {field: 'realname', title: __('Realname'),  operate: false},
                        {field: 'card_no', title: __('Card_no'),  operate: false},
                        {field: 'bank_name', title: __('Bank_name'),  operate: false},
                        {field: 'bank_branch', title: __('Bank_branch'),  operate: false},
                        {field: 'money', title: __('Money'),  operate: false},
                        {field: 'fee', title: __('Fee'),  operate: false},
                        {field: 'actual_money', title: __('Actual_money'),  operate: false},
                        // {field: 'audit_admin_id', title: __('Audit_admin_id')},
                        {field: 'audit_remark', title: __('Audit_remark'),  operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'paid_voucher', title: __('Paid_voucher'),  operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'applytime', title: __('Applytime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'audittime', title: __('Audittime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
                                    url: 'shop/withdraw/approve',
                                    confirm: __('Are you sure you want to approve this withdraw request?'),
                                    refresh: true,
                                    visible: function (row) {
                                        return row.status === 'pending';
                                    }
                                },
                                {
                                    name: 'reject',
                                    text: __('Reject'),
                                    title: __('Reject'),
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    icon: 'fa fa-times',
                                    url: 'shop/withdraw/reject',
                                    confirm: __('Are you sure you want to reject this withdraw request?'),
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
                url: 'shop/withdraw/recyclebin' + location.search,
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
                                    url: 'shop/withdraw/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'shop/withdraw/destroy',
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
