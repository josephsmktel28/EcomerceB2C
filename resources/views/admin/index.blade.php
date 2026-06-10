@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">

        <div class="main-content-wrap">
            <div class="tf-section-2 mb-30">
                <div class="flex gap20 flex-wrap-mobile">
                    <div class="w-half">

                        <div class="wg-chart-default mb-20">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap14">
                                    <div class="image ic-bg">
                                        <i class="icon-shopping-bag"></i>
                                    </div>
                                    <div>
                                        <div class="body-text mb-2">Total Pesanan</div>
                                        <h4>{{ $dashboardDatas[0]->Total }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{-- <div class="wg-chart-default mb-20">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap14">
                                    <div class="image ic-bg">
                                        <i class="icon-dollar-sign"></i>
                                    </div>
                                    <div>
                                        <div class="body-text mb-2">Total Amount</div>
                                        <h4>Rp {{ $dashboardDatas[0]->TotalAmount }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div> --}}


                        <div class="wg-chart-default mb-20">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap14">
                                    <div class="image ic-bg">
                                        <i class="icon-shopping-bag"></i>
                                    </div>
                                    <div>
                                        <div class="body-text mb-2">Pesanan Tertunda</div>
                                        <h4>{{ $dashboardDatas[0]->TotalOrdered }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{-- <div class="wg-chart-default">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap14">
                                    <div class="image ic-bg">
                                        <i class="icon-dollar-sign"></i>
                                    </div>
                                    <div>
                                        <div class="body-text mb-2">Jumlah Pesanan Tertunda</div>
                                        <h4>Rp {{ $dashboardDatas[0]->TotalOrderedAmount }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                    </div>

                    <div class="w-half">

                        <div class="wg-chart-default mb-20">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap14">
                                    <div class="image ic-bg">
                                        <i class="icon-shopping-bag"></i>
                                    </div>
                                    <div>
                                        <div class="body-text mb-2">Pesanan yang Dikirim</div>
                                        <h4>{{ $dashboardDatas[0]->TotalDelivered }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{-- <div class="wg-chart-default mb-20">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap14">
                                    <div class="image ic-bg">
                                        <i class="icon-dollar-sign"></i>
                                    </div>
                                    <div>
                                        <div class="body-text mb-2">Jumlah Pesanan yang Dikirim</div>
                                        <h4>Rp {{ $dashboardDatas[0]->TotalDeliveredAmount }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div> --}}


                        <div class="wg-chart-default mb-20">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap14">
                                    <div class="image ic-bg">
                                        <i class="icon-shopping-bag"></i>
                                    </div>
                                    <div>
                                        <div class="body-text mb-2">Pesanan yang Dibatalkan</div>
                                        <h4>{{ $dashboardDatas[0]->TotalCanceled }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{-- <div class="wg-chart-default">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap14">
                                    <div class="image ic-bg">
                                        <i class="icon-dollar-sign"></i>
                                    </div>
                                    <div>
                                        <div class="body-text mb-2">Jumlah Pesanan yang Dibatalkan</div>
                                        <h4>Rp {{ $dashboardDatas[0]->TotalCanceledAmount }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                    </div>

                </div>

                <div class="wg-box">
                    <div class="flex items-center justify-between">
                        <h5>Total Revenue</h5>
                    </div>
                    <div class="flex flex-wrap gap40">
                        <div>
                            <div class="mb-2">
                                <div class="block-legend">
                                    <div class="dot t1"></div>
                                    <div class="text-tiny">Total Order</div>
                                </div>
                            </div>
                            <div class="flex items-center gap10">
                                <h4>Rp {{ number_format($dashboardDatas[0]->TotalAmount, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                        <div>
                            <div class="mb-2">
                                <div class="block-legend">
                                    <div class="dot t2"></div>
                                    <div class="text-tiny">Order Pending</div>
                                </div>
                            </div>
                            <div class="flex items-center gap10">
                                <h4>Rp {{ number_format($dashboardDatas[0]->TotalOrderedAmount, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                        <div>
                            <div class="mb-2">
                                <div class="block-legend">
                                    <div class="dot t2"></div>
                                    <div class="text-tiny">Order Dikirim</div>
                                </div>
                            </div>
                            <div class="flex items-center gap10">
                                <h4>Rp {{ number_format($dashboardDatas[0]->TotalDeliveredAmount, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                        <div>
                            <div class="mb-2">
                                <div class="block-legend">
                                    <div class="dot t2"></div>
                                    <div class="text-tiny">Order Dibatalkan</div>
                                </div>
                            </div>
                            <div class="flex items-center gap10">
                                <h4>Rp {{ number_format($dashboardDatas[0]->TotalCanceledAmount, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                    </div>
                    <div id="line-chart-8"></div>
                </div>

            </div>
            <div class="tf-section mb-30">

                <div class="wg-box">
                    <div class="flex items-center justify-between">
                        <h5>Recent orders</h5>
                        <div class="dropdown default">
                            <a class="btn btn-secondary dropdown-toggle" href="{{ route('admin.orders') }}">
                                <span class="view-all">View all</span>
                            </a>
                        </div>
                    </div>
                    <div class="wg-table table-all-user">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width:70px">OrderNo</th>
                                        <th class="text-center">Name</th>
                                        <th class="text-center">Phone</th>
                                        <th class="text-center">Subtotal</th>
                                        <th class="text-center">Tax</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Order Date</th>
                                        <th class="text-center">Total Items</th>
                                        <th class="text-center">Delivered On</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                        <tr>
                                            <td class="text-center">{{ $order->id }}</td>
                                            <td class="text-center">{{ $order->name }}</td>
                                            <td class="text-center">{{ $order->phone }}</td>
                                            <td class="text-center">Rp{{ $order->subtotal }}</td>
                                            <td class="text-center">Rp{{ $order->tax }}</td>
                                            <td class="text-center">Rp{{ $order->total }}</td>
                                            <td class="text-center">{{ $order->status }}</td>
                                            <td class="text-center">{{ $order->created_at }}</td>
                                            <td class="text-center">{{ $order->orderItems->count() }}</td>
                                            <td class="text-center">{{ $order->delivered_date }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.order.details', $order->id) }}">
                                                    <div class="list-icon-function view-icon">
                                                        <div class="item eye">
                                                            <i class="icon-eye"></i>
                                                        </div>
                                                    </div>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function($) {
            var tfLineChart = (function() {
                var chartBar = function() {
                    var options = {
                        series: [{
                            name: 'Total Order',
                            data: [{{ $dashboardDatas[0]->TotalAmount }}]
                        }, {
                            name: 'Order Pending',
                            data: [{{ $dashboardDatas[0]->TotalOrderedAmount }}]
                        }, {
                            name: 'Order Delivered',
                            data: [{{ $dashboardDatas[0]->TotalDeliveredAmount }}]
                        }, {
                            name: 'Order Canceled',
                            data: [{{ $dashboardDatas[0]->TotalCanceledAmount }}]
                        }],
                        chart: {
                            type: 'bar',
                            height: 400,
                            toolbar: {
                                show: true,
                                tools: {
                                    download: true,
                                    selection: false,
                                    zoom: false,
                                    zoomin: false,
                                    zoomout: false,
                                    pan: false,
                                    reset: false
                                },
                                export: {
                                    csv: {
                                        filename: 'ringkasan-pesanan',
                                        headerCategory: 'Kategori',
                                        headerValue: 'Nilai'
                                    },
                                    svg: {
                                        filename: 'chart-ringkasan-pesanan',
                                    },
                                    png: {
                                        filename: 'chart-ringkasan-pesanan',
                                    }
                                },
                            },
                            dropShadow: {
                                enabled: true,
                                top: 5,
                                left: 0,
                                blur: 4,
                                opacity: 0.2
                            },
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 800,
                                animateGradually: {
                                    enabled: true,
                                    delay: 150
                                },
                                dynamicAnimation: {
                                    enabled: true,
                                    speed: 350
                                }
                            }
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '55%',
                                endingShape: 'rounded',
                                borderRadius: 4,
                                dataLabels: {
                                    position: 'top'
                                }
                            },
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function(val) {
                                return "Rp " + val.toLocaleString('id-ID');
                            },
                            offsetY: -20,
                            style: {
                                fontSize: '12px',
                                colors: ["#304758"]
                            }
                        },
                        legend: {
                            show: true,
                            position: 'top',
                            horizontalAlign: 'center',
                            fontSize: '14px',
                            markers: {
                                width: 12,
                                height: 12,
                                radius: 6
                            },
                            itemMargin: {
                                horizontal: 10,
                                vertical: 0
                            }
                        },
                        colors: ['#4361ee', '#ff9800', '#2dc653', '#e63946'],
                        stroke: {
                            show: true,
                            width: 2,
                            colors: ['transparent']
                        },
                        xaxis: {
                            labels: {
                                style: {
                                    colors: '#212529',
                                    fontSize: '14px',
                                    fontWeight: 600
                                },
                                rotate: 0
                            },
                            categories: ['Ringkasan Pesanan'],
                            axisBorder: {
                                show: true,
                                color: '#e0e0e0'
                            },
                            axisTicks: {
                                show: true,
                                color: '#e0e0e0'
                            }
                        },
                        yaxis: {
                            show: true,
                            labels: {
                                show: true,
                                style: {
                                    colors: '#212529',
                                    fontSize: '12px'
                                },
                                formatter: function(val) {
                                    return "Rp " + val.toLocaleString('id-ID');
                                }
                            },
                            axisBorder: {
                                show: true
                            },
                            title: {
                                text: 'Jumlah (Rupiah)',
                                style: {
                                    fontSize: '14px',
                                    fontWeight: 500
                                }
                            }
                        },
                        fill: {
                            opacity: 1,
                            type: 'gradient',
                            gradient: {
                                shade: 'light',
                                type: "vertical",
                                shadeIntensity: 0.2,
                                gradientToColors: undefined,
                                inverseColors: false,
                                opacityFrom: 0.85,
                                opacityTo: 0.95,
                                stops: [0, 100]
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: function(val) {
                                    return "Rp " + val.toLocaleString('id-ID');
                                }
                            },
                            theme: 'light',
                            marker: {
                                show: true
                            },
                            x: {
                                show: false
                            }
                        },
                        grid: {
                            show: true,
                            borderColor: '#e0e0e0',
                            strokeDashArray: 5,
                            position: 'back',
                            xaxis: {
                                lines: {
                                    show: false
                                }
                            },
                            yaxis: {
                                lines: {
                                    show: true
                                }
                            },
                            padding: {
                                top: 0,
                                right: 0,
                                bottom: 0,
                                left: 10
                            }
                        },
                        responsive: [{
                            breakpoint: 768,
                            options: {
                                chart: {
                                    height: 350
                                },
                                plotOptions: {
                                    bar: {
                                        columnWidth: '70%'
                                    }
                                },
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }]
                    };

                    var chart = new ApexCharts(
                        document.querySelector("#line-chart-8"),
                        options
                    );
                    if ($("#line-chart-8").length > 0) {
                        chart.render();
                    }
                };

                /* Function ============ */
                return {
                    init: function() {},
                    load: function() {
                        chartBar();
                    },
                    resize: function() {
                        if ($("#line-chart-8").length > 0) {
                            setTimeout(function() {
                                chart.render();
                            }, 200);
                        }
                    }
                };
            })();

            jQuery(document).ready(function() {});

            jQuery(window).on("load", function() {
                tfLineChart.load();
            });

            jQuery(window).on("resize", function() {
                tfLineChart.resize();
            });
        })(jQuery);
    </script>
@endpush
