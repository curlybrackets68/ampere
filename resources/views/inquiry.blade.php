@extends('master')

@section('title')
    Inquiry | AMPERE
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
                            <h5 class="card-title">Inquiry</h5>
                            <div class="d-flex justify-content-end">
                                <a href="javascript:void(0);" class="btn btn-primary btn-sm d-none" id="exportExcel">
                                    <form action="{{ route('user.inquiry.excel.export') }}" method="POST"
                                        id="exportExcelForm">
                                        @csrf
                                        {{ Form::hidden('exportStartDate', null, ['id' => 'exportStartDate']) }}
                                        {{ Form::hidden('exportEndDate', null, ['id' => 'exportEndDate']) }}
                                        {{ Form::hidden('exportStatusId', 0, ['id' => 'exportStatusId']) }}
                                        {{ Form::hidden('exportBranchId', 0, ['id' => 'exportBranchId']) }}
                                        <i class="bi bi-cloud-download me-1 align-middle me-1"></i> Export
                                    </form>
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Date</label>
                                    <input type="text" id="datePeriod" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label>Status</label>
                                    <select class="form-select" id="searchStatusId">
                                        <option value="0" {{ isset($status) && $status == '0' ? 'selected' : '' }}>All
                                        </option>
                                        <option value="1" {{ !isset($status) || $status == '1' ? 'selected' : '' }}>
                                            Pending</option>
                                        <option value="2" {{ isset($status) && $status == '2' ? 'selected' : '' }}>
                                            Completed</option>
                                        <option value="3" {{ isset($status) && $status == '3' ? 'selected' : '' }}>
                                            Rejected</option>
                                        <option value="4" {{ isset($status) && $status == '4' ? 'selected' : '' }}>
                                            Confirmed</option>
                                        <option value="5" {{ isset($status) && $status == '5' ? 'selected' : '' }}>
                                            Workshop</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Branch</label>
                                    <select class="form-select" id="searchBranchId">
                                        <option value="0">All</option>
                                        @foreach ($branches as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    </select>
                                </div>
                                <div class="col-md-2" style="margin-top: 31px;">
                                    <button type="button" class="btn btn-primary" id="searchReport">Search</button>
                                </div>
                            </div>
                            <hr>
                            <div class="row mt-3">
                                <table class="table table-bordered table-hover" style="width:100%" id="inquiryTable">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left;">Sr. No</th>
                                            <th style="text-align: left;">Inq Date.</th>
                                            <th style="text-align: left;">Inq No.</th>
                                            <th style="text-align: left;">Branch Name</th>
                                            <th style="text-align: left;">Service Type</th>
                                            <th style="text-align: left;">Name</th>
                                            <th style="text-align: left;">Mobile</th>
                                            <th style="text-align: left;">Vehicle No</th>
                                            <th style="text-align: left;">Status</th>
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
                            <input type="hidden" id="statusInquiryId">
                            <label>Status</label>
                            <select class="form-select" id="statusId">
                                {{-- <option value="">Select</option> --}}
                                {{-- <option value="4">Confirmed</option>
                                <option value="2">Completed</option>
                                <option value="3">Rejected</option> --}}
                            </select>
                            <div class="status_error"></div>
                        </div>
                        <div class="col-md-12 d-none mt-3" id="confirmDIv">
                            <label>Date</label>
                            <input type="text" id="confirmDate" class="form-control">
                        </div>
                        <div class="col-md-12 d-none mt-3" id="remarkDiv">
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

    <div class="modal fade" id="confirmDateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
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
                            <input type="hidden" id="confirmInquiryId">
                            <label>Date</label>
                            <input type="text" id="confirmDateValue" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="confirmDateSave">Save changes</button>
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
        $(document).ready(async function() {
            let startDate = $('#datePeriod').data('daterangepicker').startDate.format('YYYY-MM-DD');
            let endDate = $('#datePeriod').data('daterangepicker').endDate.format('YYYY-MM-DD');
            let searchStatusId = $('#searchStatusId').val();
            let searchBranchId = $('#searchBranchId').val();

            let filterData = {
                actionType: 'report',
                startDate: '',
                endDate: '',
                statusId: searchStatusId,
                searchBranchId: searchBranchId
            };
            await inquiryDetails(filterData);
        });

        flatpickr("#confirmDate", {
            enableTime: true,
            dateFormat: "d-m-Y H:i",
            time_24hr: false,
            defaultDate: new Date().setHours(9, 0)
        });

        flatpickr("#confirmDateValue", {
            enableTime: true,
            dateFormat: "d-m-Y H:i",
            time_24hr: false,
            defaultDate: new Date().setHours(9, 0)
        });

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

        $(document).on('click', '#searchReport', async function() {
            let startDate = $('#datePeriod').data('daterangepicker').startDate.format('YYYY-MM-DD');
            let endDate = $('#datePeriod').data('daterangepicker').endDate.format('YYYY-MM-DD');
            let statusId = $('#searchStatusId').val();
            let branchId = $('#searchBranchId').val();

            $('#exportStartDate').val(startDate);
            $('#exportEndDate').val(endDate);
            $('#exportStatusId').val(statusId);
            $('#exportBranchId').val(branchId);

            let data = {
                actionType: 'report',
                startDate: startDate,
                endDate: endDate,
                statusId: statusId,
                searchBranchId: branchId
            };
            await inquiryDetails(data);
            $('#exportExcel').removeClass('d-none');
        });

        async function inquiryDetails(filters = []) {
            let orderByConfirmDate = false;

            if (filters.statusId == '4') {
                orderByConfirmDate = true;
            }

            $('#inquiryTable').DataTable({
                serverSide: true,
                processing: true,
                destroy: true,
                responsive: true,
                scrollX: true,
                ajax: {
                    url: '{{ route('inquiry') }}',
                    data: filters
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'id',
                        searchable: false
                    },
                    {
                        data: 'display_inquiry_date',
                        name: 'created_at'
                    },
                    {
                        data: 'inquiry_no',
                        name: 'inquiry_no'
                    },
                    {
                        data: 'branch_name',
                        name: 'branch_name'
                    },
                    {
                        data: 'service_type',
                        name: 'service_type'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'vehicle_no',
                        name: 'vehicle_no'
                    },
                    {
                        data: 'display_status',
                        name: 'display_status'
                    },
                    {
                        data: 'confirm_date',
                        name: 'confirm_date',
                        visible: false,
                        type: 'date'
                    }
                ],
                // order: [
                //     [0, 'desc']
                // ],
                order: orderByConfirmDate ? [
                    [9, 'asc']
                ] : [
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

        $(document).on('click', '.change-status', function() {
            $('#statusRemark').val('');
            let id = $(this).data('id');
            let statusId = $(this).data('status');

            $('#statusInquiryId').val(id);
            $('#statusModal').modal('show');
            $('#confirmDIv').addClass('d-none');

            let selectedValue = '';
            let html = '<option value="">Select</option>';
            if (statusId == '1') {
                selectedValue = '4';
                html += '<option value="4" selected>Confirmed</option><option value="3">Rejected</option>';
            } else if (statusId == '4') {
                html +=
                    '<option value="2">Completed</option><option value="3">Rejected</option><option value="5">Workshop</option>';
            } else if (statusId == '5') {
                html +=
                    '<option value="2">Completed</option><option value="3">Rejected</option>';
            }

            $('#statusId').html(html);

            $('#statusId').val(selectedValue).trigger('change');
        });

        $(document).on('click', '#changeStatusBtn', function() {
            let id = $('#statusInquiryId').val();
            let statusId = $('#statusId').val();
            let statusRemark = $('#statusRemark').val();

            let startDate = $('#datePeriod').data('daterangepicker').startDate.format('YYYY-MM-DD');
            let endDate = $('#datePeriod').data('daterangepicker').endDate.format('YYYY-MM-DD');
            let searchStatusId = $('#searchStatusId').val();
            let searchBranchId = $('#searchBranchId').val();

            let filterData = {
                actionType: 'report',
                startDate: startDate,
                endDate: endDate,
                statusId: searchStatusId,
                searchBranchId: searchBranchId,
            };

            if (statusId == '') {
                $('.status_error').html('Please select a status');
                return false;
            }

            let confirmDate = '';
            if (statusId == '4') {
                confirmDate = $('#confirmDate').val();
            }

            $.ajax({
                url: '{{ route('inquiry.change-status') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    statusId: statusId,
                    statusRemark: statusRemark,
                    confirmDate: confirmDate
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
                        await inquiryDetails(filterData);
                    } else {
                        alert(response.message);
                    }
                }
            });
        });

        $(document).on('change', '#statusId', function() {
            $('.status_error').html('');

            if ($(this).val() == '1') {
                $('#statusRemark').val('');
                $('#remarkDiv').addClass('d-none');
                $('#confirmDIv').addClass('d-none');
            } else if ($(this).val() === '2') {
                $('#remarkDiv').removeClass('d-none');
                $('#confirmDIv').addClass('d-none');
            } else if ($(this).val() === '3') {
                $('#remarkDiv').removeClass('d-none');
                $('#confirmDIv').addClass('d-none');
            } else if ($(this).val() === '4') {
                $('#statusRemark').val('');
                $('#remarkDiv').addClass('d-none');
                $('#confirmDIv').removeClass('d-none');
            } else {
                $('#statusRemark').val('');
                $('#remarkDiv').addClass('d-none');
                $('#confirmDIv').addClass('d-none');
            }
        });

        $(document).on('click', '#exportExcel', function() {
            $('#exportExcelForm').submit();
        });

        $(document).on('click', '.confirmDateChange', function() {
            $('#confirmInquiryId').val($(this).data('id'));
            $('#confirmDateModal').modal('show');
        });

        $(document).on('click', '#confirmDateSave', function(event) {
            let confirmDate = $('#confirmDateValue').val();
            let id = $('#confirmInquiryId').val();

            $.ajax({
                url: '{{ route('inquiry.change-status') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    statusId: '4',
                    statusRemark: 'Change Confirm Date',
                    confirmDate: confirmDate
                },
                beforeSend: function() {
                    loaderButton('confirmDateSave', true);
                },
                complete: function() {
                    loaderButton('confirmDateSave', false);
                },
                success: async function(response) {
                    if (response.code == '1') {
                        $('#confirmDateModal').modal('hide');
                        await inquiryDetails(filterData);
                    } else {
                        alert(response.message);
                    }
                }
            });
        });
    </script>
@endsection
