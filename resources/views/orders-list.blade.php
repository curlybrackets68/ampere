@extends('master')

@section('title')
    Order | AMPERE
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
                            <h5 class="card-title">Order</h5>
                            {{-- <div class="d-flex justify-content-end">
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
                            </div> --}}
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
                                        <option value="6" {{ isset($status) && $status == '6' ? 'selected' : '' }}>
                                            Ordered</option>
                                        <option value="7" {{ isset($status) && $status == '7' ? 'selected' : '' }}>
                                            Recieved</option>
                                        <option value="8" {{ isset($status) && $status == '8' ? 'selected' : '' }}>
                                            Cancelled</option>
                                        <option value="9" {{ isset($status) && $status == '9' ? 'selected' : '' }}>
                                            Fitment</option>

                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Branch</label>
                                    <select class="form-select" id="branchId">
                                        <option value="0">All</option>

                                        @forelse ($branch as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>

                                <div class="col-md-2" style="margin-top: 31px;">
                                    <button type="button" class="btn btn-primary" id="searchReport">Search</button>
                                </div>
                            </div>
                            <hr>
                            <div class="row mt-3">
                                <table class="table table-bordered table-striped" style="width:100%" id="orderTable">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left;">Sr. No</th>
                                            <th style="text-align: left;">Order Date.</th>
                                            <th style="text-align: left;">Order No.</th>
                                            <th style="text-align: left;">Order</th>
                                            <th style="text-align: left;">Mobile</th>
                                            <th style="text-align: left;">Branch</th>
                                            <th style="text-align: left;">Vehicle No</th>
                                            <th style="text-align: left;">Order Details</th>
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
                            <input type="hidden" id="statusInquiryId">
                            <label>Status</label>
                            <select class="form-select" id="statusId">

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

    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mt-3">
                        <table class="table table-bordered table-striped" style="width:100%" id="orderHistoryTable">
                            <thead>
                                <tr>
                                    <th style="text-align: left;">Sr. No</th>
                                    <th style="text-align: left;">Action</th>
                                    <th style="text-align: left;">DateTime</th>
                                    <th style="text-align: left;">Remark</th>
                                    <th style="text-align: left;">User Name</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
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
        $(document).ready(async function() {
            let startDate = $('#datePeriod').data('daterangepicker').startDate.format('YYYY-MM-DD');
            let endDate = $('#datePeriod').data('daterangepicker').endDate.format('YYYY-MM-DD');
            let searchStatusId = $('#searchStatusId').val();
            let branchId = $('#branchId').val();

            let filterData = {
                actionType: 'report',
                startDate: '',
                endDate: '',
                statusId: searchStatusId,
                branchId: branchId
            };
            await orderDetails(filterData);
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
            let branchId = $('#branchId').val();

            $('#exportStartDate').val(startDate);
            $('#exportEndDate').val(endDate);
            $('#exportStatusId').val(statusId);
            $('#exportBranchId').val(branchId);

            let data = {
                actionType: 'report',
                startDate: startDate,
                endDate: endDate,
                statusId: statusId,
                branchId: branchId
            };
            await orderDetails(data);
        });



        async function orderDetails(filters = []) {
            let orderByConfirmDate = false;

            if (filters.statusId == '9') {
                orderByConfirmDate = true;
            }

            $('#orderTable').DataTable({
                serverSide: false,
                processing: true,
                destroy: true,
                responsive: true,
                scrollX: true,
                ajax: {
                    url: '{{ route('orders') }}',
                    data: filters
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'id',
                        searchable: false
                    },
                    {
                        data: 'display_order_date',
                        name: 'created_at'
                    },
                    {
                        data: 'order_no',
                        name: 'order_no'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'customer_mobile',
                        name: 'customer_mobile'
                    },
                    {
                        data: 'branch_name',
                        name: 'branch_name'
                    },
                    {
                        data: 'customer_vehicle_no',
                        name: 'customer_vehicle_no'
                    }, {
                        data: 'order_name',
                        name: 'order_name'
                    },
                    {
                        data: 'display_status',
                        name: 'display_status'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
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

        $(document).on('click', '.change-status', function() {
            $('#statusRemark').val('');
            let id = $(this).data('id');
            let statusId = $(this).data('status');

            $('#statusInquiryId').val(id);
            $('#statusModal').modal('show');
            $('#confirmDIv').addClass('d-none');
            $('#remarkDiv').addClass('d-none');

            let selectedValue = '';
            let html = '<option value="">Select</option>';
            if (statusId == '1') {
                html += '<option value="6">Order</option><option value="8">Cancelled</option>';
            } else if (statusId == '6') {
                html += '<option value="7">Recieved</option>';
            } else if (statusId == '7') {
                html += '<option value="9">Fitment</option>';
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
            let branchId = $('#branchId').val();

            let filterData = {
                actionType: 'report',
                startDate: startDate,
                endDate: endDate,
                statusId: searchStatusId,
                branchId: branchId,
            };

            if (statusId == '') {
                $('.status_error').html('Please select a status');
                return false;
            }

            let confirmDate = '';
            if (statusId == '9') {
                confirmDate = $('#confirmDate').val();
            }

            $.ajax({
                url: '{{ route('orders.change-status') }}',
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
                        await orderDetails(filterData);
                    } else {
                        alert(response.message);
                    }
                }
            });
        });

        $(document).on('change', '#statusId', function() {
            $('.status_error').html('');
            $('#remarkDiv').addClass('d-none');
            $('#statusRemark').val('');
            if ($(this).val() === '9') {
                $('#confirmDIv').removeClass('d-none');
            } else {
                $('#remarkDiv').removeClass('d-none');
                $('#confirmDIv').addClass('d-none');
            }

        });

        $(document).on('click', '.open-history-modal', async function() {
            let type_id = $(this).data('type-id');
            $('#historyModal').modal('show');
            let url = '{{ route('orders.get-history', ['type_id' => 'ID']) }}';
            url = url.replace('ID', type_id);
            $('#orderHistoryTable').DataTable({
                serverSide: false,
                processing: true,
                destroy: true,
                responsive: true,
                scrollX: true,
                ajax: {
                    url: url,
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'id',
                        searchable: false
                    },
                    {
                        data: 'display_action',
                        name: 'display_action'
                    }, {
                        data: 'display_date',
                        name: 'created_at'
                    }, {
                        data: 'remark',
                        name: 'remark'
                    }, {
                        data: 'created_by_name',
                        name: 'created_by_name'
                    }

                ],
                 order: [
                    [0, 'desc']
                ],

            });

        });
    </script>
@endsection
