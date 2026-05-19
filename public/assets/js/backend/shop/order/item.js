define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop.order.item/index' + location.search,
                    add_url: 'shop.order.item/add',
                    edit_url: 'shop.order.item/edit',
                    del_url: 'shop.order.item/del',
                    multi_url: 'shop.order.item/multi',
                    import_url: 'shop.order.item/import',
                    table: 'shop_order_item',
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
                        {field: 'product_id', title: __('Product_id')},
                        {field: 'sku_id', title: __('Sku_id')},
                        {field: 'product_sn', title: __('Product_sn'), operate: 'LIKE'},
                        {field: 'sku_code', title: __('Sku_code'), operate: 'LIKE'},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'sku_spec', title: __('Sku_spec'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'quantity', title: __('Quantity')},
                        {field: 'total_price', title: __('Total_price'), operate:'BETWEEN'},
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
