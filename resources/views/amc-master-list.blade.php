@extends('master')

@section('title')
    AMC Master | AMPERE
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
                            <h5 class="card-title">AMC Master</h5>
                            <div class="d-flex justify-content-end">
                                <a href="javascript:void(0);" class="btn btn-primary btn-sm me-2 d-none" id="exportExcel">
                                    <form action="{{ route('user.leads.excel.export') }}" method="POST"
                                        id="exportExcelForm">
                                        @csrf
                                        {{ Form::hidden('exportStartDate', null, ['id' => 'exportStartDate']) }}
                                        {{ Form::hidden('exportEndDate', null, ['id' => 'exportEndDate']) }}
                                        {{ Form::hidden('exportSalesmanId', null, ['id' => 'exportSalesmanId']) }}
                                        {{ Form::hidden('exportLeadSourceId', null, ['id' => 'exportLeadSourceId']) }}
                                        {{ Form::hidden('exportMobileNumber', null, ['id' => 'exportMobileNumber']) }}
                                        {{ Form::hidden('exportCustomerName', null, ['id' => 'exportCustomerName']) }}

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
                                            <th style="text-align: left;">Customer Name</th>
                                            <th style="text-align: left;">Customer Number</th>
                                            <th style="text-align: left;">AMC Number</th>
                                            <th style="text-align: left;">AMC Start Date</th>
                                            <th style="text-align: left;">AMC End Date</th>
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
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'contact_number',
                        name: 'contact_number'
                    },
                    {
                        data: 'amc_display_number',
                        name: 'amc_display_number'
                    },
                    {
                        data: 'display_amc_start_date',
                        name: 'display_amc_start_date'
                    },
                    {
                        data: 'display_amc_start_date',
                        name: 'display_amc_start_date'
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
    </script>
@endsection
