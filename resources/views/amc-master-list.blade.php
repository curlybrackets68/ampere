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

                                        <i class="bi bi-cloud-download me-1 align-middle me-1"></i> Export
                                    </form>
                                </a>
                                @if (checkRights('USER_AMC_ROLE_CREATE'))
                                    <a class="btn btn-info btn-sm" href="{{ route('amc-master.create') }}">
                                        <i class="bi bi-plus me-1 align-middle me-1"></i> Add AMC</a>
                                @endif

                            </div>
                        </div>
                        <div class="card-body">

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
                    <h5 class="modal-title" id="exampleModalLabel">AMC View Details</h5>
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
            let salesmanId = $('#salesmanId').val();
            let leadSourceId = $('#leadSourceId').val();
            let mobileNumber = $('#mobileNumber').val();
            let customerName = $('#customerName').val();

            $('#exportStartDate').val(startDate);
            $('#exportEndDate').val(endDate);
            $('#exportSalesmanId').val(salesmanId);
            $('#exportLeadSourceId').val(leadSourceId);
            $('#exportMobileNumber').val(mobileNumber);
            $('#exportCustomerName').val(customerName);

            let filter = {
                startDate: startDate,
                endDate: endDate,
                salesmanId: salesmanId,
                leadSourceId: leadSourceId,
                mobileNumber: mobileNumber,
                customerName: customerName
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
                        headerHtml += `
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <span>Contract Id</span><br>
                                    <span>${amcData.amc_display_number}</span>
                                </div>
                                <div class="col-md-3">
                                    <span>Customer Name</span><br>
                                    <span>${amcData.customer_name}</span>
                                </div>
                                <div class="col-md-3">
                                    <span>Customer Mobile</span><br>
                                    <span>${amcData.contact_number}</span>
                                </div>
                                <div class="col-md-3">
                                    <span>Customer Address</span><br>
                                    <span>${amcData.contact_address ?? ''}</span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <span>Start Date</span><br>
                                    <span>${amcData.display_amc_start_date}</span>
                                </div>
                                <div class="col-md-3">
                                    <span>End Date</span><br>
                                    <span>${amcData.display_amc_end_date}</span>
                                </div>
                                <div class="col-md-3">
                                    <span>Package</span><br>
                                    <span>${amcData.amc_package_type_name}</span>
                                </div>
                                <div class="col-md-3">
                                    <span>Amount</span><br>
                                    <span>${amcData.amc_basic_price}</span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <span>Vehicle Number</span><br>
                                    <span>${amcData.vehicle_number}</span>
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
                        <button class="nav-link " id="amc-detail-tab" data-bs-toggle="tab" data-bs-target="#amc-detail" type="button" role="tab" aria-controls="amc-detail" aria-selected="false">AMC Details</button>
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
                                        <th class="alignTdCenter">Service Status</th>
                                        <th class="alignTdCenter">Remark</th>
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
                                    <td class="alignTdCenter" style="width: 20%;">${item.display_service_date}</td>
                                    <td class="alignTdCenter" style="width: 20%;"><span class="badge ${badge}">${item.status_name}</span></td>
                                    <td class="alignTdCenter" style="width: 50%;">${item.service_remark ?? ''}</td>
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
                                    <td class="alignTdCenter" style="width: 10%;">${item.display_created_at}</td>
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
    </script>
@endsection
