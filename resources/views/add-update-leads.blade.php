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
                                            <select class="form-select" name="vehicle" id="vehicle">
                                                <option value="vehicle">Select Vehicle</option>
                                            </select>
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
                                            <select class="form-select" name="lead_source" id="lead_source">
                                                <option value="">Select Lead Source</option>
                                                @forelse ($leadSource as $key => $value)
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @empty
                                                @endforelse

                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Salesman</label>
                                            <select class="form-select" name="salesman" id="salesman">
                                                <option value="">Select Salesman</option>
                                                <option value="1">Mihir</option>
                                            </select>
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
                            </div>
                        </div>
                </div>
                </form>
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
    </script>
@endsection
