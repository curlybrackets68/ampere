@extends('master')

@section('title')
    AMC Master | AMPERE
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <style>
        .alignTdCenter {
            text-align: center !important;
            vertical-align: middle !important;
        }
    </style>
@endsection

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">AMC Master</h5>
                            <div class="d-flex justify-content-end">
                                <a href="javascript:void(0);" class="btn btn-primary btn-sm me-2" id="exportExcel">
                                    <form action="{{ route('user.amc.excel.export') }}" method="POST" id="exportExcelForm">
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
                                    <a class="btn btn-info btn-sm" href="{{ route('amc-master.create') }}">
                                        <i class="bi bi-plus me-1 align-middle me-1"></i> Add AMC</a>
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
                                            <label>AMC Due Date</label>
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
                                    <div class="col-md-2" style="margin-top: 31px;">
                                        <button type="button" class="btn btn-primary" id="searchReport">Search</button>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row mt-3">
                                <table class="table table-bordered table-hover" style="width:100%" id="amcMasterTable">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left;">Sr. No</th>
                                            <th style="text-align: left;">Contract ID</th>
                                            <th style="text-align: left;">Customer Deatils</th>
                                            <th style="text-align: left;">Contract Date </th>
                                            <th style="text-align: left;">Vehicle Model</th>
                                            <th style="text-align: left;">Vehicle Data</th>
                                            <th style="text-align: left;">Status</th>
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
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="hidden" id="statusAmcId">
                            <label>Status</label>
                            <select class="form-select" id="statusId">
                            </select>
                        </div>
                        <div class="col-md-12 mt-3" id="remarkDiv">
                            <label>Remark</label>
                            <textarea rows="3" id="statusRemark" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="changeStatusBtn">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="amcViewModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">AMC View Details <span class="amcDisplayNumber"
                            style="color: blueviolet"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="amcData"></div>
                    <div id="tabData" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="amcAddInquiryModel" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">AMC Add Inquiry Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Name</label>
                            <input type="text" class="form-control" id="inquiry_name" name="inquiry_name" />
                            <input type="hidden" class="form-control" id="inquiry_amc_id" name="inquiry_amc_id" />
                        </div>
                        <div class="col-md-3">
                            <label>Mobile</label>
                            <input type="text" class="form-control" id="inquiry_mobile" name="inquiry_mobile" />
                        </div>
                        <div class="col-md-3">
                            <label>Vehicle Number</label>

                            <input type="text" class="form-control" id="inquiry_vehicle_no"
                                name="inquiry_vehicle_no" />
                        </div>
                        <div class="col-md-3">
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
                        <div class="col-md-3">
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
            let searchStatusId = $('#searchStatusId').val();
            let branchId = $('#branchId').val();

            // let filterData = {
            //     actionType: 'report',
            //     startDate: '',
            //     endDate: '',
            //     statusId: searchStatusId,
            //     branchId: branchId
            // };
            amcMasterList();
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
                action_type: 'report',
            };

            amcMasterList(filter);
            $('#exportExcel').removeClass('d-none');
        });

        function amcMasterList(filter = []) {
            $('#amcMasterTable').DataTable({
                serverSide: false,
                processing: true,
                destroy: true,
                responsive: true,
                ajax: {
                    url: '{{ route('amc-master.index') }}',
                    data: filter
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'id',
                        searchable: false
                    },
                    {
                        data: 'amc_display_number',
                        name: 'amc_display_number'
                    },
                    {
                        data: 'customer_details',
                        name: 'customer_details'
                    },
                    {
                        data: 'contact_date',
                        name: 'contact_date'
                    },
                    {
                        data: 'vehicle_model',
                        name: 'vehicle_model'
                    },
                    {
                        data: 'vehicle_data',
                        name: 'vehicle_data'
                    },
                    {
                        data: 'display_status',
                        name: 'display_status'
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

        $('#amcMasterTable').on('draw.dt', function() {
            $('[data-toggle="dropdown"]').dropdown();
        });
        $(document).on('click', '#exportExcel', function() {
            $('#exportExcelForm').submit();
        });


        $(document).on('click', '.change-status', function() {
            let amcId = $(this).data('id');
            $('#statusAmcId').val(amcId);
            let status = $(this).data('status');
            let html = '<option value="">Select</option>';
            if (status == '10') {
                html += '<option value="11">Deactive</option>';
            } else if (status == '11') {
                html += '<option value="10">Active</option>';
            }
            $('#statusId').html(html);
            $('#statusRemark').val('');
            $('#statusModal').modal('show');
        });

        $(document).on('click', '#changeStatusBtn', function() {
            let amcId = $('#statusAmcId').val();
            let statusRemark = $('#statusRemark').val();
            let statusId = $('#statusId').val();

            if (statusId == '') {
                $('#statusId').after('<small class="error-message text-danger">Please select a status</small>');
                return false;
            }
            $.ajax({
                url: '{{ route('amc.change-status') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    amcId: amcId,
                    statusId: statusId,
                    statusRemark: statusRemark,
                },
                beforeSend: function() {
                    loaderButton('changeStatusBtn', true);
                },
                complete: function() {
                    loaderButton('changeStatusBtn', false);
                },
                success: async function(response) {
                    if (response.code == '1') {
                        $('#statusModal').modal('hide');
                        await amcMasterList();
                        showToast('success', response.message);
                    } else {
                        showToast('error', response.message);
                    }
                }
            });
        });

        $(document).on('keyup change', 'input, textarea, select', function() {
            $(this).siblings('.error-message').remove();
        });

        $(document).on('click', '.amc-view', async function() {
            let amcId = $(this).data('id');

            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('amcId', amcId);

            let response = await apiCallPost('{{ route('amc-view-details') }}', formData);

            if (response) {
                if (response.code == '1') {
                    $('#amcViewModal').modal('show');

                    let headerHtml = '';
                    let tabHtml = '';
                    let tabContentHtml = '';

                    let amcData = response.data.amc;
                    let serviceData = response.data.serviceDetail;
                    let amcDetails = response.data.amcDetail;
                    let historyData = response.data.historyData;

                    if (amcData) {
                        $('.amcDisplayNumber').html(`#${amcData.amc_display_number}`);
                        headerHtml += `
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <span style="font-weight: bold;">Chassis Number</span><br>
                                    <span>${amcData.chassis_number}</span>
                                </div>
                                <div class="col-md-3">
                                    <span style="font-weight: bold;">Vehicle Details</span><br>
                                    <span>${amcData.vehicle_type_name} - ${amcData.vehicle_name} - ${amcData.vehicle_number}</span>
                                </div>
                                <div class="col-md-3">
                                    <span style="font-weight: bold;">Customer Name</span><br>
                                    <span>${amcData.customer_name}</span>
                                </div>
                                <div class="col-md-3">
                                    <span style="font-weight: bold;">Customer Mobile</span><br>
                                    <span>${amcData.contact_number}</span>
                                </div>
                                
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <span style="font-weight: bold;">Customer Address</span><br>
                                    <span>${amcData.contact_address ?? ''}</span>
                                </div>
                                <div class="col-md-3">
                                    <span style="font-weight: bold;">Start & End Date</span><br>
                                    <span>${amcData.display_amc_start_date} - ${amcData.display_amc_end_date}</span>
                                </div>
                                <div class="col-md-3">
                                    <span style="font-weight: bold;">Package</span><br>
                                    <span>${amcData.amc_package_type_name}</span>
                                </div>
                                <div class="col-md-3">
                                    <span style="font-weight: bold;">Amount</span><br>
                                    <span>${amcData.amc_basic_price}/- ${amcData.payment_type_name}</span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <span style="font-weight: bold;">Transaction Details</span><br>
                                    <span>${amcData.transaction_details}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                    }

                    $('#amcData').html(headerHtml);

                    tabHtml = `
                <ul class="nav nav-tabs" id="amcServiceTabs" role="tablist">

                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="service-detail-tab" data-bs-toggle="tab" data-bs-target="#service-detail" type="button" role="tab" aria-controls="service-detail" aria-selected="true">Service Details</button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link " id="amc-detail-tab" data-bs-toggle="tab" data-bs-target="#amc-detail" type="button" role="tab" aria-controls="amc-detail" aria-selected="false">Renew AMC Details</button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link " id="system-log-detail-tab" data-bs-toggle="tab" data-bs-target="#system-log-detail" type="button" role="tab" aria-controls="system-log-detail" aria-selected="false">System Log</button>
                    </li>
                    
                </ul>
            `;

                    tabContentHtml = `
                <div class="tab-content" id="amcServiceTabContent">
                    

                    <div class="tab-pane fade show active p-3" id="service-detail" role="tabpanel" aria-labelledby="service-detail-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered serviceTable">
                                <thead>
                                    <tr>
                                        <th class="alignTdCenter">#</th>
                                        <th class="alignTdCenter">Service Date</th>
                                        <th class="alignTdCenter">Remark</th>
                                        <th class="alignTdCenter">Status</th>
                                        <th class="alignTdCenter">Service By</th>
                                        <th class="alignTdCenter">Attachment</th>
                                        <th class="alignTdCenter">Action</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    let index = 1;
                    for (let item of serviceData) {
                        let badge = 'text-bg-primary';
                        if (item.status == '1') {
                            badge = 'text-bg-danger';
                        }
                        tabContentHtml += `
                            <tr>
                                <td class="alignTdCenter" style="width: 10%;">${index++}</td>
                                <td class="alignTdCenter" style="width: 15%;">${item.display_service_date}</td>
                                <td class="alignTdCenter" style="width: 40%;">${item.service_remark ?? ''}</td>
                                <td class="alignTdCenter" style="width: 10%;"><span class="badge ${badge}">${item.status_name}</span></td>
                                <td class="alignTdCenter" style="width: 10%;">${item.service_by ?? ''}</td>
                                <td class="alignTdCenter" style="width: 5%;">
                                ${item.attachment_url ? `
                                        <a href="${item.attachment_url}" download target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fa fa-download"></i> Download
                                        </a>
                                    ` : ''}
                                </td>
                               <td class="alignTdCenter" style="width: 20%;">
                                    ${item.status == 1 ? `<button class="btn btn-sm btn-primary amc-add-inquiry"
                                                    data-id="${item.amc_master_details.id}"
                                                    data-vehicle-number="${item.amc_master_details.vehicle_number}"
                                                    data-customer-name="${item.amc_master_details.customer_name}"
                                                    data-customer-number="${item.amc_master_details.contact_number}">Add inq</button>` : ''}
                                </td>

                                </tr>
                            `;
                    }
                    tabContentHtml += `</tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade p-3" id="amc-detail" role="tabpanel" aria-labelledby="amc-detail-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered amcDetailsTable">
                                <thead>
                                    <tr>
                                        <th class="alignTdCenter">#</th>
                                        <th class="alignTdCenter">Contract Id</th>
                                        <th class="alignTdCenter">Chassis Number</th>
                                        <th class="alignTdCenter">Customer Name</th>
                                        <th class="alignTdCenter">Customer Mobile</th>
                                        <th class="alignTdCenter">Package</th>
                                        <th class="alignTdCenter">Start Date</th>
                                        <th class="alignTdCenter">End Date</th>
                                        <th class="alignTdCenter">Status</th>
                                        <th class="alignTdCenter">Action</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                    let serialNo = 1;
                    for (let item of amcDetails) {
                        let badge = 'text-bg-primary';
                        if (item.status == '11') {
                            badge = 'text-bg-danger';
                        }
                        tabContentHtml += `<tr>
                                    <td class="alignTdCenter" style="width: 5%;">${serialNo++}</td>
                                    <td class="alignTdCenter" style="width: 10%;">${item.amc_display_number}</td>
                                    <td class="alignTdCenter" style="width: 10%;">${item.chassis_number}</td>
                                    <td class="alignTdCenter" style="width: 10%;">${item.customer_name}</td>
                                    <td class="alignTdCenter" style="width: 10%;">${item.contact_number}</td>
                                    <td class="alignTdCenter" style="width: 10%;">${item.amc_package_type_name}</td>
                                    <td class="alignTdCenter" style="width: 10%;">${item.display_amc_start_date}</td>
                                    <td class="alignTdCenter" style="width: 10%;">${item.display_amc_end_date}</td>
                                    <td class="alignTdCenter" style="width: 10%;"><span class="badge ${badge}">${item.status_name}</span></td>
                                    <td class="alignTdCenter" style="width: 5%;"><button class="btn btn-primary btn-sm amc-view" data-id="${item.id}">View</button></td>
                                </tr>`;
                    }

                    tabContentHtml += `</tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade p-3" id="system-log-detail" role="tabpanel" aria-labelledby="system-log-detail-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered systemLogDetailsTable">
                                <thead>
                                    <tr>
                                        <th class="alignTdCenter">#</th>
                                        <th class="alignTdCenter">Date & Time</th>
                                        <th class="alignTdCenter">User Name</th>
                                        <th class="alignTdCenter">Notes</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                    let serialNoHistory = 1;
                    for (let item of historyData) {
                        tabContentHtml += `<tr>
                                    <td class="alignTdCenter" style="width: 5%;">${serialNoHistory++}</td>
                                    <td class="alignTdCenter" style="width: 10%;">${item.display_created_at_date_time}</td>
                                    <td class="alignTdCenter" style="width: 10%;">${item.created_by_name}</td>
                                    <td class="alignTdCenter" style="width: 10%;">${item.remark}</td>
                                </tr>`;
                    }

                    tabContentHtml += `</tbody>
                            </table>
                        </div>
                    </div>


                </div>
            `;

                    $('#tabData').html(tabHtml + tabContentHtml);

                    setTimeout(() => {
                        dataTable('amcDetailsTable');
                    }, 200);
                    setTimeout(() => {
                        dataTable('serviceTable');
                    }, 200);
                    setTimeout(() => {
                        dataTable('systemLogDetailsTable');
                    }, 200);

                } else {
                    showToast('error', response.message);
                }
            }
        });


        function dataTable(tableId) {
            const tableSelector = $("." + tableId);

            if ($.fn.DataTable.isDataTable(tableSelector)) {
                tableSelector.DataTable().clear().destroy();
            }

            tableSelector.DataTable({
                lengthMenu: [5, 10, 25, 50],
                responsive: true,
                ordering: false,
                searching: true,
                pageLength: 5,
                order: [
                    [1, "desc"]
                ],
            });
        }
        $(document).on('click', '#filterBtn', function() {
            $('#filter-form').toggleClass('d-none');
            amcMasterList()
        });
        $(document).on('click', '.amc-add-inquiry', function() {
            console.log($(this).data('customer-name'));
            $('#inquiry_name').val($(this).data('customer-name'));
            $('#inquiry_mobile').val($(this).data('customer-number'));
            $('#inquiry_vehicle_no').val($(this).data('vehicle-number'));
            $('#inquiry_amc_id').val($(this).data('id'));
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

            if (isValid) {
                let response = await apiCallPost('{{ route('amc-master-service.add-service-inquiry') }}',
                    formData);
                amcMasterList();
                if (response.code == '1') {
                    showToast('success', response.message);
                    $('#amcAddInquiryModel').modal('hide');
                    $('#amcViewModal').modal('hide');

                }
               
            }


        });
    </script>
@endsection
