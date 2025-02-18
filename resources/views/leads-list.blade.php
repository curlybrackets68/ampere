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
                                <a class="btn btn-primary btn-sm" href="{{ route('leads.create') }}">Add Lead</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mt-3">
                                <div class="table-responsive">
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
    </div>
@endsection
@section('javascript')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#leadsTable').DataTable({
                serverSide: true,
                processing: true,
                destroy: true,
                responsive: true,
                ajax: {
                    url: '{{ route('leads.index') }}',
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
        });
    </script>
@endsection
