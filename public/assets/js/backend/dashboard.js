define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    var Controller = {
        index: function () {
            var chartEl = document.getElementById('echart');
            if (!chartEl) {
                return;
            }
            var myChart = Echarts.init(chartEl, 'walden');
            var option = {
                color: ["#3c8dbc", "#18bc9c"],
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'line'
                    }
                },
                legend: {
                    data: ['订单数', '成交金额']
                },
                toolbox: {
                    show: false
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: Config.column || []
                },
                yAxis: [
                    {
                        type: 'value',
                        name: '订单数',
                        minInterval: 1
                    },
                    {
                        type: 'value',
                        name: '金额'
                    }
                ],
                grid: [{
                    left: 45,
                    top: 45,
                    right: 55,
                    bottom: 35
                }],
                series: [
                    {
                        name: '订单数',
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                                opacity: .12
                            }
                        },
                        lineStyle: {
                            normal: {
                                width: 2
                            }
                        },
                        data: Config.orderdata || []
                    },
                    {
                        name: '成交金额',
                        type: 'bar',
                        yAxisIndex: 1,
                        barWidth: 18,
                        data: Config.revenuedata || []
                    }
                ]
            };

            myChart.setOption(option);

            $(window).resize(function () {
                myChart.resize();
            });

            $(document).on("click", ".btn-refresh", function () {
                setTimeout(function () {
                    myChart.resize();
                }, 0);
            });
        }
    };

    return Controller;
});
