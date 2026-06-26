// import getChartColorsArray from "@/components/getChartColorArray";


//  Line chart datalabel
const revenueAnalyticsChart = {
    series: [
        {
            name: 'Revenue',
            data: [200, 320, 320, 275, 275, 400, 400, 300, 440, 320, 320, 275, 275, 400, 300, 440]
        },
        {
            name: 'Sales',
            data: [200, 250, 240, 300, 340, 320, 320, 400, 350, 250, 240, 300, 340, 320, 400, 350]
        }
    ],
    chartOptions: {
        chart: {
            type: 'area',
            height: 300,
            toolbar: {
                show: false
            }
        },
        colors: ['#f4c22b', '#04a9f5'],
        dataLabels: {
            enabled: false
        },
        legend: {
            show: true,
            position: 'top'
        },
        markers: {
            size: 1,
            colors: ['#fff'],
            strokeColors: ['#f4c22b', '#04a9f5'],
            strokeWidth: 1,
            shape: 'circle',
            hover: {
                size: 4
            }
        },
        stroke: {
            width: 1,
            curve: 'smooth'
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                type: 'vertical',
                inverseColors: false,
                opacityFrom: 0.5,
                opacityTo: 0
            }
        },
        grid: {
            show: false
        },
        xaxis: {
            labels: {
                hideOverlappingLabels: true
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        }
    }
};
const membership_state_chart_option = {
    series: [76],
    chartOptions: {
        chart: {
            type: 'radialBar',
            offsetY: -20,
            sparkline: {
                enabled: true
            }
        },
        colors: ['#04a9f5'],
        plotOptions: {
            radialBar: {
                startAngle: -95,
                endAngle: 95,
                hollow: {
                    margin: 15,
                    size: '40%',
                },
                track: {
                    background: '#04a9f525',
                    strokeWidth: '97%',
                    margin: 10
                },
                dataLabels: {
                    name: {
                        show: false
                    },
                    value: {
                        offsetY: 0,
                        fontSize: '20px'
                    }
                }
            }
        },
        grid: {
            padding: {
                top: 10
            }
        },
        stroke: {
            lineCap: 'round'
        },
        labels: ['Average Results']
    }
};
const membership_state_chart_option2 = {
    series: [76],
    chartOptions: {
        chart: {
            type: 'radialBar',
            height: 250,
            offsetY: -20,
            sparkline: {
                enabled: true
            }
        },
        colors: ['#04a9f5'],
        plotOptions: {
            radialBar: {
                startAngle: -95,
                endAngle: 95,
                hollow: {
                    margin: 15,
                    size: '40%',
                },
                track: {
                    background: '#04a9f525',
                    strokeWidth: '97%',
                    margin: 10
                },
                dataLabels: {
                    name: {
                        show: false
                    },
                    value: {
                        offsetY: 0,
                        fontSize: '20px'
                    }
                }
            }
        },
        grid: {
            padding: {
                top: 10
            }
        },
        stroke: {
            lineCap: 'round'
        },
        labels: ['Average Results']
    }
};
const activity_line_chart_options = {
    series: [
        {
            name: 'Active',
            data: [20, 90, 65, 85, 20, 80, 30]
        },
        {
            name: 'Inactive',
            data: [70, 30, 40, 15, 60, 40, 95]
        }
    ],
    chartOptions: {
        chart: {
            type: 'line',
            height: 150,
            toolbar: {
                show: false
            }
        },
        colors: ['#1de9b6', '#1de9b6'],
        dataLabels: {
            enabled: false
        },
        legend: {
            show: true,
            position: 'top',
        },
        markers: {
            size: 1,
            colors: ['#fff', '#fff'],
            strokeColors: ['#1de9b6', '#1de9b6'],
            strokeWidth: 1,
            shape: 'circle',
            hover: {
                size: 4
            }
        },
        fill: {
            opacity: [1, 0.3]
        },
        stroke: {
            width: 3,
            curve: 'smooth',
        },
        grid: {
            show: false,
        },
        xaxis: {
            labels: {
                hideOverlappingLabels: true
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        }
    }
};
const course_report_bar_chart_options = {
    series: [
        {
            name: 'Net Profit',
            data: [180, 90, 135, 114, 120, 145, 180, 90, 135, 114, 120, 145]
        },
        {
            name: 'Revenue',
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
                columnWidth: '60%',
                borderRadius: 3
            }
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
        colors: ['#04a9f5', '#ffa21d'],
        grid: {
            borderColor: '#00000010',
        },
        yaxis: {
            show: false
        }
    }
};
const total_revenue_line_1_chart_options = {
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
const total_revenue_line_2_chart_options = {
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
        colors: ['#f44236'],
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
const student_states_chart_options = {
    series: [76.7, 30],
    chartOptions: {
        chart: {
            height: 250,
            type: 'donut'
        },
        dataLabels: {
            enabled: false
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%'
                }
            }
        },
        labels: ['Total Signups', 'Active Student'],
        legend: {
            show: true,
            position: 'bottom'
        },
        fill: {
            opacity: [1, 0.5]
        },
        colors: ['#04a9f5', '#04a9f5']
    }
};
const visitors_bar_chart_options = {
    series: [
        {
            data: [20, 15, 22, 25, 32, 50]
        }
    ],
    chartOptions: {
        chart: {
            type: 'bar',
            height: 220,
            toolbar: {
                show: false
            }
        },
        colors: ['#1de9b6'],
        dataLabels: {
            enabled: false
        },
        grid: {
            strokeDashArray: 4
        },
        yaxis: {
            tickAmount: 3
        },
        states: {
            normal: {
                filter: {
                    type: 'lighten',
                    value: 0.5
                }
            },
            hover: {
                filter: {
                    type: 'lighten',
                    value: 0
                }
            }
        },
        plotOptions: {
            bar: {
                borderRadius: 2,
                columnWidth: '50%'
            }
        },
        labels: ['2018', '2019', '2020', '2021', '2022', '2023']
    }
};
const earning_courses_line_chart_options = {
    series: [
        {
            name: 'Last Month',
            data: [200, 320, 275, 400, 300, 440]
        }
    ],
    chartOptions: {
        chart: {
            type: 'line',
            height: 230,
            toolbar: {
                show: false
            }
        },
        colors: ['#f4c22b', '#4680ff'],
        dataLabels: {
            enabled: false
        },
        markers: {
            size: 1,
            colors: ['#fff', '#fff', '#fff'],
            strokeColors: ['#f4c22b', '#4680ff'],
            strokeWidth: 1,
            shape: 'circle',
            hover: {
                size: 4
            }
        },
        stroke: {
            width: 3
        },
        grid: {
            strokeDashArray: 4
        },
        xaxis: {
            labels: {
                hideOverlappingLabels: true
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        }
    }
};
const total_invoice_1_chart_options = {
    series: [
        {
            data: [0, 20, 10, 45, 30, 55, 20, 30]
        }
    ],
    chartOptions: {
        chart: {
            type: 'area',
            height: 55,
            sparkline: {
                enabled: true
            }
        },
        colors: ["#1de9b6"],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                type: 'vertical',
                inverseColors: false,
                opacityFrom: 0.5,
                opacityTo: 0
            }
        },
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
                        return 'Ticket '
                    }
                }
            },
            marker: {
                show: false
            }
        }
    }
};
const total_invoice_2_chart_options = {
    series: [
        {
            data: [30, 20, 55, 30, 45, 10, 20, 0]
        }
    ],
    chartOptions: {
        chart: {
            type: 'area',
            height: 55,
            sparkline: {
                enabled: true
            }
        },
        colors: ["#f4c22b"],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                type: 'vertical',
                inverseColors: false,
                opacityFrom: 0.5,
                opacityTo: 0
            }
        },
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
                        return 'Ticket '
                    }
                }
            },
            marker: {
                show: false
            }
        }
    }
};
const total_invoice_3_chart_options = {
    series: [
        {
            data: [0, 20, 10, 45, 30, 55, 20, 30]
        }
    ],
    chartOptions: {
        chart: {
            type: 'area',
            height: 55,
            sparkline: {
                enabled: true
            }
        },
        colors: ["#f44236"],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                type: 'vertical',
                inverseColors: false,
                opacityFrom: 0.5,
                opacityTo: 0
            }
        },
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
                        return 'Ticket '
                    }
                }
            },
            marker: {
                show: false
            }
        }
    }
};

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


export {
    revenueAnalyticsChart, membership_state_chart_option, activity_line_chart_options, membership_state_chart_option2, course_report_bar_chart_options, total_revenue_line_1_chart_options, total_revenue_line_2_chart_options, student_states_chart_options, visitors_bar_chart_options, earning_courses_line_chart_options, total_invoice_1_chart_options, total_invoice_2_chart_options, total_invoice_3_chart_options,
    setisfaction,
    support_chart,
    support_chart1,
    support_chart2,
 };
