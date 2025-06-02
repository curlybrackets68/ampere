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
                                        {{ Form::hidden('exportActionType', null, ['id' => 'exportActionType']) }}
                                        {{ Form::hidden('exportStatusId', null, ['id' => 'exportStatusId']) }}

                                        <i class="bi bi-cloud-download me-1 align-middle me-1"></i> Export
                                    </form>
                                </a>
                                <a class="btn btn-info btn-sm mr-2" href="#" id="filterBtn">
                                    <i class="bi bi-funnel-fill align-middle me-1"></i>Filter</a>
                                @if (checkRights('USER_SERVICE_ROLE_CREATE'))
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
                                    </div>
                                    <div class="col-md-3">
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
                                            <th style="text-align: left;">Contract ID</th>
                                            <th style="text-align: left;">Customer Details</th>
                                            <th style="text-align: left;">Vehicle Details</th>
                                            <th style="text-align: left;">Service Details</th>
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
    <div class="modal fade" id="amcAddInquiryModel" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">AMC Add Inquiry Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Name</label>
                            <input type="text" class="form-control" id="inquiry_name" name="inquiry_name" />
                            <input type="hidden" class="form-control" id="inquiry_amc_id" name="inquiry_amc_id" />
                            <input type="hidden" class="form-control" id="inquiry_service_id"
                                name="inquiry_service_id" />
                        </div>
                        <div class="col-md-4">
                            <label>Mobile</label>
                            <input type="text" class="form-control" id="inquiry_mobile" name="inquiry_mobile" />
                        </div>
                        <div class="col-md-4">
                            <label>Vehicle Number</label>
                            <input type="text" class="form-control" id="inquiry_vehicle_no"
                                name="inquiry_vehicle_no" />
                        </div>
                        <div class="col-md-4 mt-3">
                            <label>Select Branch</label>
                            <select class="form-select" id="inquiry_branch">
                                <option value="">Select Branch</option>
                                @forelse (@$branch as $key => $value)
                                    <option value="{{ $key }}">
                                        {{ $value }}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-4 mt-3">
                            <label>Select Service Type</label>
                            <select class="form-select" id="inquiry_service_type">
                                <option value="">Select Service Type</option>
                                @forelse (@$serviceTypeArray as $key => $value)
                                    <option value="{{ $key }}">
                                        {{ $value }}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>

                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="addInquiryBtn">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
            let startDate = $('#datePeriod').data('daterangepicker').startDate.format('YYYY-MM-DD');
            let endDate = $('#datePeriod').data('daterangepicker').endDate.format('YYYY-MM-DD');
            let filter = {
                startDate: startDate,
                endDate: endDate,
                chassis_number: '',
                vehicle_number: '',
                contact_number: '',
                vehicle_type: '',
                vehicle_master_id: '',
            };
            serviceList(filter);
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
            let status_id = $('#status_id').val();

            $('#exportStartDate').val(startDate);
            $('#exportEndDate').val(endDate);
            $('#exportChassisNumber').val(chassis_number);
            $('#exportVehicleNumber').val(vehicle_number);
            $('#exportContactNumber').val(contact_number);
            $('#exportVehicleType').val(vehicle_type);
            $('#exportvehicleMasterId').val(vehicle_master_id);
            $('#exportActionType').val('report');
            $('#exportStatusId').val(status_id);

            let filter = {
                startDate: startDate,
                endDate: endDate,
                chassis_number: chassis_number,
                vehicle_number: vehicle_number,
                contact_number: contact_number,
                vehicle_type: vehicle_type,
                vehicle_master_id: vehicle_master_id,
                action_type: 'report',
                status_id: status_id,
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
                        data: 'contract_details',
                        name: 'contract_details'
                    },
                    {
                        data: 'customer_details',
                        name: 'customer_details'
                    }, {
                        data: 'vehicle_details',
                        name: 'vehicle_details'
                    },
                    {
                        data: 'service_details',
                        name: 'service_details'
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
            let startDate = $('#datePeriod').data('daterangepicker').startDate.format('YYYY-MM-DD');
            let endDate = $('#datePeriod').data('daterangepicker').endDate.format('YYYY-MM-DD');
            let filter = {
                startDate: startDate,
                endDate: endDate,
                chassis_number: '',
                vehicle_number: '',
                contact_number: '',
                vehicle_type: '',
                vehicle_master_id: '',
            };
            serviceList(filter)
        });

        $(document).on('click', '.amc-add-inquiry', function() {
            loaderButton('addInquiryBtn', false);
            console.log($(this).data('customer-name'));
            $('#inquiry_name').val($(this).data('customer-name'));
            $('#inquiry_mobile').val($(this).data('customer-number'));
            $('#inquiry_vehicle_no').val($(this).data('vehicle-number'));
            $('#inquiry_amc_id').val($(this).data('id'));
            $('#inquiry_service_id').val($(this).data('service-id'));
            $('#inquiry_service_type').val('');
            $('#inquiry_branch').val('');
            $('#amcAddInquiryModel').modal('show');
            

        });
        $(document).on('click', '#addInquiryBtn', async function() {

            let inquiry_name = $('#inquiry_name').val();
            let inquiry_mobile = $('#inquiry_mobile').val();
            let inquiry_vehicle_no = $('#inquiry_vehicle_no').val();
            let inquiry_service_type = $('#inquiry_service_type').val();
            let branch_id = $('#inquiry_branch').val();
            let amc_id = $('#inquiry_amc_id').val();
            let inquiry_service_id = $('#inquiry_service_id').val();
            let formData = new FormData();

            let isValid = true;
            if (inquiry_name === '') {
                $('#inquiry_name').after(
                    '<small class="error-message text-danger">Enter Name.</small>');
                isValid = false;
            }
            if (inquiry_mobile === '') {
                $('#inquiry_mobile').after(
                    '<small class="error-message text-danger">Mobile number is required.</small>');
                isValid = false;
            } else if (!/^\d{10}$/.test(inquiry_mobile)) {
                $('#inquiry_mobile').after(
                    '<small class="error-message text-danger">Enter a valid 10-digit mobile number.</small>'
                );
                isValid = false;
            }

            if (inquiry_service_type === '') {
                $('#inquiry_service_type').after(
                    '<small class="error-message text-danger">Please Select servie type.</small>');
                isValid = false;
            }
            if (branch_id === '') {
                $('#inquiry_branch').after(
                    '<small class="error-message text-danger">Please Select branch.</small>');
                isValid = false;
            }
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('name', inquiry_name);
            formData.append('mobile', inquiry_mobile);
            formData.append('vehicle_no', inquiry_vehicle_no);
            formData.append('branch_id', branch_id);
            formData.append('amc_id', amc_id);
            formData.append('inquiry_service_id', inquiry_service_id);
            formData.append('inquiry_service_type',inquiry_service_type);

            if (isValid) {
                 loaderButton('addInquiryBtn', true);
                let response = await apiCallPost('{{ route('amc-master-service.add-service-inquiry') }}',
                    formData);

                if (response.code == '1') {

                    showToast('success', response.message);
                    $('#amcAddInquiryModel').modal('hide');
                }
                let startDate = $('#datePeriod').data('daterangepicker').startDate.format('YYYY-MM-DD');
                let endDate = $('#datePeriod').data('daterangepicker').endDate.format('YYYY-MM-DD');
                let filter = {
                    startDate: startDate,
                    endDate: endDate,
                    chassis_number: '',
                    vehicle_number: '',
                    contact_number: '',
                    vehicle_type: '',
                    vehicle_master_id: '',
                };
                serviceList(filter);

            }


        });
    </script>
@endsection
