@extends('master')

@section('title')
    Service List | AMPERE
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">AMC Master Service</h5>
                            <div class="d-flex justify-content-end">
                                <a href="javascript:void(0);" class="btn btn-primary btn-sm me-2 d-none" id="exportExcel">
                                    <form action="{{ route('amc-master-service.excel.export') }}" method="POST"
                                        id="exportExcelForm">
                                        @csrf
                                        {{ Form::hidden('exportStartDate', null, ['id' => 'exportStartDate']) }}
                                        {{ Form::hidden('exportEndDate', null, ['id' => 'exportEndDate']) }}
                                        {{ Form::hidden('exportChassisNumber', null, ['id' => 'exportChassisNumber']) }}
                                        {{ Form::hidden('exportVehicleNumber', null, ['id' => 'exportVehicleNumber']) }}
                                        {{ Form::hidden('exportContactNumber', null, ['id' => 'exportContactNumber']) }}
                                        {{ Form::hidden('exportVehicleType', null, ['id' => 'exportVehicleType']) }}
                                        {{ Form::hidden('exportvehicleMasterId', null, ['id' => 'exportvehicleMasterId']) }}

                                        <i class="bi bi-cloud-download me-1 align-middle me-1"></i> Export
                                    </form>
                                </a>
                                <a class="btn btn-info btn-sm mr-2" href="#" id="filterBtn">
                                    <i class="bi bi-funnel-fill align-middle me-1"></i>Filter</a>
                                @if (checkRights('USER_AMC_ROLE_CREATE'))
                                    <a class="btn btn-info btn-sm" href="{{ route('amc-master-service.service') }}">
                                        <i class="bi bi-plus me-1 align-middle me-1"></i> Add Service</a>
                                @endif

                            </div>
                        </div>
                        <div class="card-body">
                            <div id="filter-form" class="d-none">
                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Chassis Number</label>
                                            <input type="text" class="form-control" id="chassis_number"
                                                placeholder="Enter Chassis Number" name="chassis_number" value="">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Vehicle Number</label>
                                            <input type="text" class="form-control" id="vehicle_number"
                                                placeholder="Enter Vehicle Number" name="vehicle_number" value="">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Mobile Number</label>
                                            <input type="text" class="form-control" id="contact_number"
                                                placeholder="Enter Mobile Number" name="contact_number" value="">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Service Date</label>
                                            <input type="text" id="datePeriod" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Vehicle Type</label>
                                            <select class="form-select" id="vehicle_type" name="vehicle_type">
                                                <option value="">Select Vehicle Type</option>
                                                @forelse (@$vehicleTypeArray as $key => $value)
                                                    <option value="{{ $key }}"
                                                        {{ @$amcMaster && $key == $amcMaster->vehicle_type ? 'selected' : '' }}>
                                                        {{ $value }}</option>
                                                @empty
                                                @endforelse
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Vehicle Type</label>
                                            <select class="form-select" id="vehicle_master_id" name="vehicle_master_id">
                                                <option value="">Select Vehicle Model</option>
                                                @forelse (@$vehicle as $key => $value)
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @empty
                                                @endforelse
                                            </select>
                                        </div>
                                    </div><div class="col-md-3">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select class="form-select" id="status_id" name="status_id">
                                                <option value="">Select Status</option>
                                                @forelse (@$serviceStatus as $key => $value)
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @empty
                                                @endforelse
                                            </select>
                                        </div>
                                    </div>
                                  
                                    <div class="col-md-2" style="margin-top: 31px;">
                                        <button type="button" class="btn btn-primary" id="searchReport">Search</button>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row mt-3">
                                <table class="table table-bordered table-hover" style="width:100%" id="serviceListTable">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left;">Sr. No</th>
                                            <th style="text-align: left;">Service Date</th>
                                            <th style="text-align: left;">Service By</th>
                                            <th style="text-align: left;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

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

            serviceList();
        });

        $(document).on('input', '#mobile', function() {
            let value = $(this).val();
            value = value.replace(/[^0-9]/g, '').substring(0, 10);
            $(this).val(value);
        });

        $(document).on('click', '#searchReport', function() {
            let startDate = $('#datePeriod').data('daterangepicker').startDate.format('YYYY-MM-DD');
            let endDate = $('#datePeriod').data('daterangepicker').endDate.format('YYYY-MM-DD');
            let chassis_number = $('#chassis_number').val();
            let vehicle_number = $('#vehicle_number').val();
            let contact_number = $('#contact_number').val();
            let vehicle_type = $('#vehicle_type').val();
            let vehicle_master_id = $('#vehicle_master_id').val();

            $('#exportStartDate').val(startDate);
            $('#exportEndDate').val(endDate);
            $('#exportChassisNumber').val(chassis_number);
            $('#exportVehicleNumber').val(vehicle_number);
            $('#exportContactNumber').val(contact_number);
            $('#exportVehicleType').val(vehicle_type);
            $('#exportvehicleMasterId').val(vehicle_master_id);

            let filter = {
                startDate: startDate,
                endDate: endDate,
                chassis_number: chassis_number,
                vehicle_number: vehicle_number,
                contact_number: contact_number,
                vehicle_type: vehicle_type,
                vehicle_master_id: vehicle_master_id,
            };



            serviceList(filter);
            $('#exportExcel').removeClass('d-none');
        });

        function serviceList(filter = []) {
            $('#serviceListTable').DataTable({
                serverSide: false,
                processing: true,
                destroy: true,
                responsive: true,
                ajax: {
                    url: '{{ route('amc-master-service.index') }}',
                    data: filter
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'id',
                        searchable: false
                    },
                    {
                        data: 'service_date',
                        name: 'service_date'
                    },
                    {
                        data: 'service_by',
                        name: 'service_by'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                createdRow: function(row, data, index) {
                    $('td', row).eq(0).css('text-align', 'left');
                    $('td', row).eq(1).css('text-align', 'left');
                    $('td', row).eq(2).css('text-align', 'left');
                    $('td', row).eq(3).css('text-align', 'left');
                    $('td', row).eq(4).css('text-align', 'left');
                    $('td', row).eq(5).css('text-align', 'left');
                },
            });
        }

        $('#serviceListTable').on('draw.dt', function() {
            $('[data-toggle="dropdown"]').dropdown();
        });
      
        $(document).on('click', '#exportExcel', function() {
            $('#exportExcelForm').submit();
        });
       
        $(document).on('click', '#filterBtn', function() {
            $('#filter-form').toggleClass('d-none');
            serviceList()
        });
    </script>
@endsection
