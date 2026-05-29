@extends('master')

@section('title')
    Leads | AMPERE
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
                            <h5 class="card-title">Leads</h5>
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

                                {{-- <a href="javascript:void(0);" class="btn btn-primary btn-sm me-2" id="exportExcel">
                                    <i class="bi bi-cloud-download me-1 align-middle me-1"></i> Export
                                </a>

                                <form action="{{ route('user.leads.excel.export') }}" method="POST" id="exportExcelForm"
                                    style="display: none;">
                                    @csrf
                                    {{ Form::hidden('exportStartDate', null, ['id' => 'exportStartDate', 'name' => 'exportStartDate']) }}
                                    {{ Form::hidden('exportEndDate', null, ['id' => 'exportEndDate', 'name' => 'exportEndDate']) }}
                                    {{ Form::hidden('exportSalesmanId', null, ['id' => 'exportSalesmanId', 'name' => 'exportSalesmanId']) }}
                                    {{ Form::hidden('exportLeadSourceId', null, ['id' => 'exportLeadSourceId', 'name' => 'exportLeadSourceId']) }}
                                    {{ Form::hidden('exportMobileNumber', null, ['id' => 'exportMobileNumber', 'name' => 'exportMobileNumber']) }}
                                    {{ Form::hidden('exportCustomerName', null, ['id' => 'exportCustomerName', 'name' => 'exportCustomerName']) }}
                                </form> --}}
                                @if(checkRights('USER_LEAD_ROLE_CREATE'))
                                    <a class="btn btn-info btn-sm" href="{{ route('leads.create') }}">
                                        <i class="bi bi-plus me-1 align-middle me-1"></i> Add Lead</a>

                                @endif

                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Date</label>
                                    <input type="text" id="datePeriod" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label>Salesman</label>
                                    <select class="form-select" id="salesmanId">
                                        <option value="0" selected>All</option>
                                        @forelse ($salesman as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Lead Source</label>
                                    <select class="form-select" id="leadSourceId">
                                        <option value="0" selected>All</option>
                                        @forelse (@$leadSource as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Mobile</label>
                                    <input type="text" class="form-control" id="mobileNumber">
                                </div>
                                <div class="col-md-3 mt-3">
                                    <label>Customer Name</label>
                                    <input type="text" class="form-control" id="customerName">
                                </div>
                                <div class="col-md-2 mt-5">
                                    <button type="button" class="btn btn-primary" id="searchReport">Search</button>
                                </div>
                            </div>
                            <hr>
                            <div class="row mt-3">
                                <table class="table table-bordered table-hover" style="width:100%" id="leadsTable">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left;">Sr. No</th>
                                            <th style="text-align: left;">Date</th>
                                            <th style="text-align: left;">Name</th>
                                            <th style="text-align: left;">Vehicle</th>
                                            <th style="text-align: left;">Mobile</th>
                                            <th style="text-align: left;">Lead Source</th>
                                            <th style="text-align: left;">Salesman</th>
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

            leadList();
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

            leadList(filter);
            $('#exportExcel').removeClass('d-none');
        });

        function leadList(filter = []) {
            $('#leadsTable').DataTable({
                serverSide: true,
                processing: true,
                destroy: true,
                responsive: true,
                ajax: {
                    url: '{{ route('leads.index') }}',
                    data: filter
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'leads.id',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'display_date_time',
                        name: 'leads.created_at',
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'leads.name'
                    },
                    {
                        data: 'vehicle_details',
                        name: 'vehicle_details',
                        orderable: false
                    },
                    {
                        data: 'mobile',
                        name: 'leads.mobile'
                    },
                    {
                        data: 'leadSourceName',
                        name: 'lead_sources.name'
                    },
                    {
                        data: 'salesmanName',
                        name: 'users.user_name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        searchable: false,
                        orderable: false
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
                    $('td', row).eq(6).css('text-align', 'left');
                },
            });
        }

        $(document).on('click', '#exportExcel', function() {
            $('#exportExcelForm').submit();
        });

        // $(document).on('click', '#exportExcel', function(e) {
        //     e.preventDefault();

        //     let form = $('#exportExcelForm');
        //     let formData = form.serialize();

        //     $.ajax({
        //         url: form.attr('action'),
        //         method: 'POST',
        //         data: formData,
        //         success: function(response) {
        //             showToast('success', 'Export started. You’ll be notified once it’s ready.');
        //         },
        //         error: function(xhr) {
        //             showToast('error', 'Something went wrong while exporting.');
        //         }
        //     });
        // });
    </script>
@endsection
