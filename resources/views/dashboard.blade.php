@extends('master')

@section('title')
    AMPERE
@endsection

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-8">
                    <h3 class="mb-0">Dashboard</h3>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" name="mobileNumber" maxlength="10" id="mobileNumber"
                            placeholder="Type Mobile Number..." class="form-control">
                        <span class="input-group-append">
                            <button type="button" class="btn btn-primary" id="sendMessage">Send</button>
                        </span>

                    </div>
                    <div id="errorContainer" style="color: red; display: none;"></div>
                </div>
            </div>

            <div class="row mt-3"> <!--begin::Col-->
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Inquiry</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4"> <!--begin::Small Box Widget 1-->
                                    <a href="{{ route('inquiry', ['status' => '1']) }}">
                                        <div class="small-box text-bg-warning">
                                            <div class="inner">
                                                <h3>{{ $pendingInquiry }}</h3>
                                                <p>Pending</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 1-->
                                    </a>
                                </div> <!--end::Col-->
                                <div class="col-4"> <!--begin::Small Box Widget 2-->
                                    <a href="{{ route('inquiry', ['status' => '2']) }}">
                                        <div class="small-box text-bg-success">
                                            <div class="inner">
                                                <h3>{{ $completeInquiry }}</h3>
                                                <p>Completed</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 2-->
                                    </a>
                                </div> <!--end::Col-->

                                <div class="col-4"> <!--begin::Small Box Widget 4-->
                                    <a href="{{ route('inquiry', ['status' => '4']) }}">
                                        <div class="small-box text-bg-primary">
                                            <div class="inner">
                                                <h3>{{ $confirmInquiry }}</h3>
                                                <p>Confirm</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 4-->
                                    </a>
                                </div> <!--end::Col-->
                            </div>
                            <div class="row">
                                <div class="col-2"></div>
                                <div class="col-4"> <!--begin::Small Box Widget 4-->
                                    <a href="{{ route('inquiry', ['status' => '5']) }}">
                                        <div class="small-box text-bg-info">
                                            <div class="inner">
                                                <h3>{{ $workshopInquiry }}</h3>
                                                <p>Workshop</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 4-->
                                    </a>
                                </div> <!--end::Col-->
                                <div class="col-4"> <!--begin::Small Box Widget 3-->
                                    <a href="{{ route('inquiry', ['status' => '3']) }}">
                                        <div class="small-box text-bg-danger">
                                            <div class="inner">
                                                <h3>{{ $rejectedInquiry }}</h3>
                                                <p>Rejected</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 3-->
                                    </a>
                                </div> <!--end::Col-->
                                <div class="col-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Inquiry Chart</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <label>Date</label>
                                    <input type="text" id="datePeriod" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label>Service Type</label>
                                    <select class="form-select" id="serviceTypeId">
                                        <option value="0" selected>All</option>
                                        @forelse ($serviceType as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <canvas id="pieChart" style="min-height: 350px; height: 350px; max-height: 350px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6 mt-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Orders</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4"> <!--begin::Small Box Widget 1-->
                                    <a href="{{ route('orders', ['status' => '1']) }}">
                                        <div class="small-box text-bg-warning">
                                            <div class="inner">
                                                <h3>{{ $totalOrdersPending }}</h3>
                                                <p>Pending</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 1-->
                                    </a>
                                </div> <!--end::Col-->
                                <div class="col-4"> <!--begin::Small Box Widget 1-->
                                    <a href="{{ route('orders', ['status' => '6']) }}">
                                        <div class="small-box text-bg-primary">
                                            <div class="inner">
                                                <h3>{{ $totalOrdersOrdered }}</h3>
                                                <p>Ordered</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 1-->
                                    </a>
                                </div> <!--end::Col-->
                                <div class="col-4"> <!--begin::Small Box Widget 1-->
                                    <a href="{{ route('orders', ['status' => '7']) }}">
                                        <div class="small-box text-bg-info">
                                            <div class="inner">
                                                <h3>{{ $totalOrdersReceived }}</h3>
                                                <p>Received</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 1-->
                                    </a>
                                </div> <!--end::Col-->
                            </div>
                            <div class="row">
                                <div class="col-2"></div>
                                <div class="col-4"> <!--begin::Small Box Widget 1-->
                                    <a href="{{ route('orders', ['status' => '8']) }}">
                                        <div class="small-box text-bg-danger">
                                            <div class="inner">
                                                <h3>{{ $totalOrdersCancelled }}</h3>
                                                <p>Cancelled</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 1-->
                                    </a>
                                </div> <!--end::Col-->
                                <div class="col-4"> <!--begin::Small Box Widget 1-->
                                    <a href="{{ route('orders', ['status' => '9']) }}">
                                        <div class="small-box text-bg-success">
                                            <div class="inner">
                                                <h3>{{ $totalOrdersFitment }}</h3>
                                                <p>Fitment</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 1-->
                                    </a>
                                </div> <!--end::Col-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{ asset('/dist/js/Chart.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#datePeriod').daterangepicker({
                timePicker: false,
                timePicker24Hour: true,
                timePickerIncrement: 1,
                locale: {
                    format: 'DD-MM-YYYY'
                },
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month')
            });


            inquiryChart();

        });
        $(document).on('input', '#mobileNumber', function() {
            this.value = this.value.replace(/\D/g, '');
        });

        $(document).on('click', '#sendMessage', function() {
            let mobileNumber = $('#mobileNumber').val();
            let errorMessage = '';

            if (!mobileNumber) {
                errorMessage = 'Mobile number is required.';
            } else if (mobileNumber.length !== 10) {
                errorMessage = 'Mobile number must be 10 digits long.';
            }

            if (errorMessage) {
                $('#errorContainer').text(errorMessage).show();
            } else {
                $('#errorContainer').hide();
                // $('#sendMessage').prop('disabled', true);
                $.ajax({
                    type: "POST",
                    headers: {
                        "Accept": "application/json"
                    },
                    url: "{{ route('send-message') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        mobile: mobileNumber,
                    },
                    beforeSend: function() {
                        loaderButton('sendMessage', true);
                    },
                    complete: function() {
                        loaderButton('sendMessage', false);
                    },
                    crossDomain: true,
                    success: function(response) {
                        if (response) {
                            // $('#sendMessage').prop('disabled', false);
                            $('#mobileNumber').val('');
                        }
                    }
                });
            }
        });


    function  inquiryChart(){

        var serviceTypeId = $('#serviceTypeId').val();
        let startDate = $('#datePeriod').data('daterangepicker').startDate.format('YYYY-MM-DD');
        let endDate = $('#datePeriod').data('daterangepicker').endDate.format('YYYY-MM-DD');

        var donutData        = {
            labels: [
                'Chrome',
                'IE',
                'FireFox',
                'Safari',
                'Opera',
                'Navigator',
            ],
            datasets: [
                {
                    data: [700,500,400,600,300,100],
                    backgroundColor : ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
                }
            ]
        }

        var pieChartCanvas = $('#pieChart').get(0).getContext('2d')
        var pieData        = donutData;
        var pieOptions     = {
            maintainAspectRatio : false,
            responsive : true,
        }

        // You can switch between pie and douhnut using the method below.
        new Chart(pieChartCanvas, {
            type: 'pie',
            data: pieData,
            options: pieOptions
        })
    }

    </script>
@endsection
