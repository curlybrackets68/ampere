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
                    <form name="leadsForm"
                        action="{{ isset($lead) ? route('leads.update', @$lead->id) : route('leads.store') }}"
                        method="post">
                        @csrf
                        @if (isset($lead))
                            @method('PUT')
                        @endif
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">{{ isset($lead) ? 'Update Lead' : 'Add Lead' }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" class="form-control" id="name"
                                                placeholder="Enter name" name="name"
                                                value="{{ old('name', $lead->name ?? '') }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Vehicle</label>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select class="form-select" name="vehicle" id="vehicle">
                                                        <option value="vehicle">Select Vehicle</option>
                                                        @forelse (@$vehicle as $key => $value)
                                                            <option value="{{ $key }}"
                                                                {{ @$lead && $key == $lead->vehicle ? 'selected' : '' }}>
                                                                {{ $value }}</option>
                                                        @empty
                                                        @endforelse
                                                    </select>
                                                </div>
                                                {{-- <div class="col-md-2">
                                                    <button type="button" class="btn btn-primary" id="addVehicle"><i
                                                            class="bi bi-plus"></i></button>
                                                </div> --}}
                                            </div>

                                        </div>
                                        <div class="form-group">
                                            <label>Mobile</label>
                                            <input type="text" class="form-control" id="mobile"
                                                placeholder="Enter mobile" name="mobile"
                                                value="{{ old('mobile', $lead->mobile ?? '') }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Area</label>
                                            <input type="text" class="form-control" id="area"
                                                placeholder="Enter area" name="area"
                                                value="{{ old('area', $lead->area ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Lead Source</label>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select class="form-select" name="lead_source" id="lead_source">
                                                        <option value="">Select Lead Source</option>
                                                        @forelse (@$leadSource as $key => $value)
                                                            <option value="{{ $key }}"
                                                                {{ @$lead && $key == $lead->lead_source ? 'selected' : '' }}>
                                                                {{ $value }}</option>
                                                        @empty
                                                        @endforelse
    
                                                    </select>
                                                </div>
                                                {{-- <div class="col-md-2">
                                                    <button type="button" class="btn btn-primary" id="addLeadSource"><i
                                                            class="bi bi-plus"></i></button>
                                                </div> --}}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Salesman</label>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select class="form-select" name="salesman" id="salesman">
                                                        <option value="">Select Salesman</option>
                                                        @forelse (@$salesman as $key => $value)
                                                            <option value="{{ $key }}"
                                                                {{ @$lead && $key == $lead->salesman ? 'selected' : '' }}>
                                                                {{ $value }}
                                                            </option>
                                                        @empty
                                                        @endforelse
                                                    </select>
                                                </div>
                                                {{-- <div class="col-md-2">
                                                    <button type="button" class="btn btn-primary" id="addSalesman"><i
                                                            class="bi bi-plus"></i></button>
                                                </div> --}}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Notes</label>
                                            <textarea class="form-control" name="notes" id="notes" rows="3">{{ old('notes', $lead->notes ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-sm" id="addUpdateLeads">Submit</button>
                                <button type="reset" class="btn btn-light btn-sm">Cancel</button>
                            </div>
                        </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="vehicleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Vehicle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label>Name</label>
                            <input type="text" id="vehicleName" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveVehicle">Save</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="salesmanModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Salesman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label>Name</label>
                            <input type="text" id="salesmanName" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveSalesman">Save</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="leadSourceModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Lead Source</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label>Name</label>
                            <input type="text" id="leadSourceName" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveLeadSource">Save</button>
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
        $(document).on('click', '#addUpdateLeads', function(e) {
            e.preventDefault();

            $('.error-message').remove();
            let name = $('#name').val();
            let vehicle = $('#vehicle').val();
            let mobile = $('#mobile').val();
            let area = $('#area').val();
            let lead_source = $('#lead_source').val();
            let salesman = $('#salesman').val();
            let notes = $('#notes').val();

            let isValid = true;

            if (name === '') {
                $('#name').after('<small class="error-message text-danger">Name is required.</small>');
                isValid = false;
            }
            if (vehicle === '') {
                $('#vehicle').after('<small class="error-message text-danger">Please select a vehicle.</small>');
                isValid = false;
            }
            if (mobile === '') {
                $('#mobile').after('<small class="error-message text-danger">Mobile number is required.</small>');
                isValid = false;
            } else if (!/^\d{10}$/.test(mobile)) {
                $('#mobile').after(
                    '<small class="error-message text-danger">Enter a valid 10-digit mobile number.</small>');
                isValid = false;
            }

            if (area === '') {
                $('#area').after('<small class="error-message text-danger">Area is required.</small>');
                isValid = false;
            }
            if (lead_source === '') {
                $('#lead_source').after(
                    '<small class="error-message text-danger">Lead source is required.</small>');
                isValid = false;
            }
            if (salesman === '') {
                $('#salesman').after('<small class="error-message text-danger">Salesman is required.</small>');
                isValid = false;
            }
            if (notes === '') {
                $('#notes').after('<small class="error-message text-danger">Notes cannot be empty.</small>');
                isValid = false;
            }

            if (isValid) {
                loaderButton('addUpdateLeads', true);
                $('form[name="leadsForm"]').submit();
            }
        });

        $(document).on('keyup change', 'input, textarea, select', function() {
            $(this).siblings('.error-message').remove();
        });

        $(document).on('input', '#mobile', function() {
            let value = $(this).val();
            value = value.replace(/[^0-9]/g, '').substring(0, 10);
            $(this).val(value);
        });

        $(document).on('click', '#addVehicle', function() {
            $('#vehicleModal').modal('show');
        });

        $(document).on('click', '#addSalesman', function() {
            $('#salesmanModal').modal('show');
        });

        $(document).on('click', '#addLeadSource', function() {
            $('#leadSourceModal').modal('show');
        });

        $(document).on('click', '#saveVehicle', function() {
            let vehicleName = $('#vehicleName').val();
            if (vehicleName === '') {
                alert('Vehicle name is required.');
                return;
            }
            let vehicle = {
                _token: "{{ csrf_token() }}",
                name: vehicleName,
            };

            $.ajax({
                type: "POST",
                url: "{{ route('add-vehicle') }}",
                data: vehicle,
                success: function(response) {
                    $('#vehicleModal').modal('hide');
                    $('#vehicleName').val('');
                    getVehicles();
                }
            });
        });

        $(document).on('click', '#saveSalesman', function() {
            let salesmanName = $('#salesmanName').val();
            if (salesmanName === '') {
                alert('Salesman name is required.');
                return;
            }
            let vehicle = {
                _token: "{{ csrf_token() }}",
                name: salesmanName,
            };

            $.ajax({
                type: "POST",
                url: "{{ route('add-salesman') }}",
                data: vehicle,
                success: function(response) {
                    $('#salesmanModal').modal('hide');
                    $('#salesmanName').val('');
                    getSalesman();
                }
            });
        });

        $(document).on('click', '#saveLeadSource', function() {
            let leadSourceName = $('#leadSourceName').val();
            if (leadSourceName === '') {
                alert('Lead Source name is required.');
                return;
            }
            let vehicle = {
                _token: "{{ csrf_token() }}",
                name: leadSourceName,
            };

            $.ajax({
                type: "POST",
                url: "{{ route('add-lead-source') }}",
                data: vehicle,
                success: function(response) {
                    $('#leadSourceModal').modal('hide');
                    $('#leadSourceName').val('');
                    getLeadSource();
                }
            });
        });

        function getVehicles() {
            $.ajax({
                url: "{{ route('vehicle-details') }}",
                type: 'GET',
                success: function(response) {
                    let vehicleSelect = $('#vehicle');
                    vehicleSelect.empty();
                    vehicleSelect.append('<option value="">Select Vehicle</option>');
                    response.forEach(function(vehicle) {
                        vehicleSelect.append(
                            '<option value="' + vehicle.id + '">' + vehicle.name + '</option>'
                        );
                    });
                },
            });
        }

        function getSalesman() {
            $.ajax({
                url: "{{ route('salesman-details') }}",
                type: 'GET',
                success: function(response) {
                    let salesmanSelect = $('#salesman');
                    salesmanSelect.empty();
                    salesmanSelect.append('<option value="">Select Salesman</option>');
                    response.forEach(function(salesman) {
                        salesmanSelect.append(
                            '<option value="' + salesman.id + '">' + salesman.name + '</option>'
                        );
                    });
                },
            });
        }

        function getLeadSource() {
            $.ajax({
                url: "{{ route('lead-source-details') }}",
                type: 'GET',
                success: function(response) {
                    let select = $('#lead_source');
                    select.empty();
                    select.append('<option value="">Select Lead Source</option>');
                    response.forEach(function(lead_source) {
                        select.append(
                            '<option value="' + lead_source.id + '">' + lead_source.name + '</option>'
                        );
                    });
                },
            });
        }
    </script>
@endsection
