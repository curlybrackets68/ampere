@extends('master')

@section('title')
    AMPERE
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
                                        <option value="1">Pending</option>
                                        <option value="2">Completed</option>
                                        <option value="3">Rejected</option>
                                        <option value="4">Confirmed</option>
                                    </select>
                                </div>
                                <div class="col-md-2" style="margin-top: 31px;">
                                    <button type="button" class="btn btn-primary" id="searchReport">Search</button>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <table class="table table-bordered table-striped dt-responsive nowrap" style="width:100%"
                                    id="inquiryTable">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left;">Sr. No</th>
                                            <th style="text-align: left;">Inq No.</th>
                                            <th style="text-align: left;">Name</th>
                                            <th style="text-align: left;">Mobile</th>
                                            <th style="text-align: left;">Vehicle No</th>
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
                                <option value="">Select</option>
                                <option value="2">Completed</option>
                                <option value="3">Rejected</option>
                                <option value="4">Confirmed</option>
                            </select>
                            <div class="status_error"></div>
                        </div>
                        <div class="col-md-12 d-none mt-3" id="confirmDIv">
                            <label>Date</label>
                            <input type="text" id="confirmDate" class="form-control">
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
        $(document).ready(async function() {
            await inquiryDetails();
        });

        flatpickr("#confirmDate", {
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

            let data = {
                actionType: 'report',
                startDate: startDate,
                endDate: endDate,
                statusId: statusId
            };
            await inquiryDetails(data);
        });

        async function inquiryDetails(filters = []) {
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
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'inquiry_no',
                        name: 'inquiry_no'
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
                        data: 'action',
                        name: 'action'
                    },
                ],
                order: [
                    [1, 'desc']
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
            let id = $(this).data('id');
            $('#statusInquiryId').val(id);
            $('#statusId').val('').trigger('change');
            $('#statusModal').modal('show');
        });

        $(document).on('click', '#changeStatusBtn', function() {
            let id = $('#statusInquiryId').val();
            let statusId = $('#statusId').val();

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
                    confirmDate: confirmDate
                },
                success: async function(response) {
                    if (response.code == '1') {
                        $('#statusModal').modal('hide');
                        await inquiryDetails();
                    } else {
                        alert(response.message);
                    }
                }
            });
        });

        $(document).on('change', '#statusId', function() {
            $('.status_error').html('');

            if ($(this).val() === '4') {
                $('#confirmDIv').removeClass('d-none');
            } else {
                $('#confirmDIv').addClass('d-none');
            }
        });
    </script>
@endsection
