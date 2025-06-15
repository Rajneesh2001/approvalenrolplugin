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
                (function (H) {
                    H.seriesTypes.pie.prototype.animate = function (init) {
                        const series = this,
                            chart = series.chart,
                            points = series.points,
                            {
                                animation
                            } = series.options,
                            {
                                startAngleRad
                            } = series;

                        /**
                         * Animate the pie chart point to fan out from the center, creating a sequential animation
                         * effect starting from given angle. Recursively animates the next point on completion.
                         *
                         * @param {Highcharts.point} point
                         * @param {number} startAngleRad
                         */
                        function fanAnimate(point, startAngleRad) {
                            const graphic = point.graphic,
                                args = point.shapeArgs;

                            if (graphic && args) {

                                graphic
                                    // Set inital animation values
                                    .attr({
                                        start: startAngleRad,
                                        end: startAngleRad,
                                        opacity: 1
                                    })
                                    // Animate to the final position
                                    .animate({
                                        start: args.start,
                                        end: args.end
                                    }, {
                                        duration: animation.duration / points.length
                                    }, function () {
                                        // On complete, start animating the next point
                                        if (points[point.index + 1]) {
                                            fanAnimate(points[point.index + 1], args.end);
                                        }
                                        // On the last point, fade in the data labels, then
                                        // apply the inner size
                                        if (point.index === series.points.length - 1) {
                                            series.dataLabelsGroup.animate({
                                                opacity: 1
                                            },
                                                void 0,
                                                function () {
                                                    points.forEach(point => {
                                                        point.opacity = 1;
                                                    });
                                                    series.update({
                                                        enableMouseTracking: true
                                                    }, false);
                                                    chart.update({
                                                        plotOptions: {
                                                            pie: {
                                                                innerSize: '40%',
                                                                borderRadius: 8
                                                            }
                                                        }
                                                    });
                                                });
                                        }
                                    });
                            }
                        }

                        if (init) {
                            // Hide points on init
                            points.forEach(point => {
                                point.opacity = 0;
                            });
                        } else {
                            fanAnimate(points[0], startAngleRad);
                        }
                    };
                }(Highcharts));

                Highcharts.chart('container', {
                    chart: {
                        type: 'pie'
                    },
                    title: {
                        text: ''
                    },
                    tooltip: {
                        headerFormat: '',
                        pointFormat:
                            '<span style="color:{point.color}">\u25cf</span> ' +
                            '{point.name}: <b>{point.percentage:.2f}%</b>'
                    },
                    accessibility: {
                        point: {
                            valueSuffix: '%'
                        }
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            borderWidth: 2,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b><br>{point.percentage:.2f}%',
                                distance: 20
                            }
                        }
                    },
                    exporting: {
                        buttons: {
                            contextButton: {
                                menuItems: [
                                    'downloadCSV',
                                    'viewFullscreen'
                                ]
                            }
                        }
                    },
                    credits: {
                        enabled: false
                    },
                    series: [{
                        // Disable mouse tracking on load, enable after custom animation
                        enableMouseTracking: false,
                        name: 'Counts',
                        animation: {
                            duration: 2000
                        },
                        colorByPoint: true,
                        data: data
                    }]
                });
            });
        }
    };
});