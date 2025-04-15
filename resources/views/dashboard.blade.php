@extends('master')

@section('title')
    AMPERE
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
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
                @if(checkRights('USER_INQUIRY_ROLE_VIEW') || checkRights('USER_INQUIRY_ROLE_VIEWONLY'))
                    <div class="col-6">
                        <div class="card h-100 " >
                            <div class="card-header">
                                <h5 class="card-title">Inquiry</h5>
                            </div>
                            <div class="card-body">
                                <div class="row ">
                                    <div class="col-4 "> <!--begin::Small Box Widget 1-->
                                        <a href="{{ route('inquiry', ['status' => '1']) }}">
                                            <div class="small-box text-bg-warning">
                                                <div class="inner">
                                                    <h3>{{ $pendingInquiry }}</h3>
                                                    <p>Pending</p>
                                                </div>
                                            </div> <!--end::Small Box Widget 1-->
                                        </a>
                                    </div> <!--end::Col-->
                                    <div class="col-4 "> <!--begin::Small Box Widget 2-->
                                        <a href="{{ route('inquiry', ['status' => '2']) }}">
                                            <div class="small-box text-bg-success">
                                                <div class="inner">
                                                    <h3>{{ $completeInquiry }}</h3>
                                                    <p>Completed</p>
                                                </div>
                                            </div> <!--end::Small Box Widget 2-->
                                        </a>
                                    </div> <!--end::Col-->

                                    <div class="col-4 "> <!--begin::Small Box Widget 4-->
                                        <a href="{{ route('inquiry', ['status' => '4']) }}">
                                            <div class="small-box text-bg-primary">
                                                <div class="inner">
                                                    <h3>{{ $confirmInquiry }}</h3>
                                                    <p>Confirm</p>
                                                </div>
                                            </div> <!--end::Small Box Widget 4-->
                                        </a>
                                    </div> <!--end::Col-->

                                    <div class="col-4 "> <!--begin::Small Box Widget 4-->
                                        <a href="{{ route('inquiry', ['status' => '5']) }}">
                                            <div class="small-box text-bg-info">
                                                <div class="inner">
                                                    <h3>{{ $workshopInquiry }}</h3>
                                                    <p>Workshop</p>
                                                </div>
                                            </div> <!--end::Small Box Widget 4-->
                                        </a>
                                    </div> <!--end::Col-->
                                    <div class="col-4 "> <!--begin::Small Box Widget 3-->
                                        <a href="{{ route('inquiry', ['status' => '3']) }}">
                                            <div class="small-box text-bg-danger">
                                                <div class="inner">
                                                    <h3>{{ $rejectedInquiry }}</h3>
                                                    <p>Rejected</p>
                                                </div>
                                            </div> <!--end::Small Box Widget 3-->
                                        </a>
                                    </div> <!--end::Col-->

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Inquiry Chart</h5>
                                <div class="card-tools">
                                    <div class="">
                                        <input type="text" id="inquiryDatePeriod" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <label>Service Type</label>
                                        <select class="form-select" id="serviceTypeId">
                                            <option value="0" selected>All</option>
                                            @forelse ($serviceType as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Status</label>
                                        <select class="form-select" id="inquiryStatusId">
                                            <option value="0" selected>All</option>
                                            @forelse ($inquiryStatusArrayData as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                                <canvas id="inquiryChart"
                                        style="min-height: 350px; height: 350px; max-height: 350px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            <div class="row mt-3">
                @if(checkRights('USER_ORDER_ROLE_VIEW') || checkRights('USER_ORDER_ROLE_VIEWONLY'))
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
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Order Chart</h5>
                                <div class="card-tools">
                                    <div class="">
                                        <input type="text" id="orderDatePeriod" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">

                                    <div class="col-md-4">
                                        <label>Status</label>
                                        <select class="form-select" id="orderStatusId">
                                            <option value="0" selected>All</option>
                                            @forelse ($orderStatusArrayData as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                                <canvas id="orderChart"
                                        style="min-height: 350px; height: 350px; max-height: 350px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{ asset('/dist/js/Chart.min.js') }}"></script>
    <script>

        $(document).ready(function () {
            $('#inquiryDatePeriod').daterangepicker({
                timePicker: false,
                timePicker24Hour: true,
                timePickerIncrement: 1,
                locale: {
                    format: 'DD-MM-YYYY'
                },
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month')
            });
            $('#orderDatePeriod').daterangepicker({
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
            orderChart();
        });
        $(document).on('input', '#mobileNumber', function () {
            this.value = this.value.replace(/\D/g, '');
        });

        $(document).on('click', '#sendMessage', function () {
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
                    beforeSend: function () {
                        loaderButton('sendMessage', true);
                    },
                    complete: function () {
                        loaderButton('sendMessage', false);
                    },
                    crossDomain: true,
                    success: function (response) {
                        if (response) {
                            // $('#sendMessage').prop('disabled', false);
                            $('#mobileNumber').val('');
                        }
                    }
                });
            }
        });

        $(document).on('change', '#serviceTypeId,#inquiryStatusId', function () {
            inquiryChart();
        });
        $(document).on('change', '#orderStatusId', function () {
            orderChart();
        });


        let inquiryChatInstance = null;
        let orderChatInstance = null;

        function inquiryChart() {
            var statusId = $('#statusId').val();
            var serviceTypeId = $('#serviceTypeId').val();
            var startDate = $('#inquiryDatePeriod').data('daterangepicker').startDate.format('YYYY-MM-DD');
            var endDate = $('#inquiryDatePeriod').data('daterangepicker').endDate.format('YYYY-MM-DD');

            $.ajax({
                url: "{{ route('get-inquiry-chart') }}",
                method: 'GET',
                data: {
                    serviceTypeId: serviceTypeId,
                    startDate: startDate,
                    endDate: endDate,
                    statusId: statusId
                },
                success: function (response) {
                    const inquiryData = {
                        labels: response.labels,
                        datasets: [{
                            data: response.data,
                            backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
                        }]
                    };

                   let ctx1 =  $('#inquiryChart').get(0).getContext('2d');

                    if (inquiryChatInstance) {
                        inquiryChatInstance.destroy();
                    }

                    inquiryChatInstance = new Chart(ctx1, {
                        type: 'pie',
                        data: inquiryData,
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                        }
                    });

                },
                error: function (xhr, status, error) {
                    console.error("Error fetching chart data:", error);
                }
            });
        }
        function orderChart() {
            var statusId = $('#orderStatusId').val();
            var startDate = $('#orderDatePeriod').data('daterangepicker').startDate.format('YYYY-MM-DD');
            var endDate = $('#orderDatePeriod').data('daterangepicker').endDate.format('YYYY-MM-DD');

            $.ajax({
                url: "{{ route('get-order-chart') }}",
                method: 'GET',
                data: {
                    startDate: startDate,
                    endDate: endDate,
                    statusId: statusId
                },
                success: function (response) {
                    const orderChartData = {
                        labels: response.labels,
                        datasets: [{
                            data: response.data,
                            backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
                        }]
                    };

                    const ctx1 = $('#orderChart').get(0).getContext('2d');

                    if (orderChatInstance) {
                        orderChatInstance.destroy();
                    }

                    orderChatInstance = new Chart(ctx1, {
                        type: 'pie',
                        data: orderChartData,
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                        }
                    });

                },
                error: function (xhr, status, error) {
                    console.error("Error fetching chart data:", error);
                }
            });
        }


    </script>
@endsection
