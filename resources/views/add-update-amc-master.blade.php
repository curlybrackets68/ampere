@extends('master')

@section('title')
    Add Update AMC | AMPERE
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
@endsection

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <form name="leadsForm"
                          action="{{ isset($amcMaster) ? route('amc-master.update', @$amcMaster->id) : route('amc-master.store') }}"
                          method="post">
                        @csrf
                        @if (isset($amcMaster))
                            @method('PUT')
                        @endif
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">{{ isset($amcMaster) ? 'Update AMC Master' : 'Add AMC Master' }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Chassis Number</label>
                                            <input type="text" class="form-control" id="chassis_number"
                                                   placeholder="Enter Chassis Number" name="chassis_number"
                                                   value="{{ old('name', $amcMaster->chassis_number ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Vehicle Type</label>

                                            <select class="form-select" name="vehicle_type" id="vehicle_type">
                                                <option value="">Select Vehicle Type</option>
                                                @forelse (@$vehicleTypeArray as $key => $value)
                                                    <option value="{{ $key }}"
                                                        {{ @$amcMaster && $key == $amcMaster->vehicle_type ? 'selected' : '' }}>
                                                        {{ $value }}</option>
                                                @empty
                                                @endforelse

                                            </select>
                                        </div>

                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Vehicle</label>

                                            <select class="form-select" name="vehicle_master_id" id="vehicle_master_id">
                                                <option value="">Select Vehicle</option>
                                                @forelse (@$vehicle as $key => $value)
                                                    <option value="{{ $key }}"
                                                        {{ @$amcMaster && $key == $amcMaster->vehicle_master_id ? 'selected' : '' }}>
                                                        {{ $value }}</option>
                                                @empty
                                                @endforelse

                                            </select>
                                        </div>

                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Vehicle Number</label>
                                            <input type="text" class="form-control" id="vehicle_number"
                                                   placeholder="Enter Vehicle Number" name="vehicle_number"
                                                   value="{{ old('name', $amcMaster->vehicle_number ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Customer Name</label>
                                            <input type="text" class="form-control" id="customer_name"
                                                   placeholder="Enter Customer Name" name="customer_name"
                                                   value="{{ old('contact_number', $amcMaster->contact_number ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Contact Number</label>
                                            <input type="text" class="form-control" id="contact_number"
                                                   placeholder="Enter Contact Number" name="contact_number"
                                                   value="{{ old('contact_number', $amcMaster->contact_number ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>AMC Start Date:</label>
                                            <div class="input-group date" id="amc_start_date"
                                                 data-target-input="nearest">
                                                <input type="text" class="form-control datetimepicker-input"  name="amc_start_date"
                                                >

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>AMC End Date</label>
                                            <input type="text" class="form-control" id="amc_end_date"
                                                   placeholder="Enter Contact Number" name="amc_end_date"
                                                   value="{{ old('amc_end_date', $amcMaster->amc_end_date ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-sm" id="addUpdateLeads">Submit</button>
                            <button type="reset" class="btn btn-light btn-sm">Cancel</button>
                        </div>
                    </form>
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
        $('#amc_start_date').datetimepicker({
            format: 'L'
        });
    </script>
@endsection
