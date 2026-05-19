define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/banner/index' + location.search,
                    add_url: 'shop/banner/add',
                    edit_url: 'shop/banner/edit',
                    del_url: 'shop/banner/del',
                    multi_url: 'shop/banner/multi',
                    import_url: 'shop/banner/import',
                    table: 'shop_banner',
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
                        {field: 'position', title: __('Position'), searchList: {"home":__('Position home')}, formatter: Table.api.formatter.normal},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'link_type', title: __('Link_type'), searchList: {"none":__('Link_type none'),"url":__('Link_type url'),"product":__('Link_type product'),"category":__('Link_type category'),"notice":__('Link_type notice')}, formatter: Table.api.formatter.normal},
                        {field: 'link_url', title: __('Link_url'), operate: 'LIKE', formatter: Table.api.formatter.url},
                        {field: 'link_id', title: __('Link_id')},
                        {field: 'target', title: __('Target'), searchList: {"self":__('Target self'),"blank":__('Target blank')}, formatter: Table.api.formatter.normal},
                        {field: 'weigh', title: __('Weigh'), operate: false},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Status normal'),"hidden":__('Status hidden')}, formatter: Table.api.formatter.status},
                        {field: 'starttime', title: __('Starttime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'endtime', title: __('Endtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
                url: 'shop/banner/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title'), align: 'left'},
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
                                    url: 'shop/banner/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'shop/banner/destroy',
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
                var toggleLinkFields = function () {
                    var type = $("#c-link_type").val();
                    var objectTypes = ['product', 'category', 'notice'];
                    $(".link-object-field").addClass("hide").find(":input").prop("disabled", true).removeAttr("data-rule");
                    $("#c-link_url").removeAttr("data-rule").closest(".form-group").addClass("hide");

                    if (type === 'url') {
                        $("#c-link_url").attr("data-rule", "required").closest(".form-group").removeClass("hide");
                    } else if (objectTypes.indexOf(type) !== -1) {
                        $("#link-" + type + "-field").removeClass("hide").find(":input").prop("disabled", false).attr("data-rule", "required");
                    }
                };
                $("#c-link_type").on("changed.bs.select change", toggleLinkFields);
                Form.api.bindevent($("form[role=form]"));
                toggleLinkFields();
            }
        }
    };
    return Controller;
});
