define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/user/index' + location.search,
                    add_url: 'shop/user/add',
                    edit_url: 'shop/user/edit',
                    del_url: 'shop/user/del',
                    multi_url: 'shop/user/multi',
                    import_url: 'shop/user/import',
                    table: 'shop_user',
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
                            field: 'level_id',
                            title: __('Level_name'),
                            operate: '=',
                            searchList: Config.levelList || {},
                            formatter: function (value, row) {
                                return row.level && row.level.name ? row.level.name : '';
                            }
                        },
                        {field: 'username', title: __('Username'), operate: 'LIKE'},
                        {field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        {field: 'money', title: __('Money'), operate:false},
                        {field: 'frozen_money', title: __('Frozen_money'), operate:false},
                        {
                            field: 'frozen_status',
                            title: __('Frozen_status'),
                            operate: false,
                            formatter: function (value, row) {
                                var frozenMoney = parseFloat(row.frozen_money || 0);
                                var label = frozenMoney > 0 ? __('Frozen_status frozen') : __('Frozen_status normal');
                                var type = frozenMoney > 0 ? 'warning' : 'success';
                                return '<span class="label label-' + type + '">' + label + '</span>';
                            }
                        },
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Status normal'),"hidden":__('Status hidden'),"locked":__('Status locked')}, formatter: Table.api.formatter.status},

                        {field: 'score', title: __('Score'), operate:false},
                        {field: 'total_order_amount', title: __('Total_order_amount'), operate:false},
                        {field: 'total_pay_amount', title: __('Total_pay_amount'), operate:false},
                        {field: 'total_recharge_amount', title: __('Total_recharge_amount'), operate:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'recharge',
                                    text: __('Recharge'),
                                    title: __('Recharge'),
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa fa-plus-circle',
                                    url: function (row) {
                                        return 'shop/recharge/add?user_id=' + row.id;
                                    }
                                },
                                {
                                    name: 'bankcards',
                                    text: __('Bank_cards'),
                                    title: __('Bank_cards'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-credit-card',
                                    url: function (row) {
                                        return 'shop.user.bank/index?user_id=' + row.id;
                                    }
                                },
                                {
                                    name: 'deduct',
                                    text: __('Deduct_balance'),
                                    title: __('Deduct_balance'),
                                    classname: 'btn btn-xs btn-warning btn-dialog',
                                    icon: 'fa fa-minus-circle',
                                    url: 'shop/user/deduct'
                                },
                                {
                                    name: 'freezemoney',
                                    text: __('Freeze_money'),
                                    title: __('Freeze_money'),
                                    classname: 'btn btn-xs btn-info btn-ajax',
                                    icon: 'fa fa-pause-circle',
                                    url: 'shop/user/freezemoney',
                                    confirm: __('Are you sure you want to freeze all available balance?'),
                                    refresh: true,
                                    visible: function (row) {
                                        return parseFloat(row.money || 0) > 0;
                                    }
                                },
                                {
                                    name: 'unfreezemoney',
                                    text: __('Unfreeze_money'),
                                    title: __('Unfreeze_money'),
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    icon: 'fa fa-play-circle',
                                    url: 'shop/user/unfreezemoney',
                                    confirm: __('Are you sure you want to unfreeze all frozen balance?'),
                                    refresh: true,
                                    visible: function (row) {
                                        return parseFloat(row.frozen_money || 0) > 0;
                                    }
                                },
                                {
                                    name: 'freeze',
                                    text: __('Freeze'),
                                    title: __('Freeze'),
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    icon: 'fa fa-lock',
                                    url: 'shop/user/freeze',
                                    confirm: __('Are you sure you want to freeze this user?'),
                                    refresh: true,
                                    visible: function (row) {
                                        return row.status === 'normal';
                                    }
                                },
                                {
                                    name: 'unfreeze',
                                    text: __('Unfreeze'),
                                    title: __('Unfreeze'),
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    icon: 'fa fa-unlock',
                                    url: 'shop/user/unfreeze',
                                    confirm: __('Are you sure you want to unfreeze this user?'),
                                    refresh: true,
                                    visible: function (row) {
                                        return row.status === 'locked';
                                    }
                                },
                                {
                                    name: 'loginuser',
                                    text: __('Login_as_user'),
                                    title: __('Login_as_user'),
                                    classname: 'btn btn-xs btn-primary btn-click',
                                    icon: 'fa fa-sign-in',
                                    visible: function (row) {
                                        return row.status === 'normal';
                                    },
                                    click: function (data, row) {
                                        window.open(Fast.api.fixurl('shop/user/loginuser/ids/' + row.id), '_blank');
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
                url: 'shop/user/recyclebin' + location.search,
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
                                    url: 'shop/user/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'shop/user/destroy',
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
