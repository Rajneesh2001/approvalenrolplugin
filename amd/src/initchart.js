require.config({
    paths: {
        'highcharts/highcharts': ['https://code.highcharts.com/12.2.0/highcharts'
            , M.cfg.wwwroot + '/enrol/approvalenrol/vendor/highcharts/highcharts'
        ],
        'highcharts/modules/exporting': ['https://code.highcharts.com/12.2.0/modules/exporting'
            , M.cfg.wwwroot + '/enrol/approvalenrol/vendor/highcharts/exporting'
        ],
        'highcharts/modules/export-data': ['https://code.highcharts.com/12.2.0/modules/export-data'
            , M.cfg.wwwroot + '/enrol/approvalenrol/vendor/highcharts/export-data'
        ]
    }
});

define([
    'jquery',
    'highcharts/highcharts',
    'highcharts/modules/exporting',
    'highcharts/modules/export-data'
], function ($, Highcharts) {
    return {
        init: function (data) {
            $(document).ready(function () {
                Highcharts.chart('container', {
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {
                        text: ''
                    },
                    tooltip: {
                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                    },
                    accessibility: {
                        point: {
                            valueSuffix: '%'
                        }
                    },
                    exporting: {
                        filename: 'Approval Request Status',
                        enabled: true,
                        buttons: {
                            contextButton: {
                                menuItems: ['viewFullscreen']
                            }
                        }
                    },
                    credits: {
                        enabled: false
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<span style="font-size: 1.2em"><b>{point.name}</b>' +
                                    '</span><br>' +
                                    '<span style="opacity: 0.6">{point.percentage:.1f} ' +
                                    '%</span>',
                                connectorColor: 'rgba(128,128,128,0.5)'
                            }
                        }
                    },
                    series: [{
                        name: 'Counts',
                        data: data
                    }]
                });
            });
        }
    };
});