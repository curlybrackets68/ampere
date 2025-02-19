@extends('master')

@section('title')
    AMPERE
@endsection

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Add / Edit Salesman</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4">
                                    <input type="hidden" id="salesmanId" value="">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" class="form-control" id="salesmanName" value="">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="mobile">Mobile</label>
                                        <input type="text" class="form-control" id="salesmanMobile" value="">
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <button type="button" class="btn btn-primary" id="saveSalesman">Save</button>
                                    <button type="reset" class="btn btn-info" id="cancelSave">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title">Salesman List</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mt-3">
                                <table class="table table-bordered table-hover" style="width:100%" id="salesmanTable">
                                    <thead>
                                        <tr>
                                            <th style="text-align: center;">Sr. No</th>
                                            <th style="text-align: center;">Name</th>
                                            <th style="text-align: center;">Mobile</th>
                                            <th style="text-align: center;">Action</th>
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
    <script>
        $(document).ready(function() {
            salesMans();
        });

        function salesMans() {
            $('#salesmanTable').DataTable({
                serverSide: true,
                processing: true,
                destroy: true,
                responsive: true,
                ajax: {
                    url: '{{ route('salesman') }}',
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
                        data: 'mobile',
                        name: 'mobile'
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
                    $('td', row).eq(0).css('text-align', 'center');
                    $('td', row).eq(1).css('text-align', 'center');
                    $('td', row).eq(2).css('text-align', 'center');
                    $('td', row).eq(3).css('text-align', 'center');
                },
            });
        }

        $(document).on('click', '#saveSalesman', function() {
            let salesmanName = $('#salesmanName').val();
            let salesmanMobile = $('#salesmanMobile').val();
            if (salesmanName === '') {
                $('#salesmanName').after(
                    '<small class="error-message text-danger">Salesman Name is required.</small>');
                return;
            }

            let salesman = {
                _token: '{{ csrf_token() }}',
                id: $('#salesmanId').val(),
                name: salesmanName,
                mobile: salesmanMobile
            };

            loaderButton('saveSalesman', true);
            $.ajax({
                url: '{{ route('add-salesman') }}',
                type: 'POST',
                data: salesman,
                success: function(response) {
                    loaderButton('saveSalesman', false);
                    if (response.success) {
                        $('#salesmanName').val('');
                        $('#salesmanMobile').val('');
                        $('#salesmanId').val('');
                        salesMans();
                    } else {
                        loaderButton('saveSalesman', false);
                        alert(response.message);
                    }
                }
            });
        });

        $(document).on('keyup change', 'input', function() {
            $(this).siblings('.error-message').remove();
        });

        $(document).on('click', '.edit-salesman', function() {
            let salesmanId = $(this).data('id');
            $('#salesmanId').val(salesmanId);
            let url = '{{ route('salesman-details', ['id' => 'ID']) }}';
            url = url.replace('ID', salesmanId);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(salesman) {
                    $('#salesmanName').val(salesman.name);
                    $('#salesmanMobile').val(salesman.mobile);
                }
            });
        });

        $(document).on('click', '#cancelSave', function() {
            $('#salesmanName').val('');
            $('#salesmanMobile').val('');
            $('#salesmanId').val('');
        });

        $(document).on('input', '#salesmanMobile', function() {
            let value = $(this).val();
            value = value.replace(/[^0-9]/g, '').substring(0, 10);
            $(this).val(value);
        });
    </script>
@endsection
