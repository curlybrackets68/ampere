@extends('master')

@section('title')
    AMPERE
@endsection

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Dashboard</h3>
                </div>
            </div>

            <div class="row mt-3"> <!--begin::Col-->
                <div class="col-lg-3 col-6"> <!--begin::Small Box Widget 1-->
                    <a href="{{ route('inquiry', ['status' => '1']) }}">
                        <div class="small-box text-bg-warning">
                            <div class="inner">
                                <h3>{{ $pendingInquiry }}</h3>
                                <p>Pending</p>
                            </div>
                        </div> <!--end::Small Box Widget 1-->
                    </a>
                </div> <!--end::Col-->
                <div class="col-lg-3 col-6"> <!--begin::Small Box Widget 2-->
                    <a href="{{ route('inquiry', ['status' => '2']) }}">
                        <div class="small-box text-bg-success">
                            <div class="inner">
                                <h3>{{ $completeInquiry }}</h3>
                                <p>Completed</p>
                            </div>
                        </div> <!--end::Small Box Widget 2-->
                    </a>
                </div> <!--end::Col-->
                <div class="col-lg-3 col-6"> <!--begin::Small Box Widget 3-->
                    <a href="{{ route('inquiry', ['status' => '3']) }}">
                        <div class="small-box text-bg-danger">
                            <div class="inner">
                                <h3>{{ $rejectedInquiry }}</h3>
                                <p>Rejected</p>
                            </div>
                        </div> <!--end::Small Box Widget 3-->
                    </a>
                </div> <!--end::Col-->
                <div class="col-lg-3 col-6"> <!--begin::Small Box Widget 4-->
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
        </div>
    </div>
@endsection
