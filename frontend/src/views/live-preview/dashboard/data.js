const total_line_1_chart_options = {
    series: [
        {
            name: 'series1',
            data: [20, 10, 18, 12, 25, 10, 20]
        }
    ],
    chartOptions: {
        chart: {
            type: 'line',
            height: 60,
            sparkline: {
                enabled: true
            }
        },
        dataLabels: {
            enabled: false
        },
        colors: ['#1de9b6'],
        stroke: {
            curve: 'straight',
            lineCap: 'round',
            width: 3
        },
        yaxis: {
            min: 0,
            max: 30
        },
        tooltip: {
            theme: 'dark',
            fixed: {
                enabled: false
            },
            x: {
                show: false
            },
            y: {
                title: {
                    formatter: function () {
                        return '';
                    }
                }
            },
            marker: {
                show: false
            }
        }
    }
};

const total_line_2_chart_options = {

    series: [
        {
            name: 'series1',
            data: [20, 10, 18, 12, 25, 10, 20]
        }
    ],
    chartOptions: {
        chart: {
            type: 'line',
            height: 60,
            sparkline: {
                enabled: true
            }
        },
        dataLabels: {
            enabled: false
        },
        colors: ['#1de9b6'],
        stroke: {
            curve: 'straight',
            lineCap: 'round',
            width: 3
        },
        yaxis: {
            min: 0,
            max: 30
        },
        tooltip: {
            theme: 'dark',
            fixed: {
                enabled: false
            },
            x: {
                show: false
            },
            y: {
                title: {
                    formatter: function () {
                        return '';
                    }
                }
            },
            marker: {
                show: false
            }
        }
    }
};
const total_line_3_chart_options = {

    series: [
        {
            name: 'series1',
            data: [20, 10, 18, 12, 25, 10, 20]
        }
    ],
    chartOptions: {
        chart: {
            type: 'line',
            height: 60,
            sparkline: {
                enabled: true
            }
        },
        dataLabels: {
            enabled: false
        },
        colors: ['#FF3333'],
        stroke: {
            curve: 'straight',
            lineCap: 'round',
            width: 3
        },
        yaxis: {
            min: 0,
            max: 30
        },
        tooltip: {
            theme: 'dark',
            fixed: {
                enabled: false
            },
            x: {
                show: false
            },
            y: {
                title: {
                    formatter: function () {
                        return '';
                    }
                }
            },
            marker: {
                show: false
            }
        }
    }
};
const cashflow_bar_chart_options = {
    series: [
        {
            name: 'Income',
            data: [180, 90, 135, 114, 120, 145, 180, 90, 135, 114, 120, 145]
        },
        {
            name: 'Expends',
            data: [120, 45, 78, 150, 168, 99, 120, 45, 78, 150, 168, 99]
        }
    ],
    chartOptions: {
        chart: {
            type: 'bar',
            height: 210,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                columnWidth: '70%',
                borderRadius: 2
            }
        },
        fill: {
            opacity: [1, 0.4]
        },
        stroke: {
            show: true,
            width: 3,
            colors: ['transparent']
        },
        dataLabels: {
            enabled: false
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            show: true,
            fontFamily: `'Public Sans', sans-serif`,
            offsetX: 10,
            offsetY: 10,
            labels: {
                useSeriesColors: false
            },
            markers: {
                width: 10,
                height: 10,
                radius: '50%',
                offsetX: 2,
                offsetY: 2
            },
            itemMargin: {
                horizontal: 15,
                vertical: 5
            }
        },
        colors: ['#04a9f5', '#04a9f5'],
        grid: {
            borderColor: '#00000010'
        },
        yaxis: {
            show: false
        }
    }
};
const donut_chart_options = {
    series: [25, 50, 25],
    chartOptions: {
        chart: {
            height: 268,
            type: 'donut'
        },
        dataLabels: {
            enabled: false
        },
        legend: {
            show: true,
            position: 'bottom'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%'
                }
            }
        },
        labels: ['Saving', 'Spend', 'Income'],
        colors: ['#212529', '#04a9f5', '#caedfd']
    }
}
const support_chart = {
    series: [{
        name: 'series1',
        data: [0, 20, 10, 45, 30, 55, 20, 30, 0]
    }],
    chartOptions: {
        chart: {
            type: 'area',
            height: 100,
            sparkline: {
                enabled: true
            }
        },
        colors: ["#3ebfea"],
        stroke: {
            curve: 'smooth',
            width: 2
        },
        tooltip: {
            fixed: {
                enabled: false
            },
            x: {
                show: false
            },
            y: {
                title: {
                    formatter: function () {
                        return ''
                    }
                }
            },
            marker: {
                show: false
            }
        }
    }
}
const support_chart1 = {
    series: [{
        name: 'series1',
        data: [0, 20, 10, 45, 30, 55, 20, 30, 0]
    }],
    chartOptions: {
        chart: {
            type: 'area',
            height: 100,
            sparkline: {
                enabled: true
            }
        },
        colors: ["#04a9f5"],
        stroke: {
            curve: 'smooth',
            width: 2
        },
        tooltip: {
            fixed: {
                enabled: false
            },
            x: {
                show: false
            },
            y: {
                title: {
                    formatter: function () {
                        return ''
                    }
                }
            },
            marker: {
                show: false
            }
        }
    }
}
const support_chart2 = {
    series: [{
        name: 'series1',
        data: [0, 20, 10, 45, 30, 55, 20, 30, 0]
    }],
    chartOptions: {
        chart: {
            type: 'area',
            height: 100,
            sparkline: {
                enabled: true
            }
        },
        colors: ["#1de9b6"],
        stroke: {
            curve: 'smooth',
            width: 2
        },
        tooltip: {
            fixed: {
                enabled: false
            },
            x: {
                show: false
            },
            y: {
                title: {
                    formatter: function () {
                        return ''
                    }
                }
            },
            marker: {
                show: false
            }
        }
    }
}

const setisfaction = {
    series: [66, 50, 40, 30],
    chartOptions: {
        chart: {
            height: 260,
            type: 'pie',
        },
        labels: ["Very Poor", "Satisfied", "Very Satisfied", "Poor"],
        legend: {
            show: true,
            offsetY: 50,
        },
        theme: {
            monochrome: {
                enabled: true,
                color: '#04a9f5',
            }
        },
        responsive: [{
            breakpoint: 768,
            options: {
                chart: {
                    height: 320,
                },
                legend: {
                    position: 'bottom',
                    offsetY: 0,
                }
            }
        }]
    }
}

const invoice_chart = {
    chartOptions: {
        chart: {
            height: 300,
            type: 'line',
            toolbar: {
                show: false,
            }
        },
        plotOptions: {
            bar: {
                columnWidth: '50%',
            }
        },
        legend: {
            show: false
        },
        stroke: {
            width: [0, 2],
            curve: 'smooth'
        },
        dataLabels: {
            enabled: false
        },
        fill: {
            type: 'gradient',
            gradient: {
                inverseColors: false,
                shade: 'light',
                type: 'vertical',
                opacityFrom: [0, 1],
                opacityTo: [0.5, 1],
                stops: [0, 100],
                hover: {
                    inverseColors: false,
                    shade: 'light',
                    type: 'vertical',
                    opacityFrom: 0.15,
                    opacityTo: 0.65,
                    stops: [0, 96, 100]
                }
            }
        },
        markers: {
            size: 3,
            colors: '#fff',
            strokeColors: '#f4c22b',
            strokeWidth: 2,
            shape: "circle",
            hover: {
                size: 5,
            }
        },
        colors: ['#f4c22b', '#f4c22b'],
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        yaxis: {
            tickAmount: 3,
        },
        grid: {
            show: true,
            borderColor: '#00000010',
        },
        xaxis: {
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
            tickAmount: 11
        },
    },
    series: [
        {
            name: 'TEAM A',
            type: 'column',
            data: [23, 11, 22, 27, 13, 22, 37, 21, 44, 22, 30, 25]
        },
        {
            name: 'TEAM B',
            type: 'line',
            data: [30, 25, 36, 30, 45, 35, 64, 52, 59, 36, 39, 35]
        }
    ],
}
const total_income_graph_options = {
    chartOptions: {
        chart: {
            height: 280,
            type: 'donut'
        },
        colors: ['#f4c22b', '#1de9b6', '#f44236', '#04a9f5'],
        labels: ['Pending', 'Paid', 'Overdue', 'Draft'],
        fill: {
            opacity: [1, 1, 1, 0.3]
        },
        legend: {
            show: false
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        name: {
                            show: true
                        },
                        value: {
                            show: true
                        }
                    }
                }
            }
        },
        dataLabels: {
            enabled: false
        },
        responsive: [
            {
                breakpoint: 575,
                options: {
                    chart: {
                        height: 250
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%',
                                labels: {
                                    show: false
                                }
                            }
                        }
                    }
                }
            }
        ]
    },
    series: [27, 23, 20, 17]
}



export {
    total_line_1_chart_options, total_line_2_chart_options, total_line_3_chart_options, cashflow_bar_chart_options,
    invoice_chart, donut_chart_options, support_chart, support_chart1, support_chart2, setisfaction, total_income_graph_options
}