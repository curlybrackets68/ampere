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
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Inquiry</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-2 col-md-4 col-6 mb-3"> <!--begin::Small Box Widget 1-->
                                    <a href="{{ route('inquiry', ['status' => '1']) }}">
                                        <div class="small-box text-bg-warning">
                                            <div class="inner">
                                                <h3>{{ $pendingInquiry }}</h3>
                                                <p>Pending</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 1-->
                                    </a>
                                </div> <!--end::Col-->
                                <div class="col-lg-2 col-md-4 col-6 mb-3"> <!--begin::Small Box Widget 2-->
                                    <a href="{{ route('inquiry', ['status' => '2']) }}">
                                        <div class="small-box text-bg-success">
                                            <div class="inner">
                                                <h3>{{ $completeInquiry }}</h3>
                                                <p>Completed</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 2-->
                                    </a>
                                </div> <!--end::Col-->
                                
                                <div class="col-lg-2 col-md-4 col-6 mb-3"> <!--begin::Small Box Widget 4-->
                                    <a href="{{ route('inquiry', ['status' => '4']) }}">
                                        <div class="small-box text-bg-primary">
                                            <div class="inner">
                                                <h3>{{ $confirmInquiry }}</h3>
                                                <p>Confirm</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 4-->
                                    </a>
                                </div> <!--end::Col-->
                                <div class="col-lg-2 col-md-4 col-6 mb-3"> <!--begin::Small Box Widget 4-->
                                    <a href="{{ route('inquiry', ['status' => '5']) }}">
                                        <div class="small-box text-bg-info">
                                            <div class="inner">
                                                <h3>{{ $workshopInquiry }}</h3>
                                                <p>Workshop</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 4-->
                                    </a>
                                </div> <!--end::Col-->
                                <div class="col-lg-2 col-md-4 col-6 mb-3"> <!--begin::Small Box Widget 3-->
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
                <div class="col-md-12 mt-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Orders</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-2 col-md-4 col-6 mb-3"> <!--begin::Small Box Widget 1-->
                                    <a href="{{ route('orders', ['status' => '1']) }}">
                                        <div class="small-box text-bg-warning">
                                            <div class="inner">
                                                <h3>{{ $totalOrdersPending }}</h3>
                                                <p>Pending</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 1-->
                                    </a>
                                </div> <!--end::Col-->
                                <div class="col-lg-2 col-md-4 col-6 mb-3"> <!--begin::Small Box Widget 1-->
                                    <a href="{{ route('orders', ['status' => '6']) }}">
                                        <div class="small-box text-bg-primary">
                                            <div class="inner">
                                                <h3>{{ $totalOrdersOrdered }}</h3>
                                                <p>Ordered</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 1-->
                                    </a>
                                </div> <!--end::Col-->
                                <div class="col-lg-2 col-md-4 col-6 mb-3"> <!--begin::Small Box Widget 1-->
                                    <a href="{{ route('orders', ['status' => '7']) }}">
                                        <div class="small-box text-bg-info">
                                            <div class="inner">
                                                <h3>{{ $totalOrdersReceived }}</h3>
                                                <p>Received</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 1-->
                                    </a>
                                </div> <!--end::Col-->
                                <div class="col-lg-2 col-md-4 col-6 mb-3"> <!--begin::Small Box Widget 1-->
                                    <a href="{{ route('orders', ['status' => '8']) }}">
                                        <div class="small-box text-bg-danger">
                                            <div class="inner">
                                                <h3>{{ $totalOrdersCancelled }}</h3>
                                                <p>Cancelled</p>
                                            </div>
                                        </div> <!--end::Small Box Widget 1-->
                                    </a>
                                </div> <!--end::Col-->
                                <div class="col-lg-2 col-md-4 col-6 mb-3"> <!--begin::Small Box Widget 1-->
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
    <script>
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
    </script>
@endsection
