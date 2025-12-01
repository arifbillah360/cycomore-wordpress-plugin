/**
 * Chart.js Integration for Public Dashboard
 */

(function($) {
    'use strict';

    var charts = {};

    $(document).ready(function() {
        initializeCharts();
    });

    /**
     * Initialize all charts
     */
    function initializeCharts() {
        // Status Pie Chart
        initStatusPieChart();

        // Top Partners Chart
        initTopPartnersChart();

        // Collections Chart
        initCollectionsChart();

        // Timeline Chart
        initTimelineChart();
    }

    /**
     * Initialize Status Pie Chart
     */
    function initStatusPieChart() {
        var canvas = document.getElementById('status-pie-chart');
        if (!canvas) return;

        $.ajax({
            url: partnerCiuPublicDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_chart_data',
                nonce: partnerCiuPublicDashboard.nonce,
                chart_type: 'status'
            },
            success: function(response) {
                if (response.success) {
                    var ctx = canvas.getContext('2d');
                    charts.statusPie = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: response.data.labels,
                            datasets: [{
                                data: response.data.values,
                                backgroundColor: response.data.colors,
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 15,
                                        font: {
                                            size: 14
                                        }
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'CIU Status Distribution',
                                    font: {
                                        size: 18,
                                        weight: 'bold'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            var percentage = Math.round((context.parsed / total) * 100);
                                            return context.label + ': ' + context.parsed.toLocaleString() + ' CIUs (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        });
    }

    /**
     * Initialize Top Partners Chart
     */
    function initTopPartnersChart() {
        var canvas = document.getElementById('top-partners-chart');
        if (!canvas) return;

        $.ajax({
            url: partnerCiuPublicDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_chart_data',
                nonce: partnerCiuPublicDashboard.nonce,
                chart_type: 'top_partners'
            },
            success: function(response) {
                if (response.success) {
                    var ctx = canvas.getContext('2d');
                    charts.topPartners = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: response.data.labels,
                            datasets: [{
                                label: 'Total CIUs',
                                data: response.data.values,
                                backgroundColor: '#2196F3',
                                borderColor: '#1976D2',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            plugins: {
                                legend: {
                                    display: false
                                },
                                title: {
                                    display: true,
                                    text: 'Top 10 Contributing Partners',
                                    font: {
                                        size: 18,
                                        weight: 'bold'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.parsed.x.toLocaleString() + ' CIUs';
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return value.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        });
    }

    /**
     * Initialize Collections Chart
     */
    function initCollectionsChart() {
        var canvas = document.getElementById('collections-chart');
        if (!canvas) return;

        $.ajax({
            url: partnerCiuPublicDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_chart_data',
                nonce: partnerCiuPublicDashboard.nonce,
                chart_type: 'collections'
            },
            success: function(response) {
                if (response.success) {
                    var colors = ['#2196F3', '#4CAF50', '#FFA500', '#9C27B0', '#F44336',
                                  '#00BCD4', '#FFEB3B', '#795548', '#607D8B', '#E91E63'];

                    var ctx = canvas.getContext('2d');
                    charts.collections = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: response.data.labels,
                            datasets: [{
                                data: response.data.values,
                                backgroundColor: colors,
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 15,
                                        font: {
                                            size: 14
                                        }
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Collection-wise CIU Allocation',
                                    font: {
                                        size: 18,
                                        weight: 'bold'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            var percentage = Math.round((context.parsed / total) * 100);
                                            return context.label + ': ' + context.parsed.toLocaleString() + ' CIUs (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        });
    }

    /**
     * Initialize Timeline Chart
     */
    function initTimelineChart() {
        var canvas = document.getElementById('timeline-chart');
        if (!canvas) return;

        $.ajax({
            url: partnerCiuPublicDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_chart_data',
                nonce: partnerCiuPublicDashboard.nonce,
                chart_type: 'timeline'
            },
            success: function(response) {
                if (response.success) {
                    var ctx = canvas.getContext('2d');
                    charts.timeline = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: response.data.labels,
                            datasets: [{
                                label: 'CIUs Purchased',
                                data: response.data.values,
                                borderColor: '#2196F3',
                                backgroundColor: 'rgba(33, 150, 243, 0.1)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5,
                                pointHoverRadius: 7
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                },
                                title: {
                                    display: true,
                                    text: 'CIU Purchase Growth',
                                    font: {
                                        size: 18,
                                        weight: 'bold'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.parsed.y.toLocaleString() + ' CIUs';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return value.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        });
    }

})(jQuery);
