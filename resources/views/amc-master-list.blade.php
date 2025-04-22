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
                                @if(checkRights('USER_AMC_ROLE_CREATE'))
                                    <a class="btn btn-info btn-sm" href="{{ route('amc-master.create') }}">
                                        <i class="bi bi-plus me-1 align-middle me-1"></i> Add AMC</a>

                                @endif

                            </div>
                        </div>
                        <div class="card-body">

                            <div class="row mt-3">
                                <table class="table table-bordered table-hover" style="width:100%" id="leadsTable">
                                    <thead>
                                    <tr>
                                        <th style="text-align: left;">Sr. No</th>
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
                serverSide: false,
                processing: true,
                destroy: true,
                responsive: true,
                ajax: {
                    url: '{{ route('leads.index') }}',
                    data: filter
                },
                columns: [{
                    data: 'DT_RowIndex',
                    name: 'id',
                    searchable: false
                },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'vehicleName',
                        name: 'vehicle.name'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'leadSourceName',
                        name: 'lead_sources.name'
                    },
                    {
                        data: 'salesmanName',
                        name: 'salesman.name'
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

        $(document).on('click', '#exportExcel', function() {
            $('#exportExcelForm').submit();
        });
    </script>
@endsection
