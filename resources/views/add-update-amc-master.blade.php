@extends('master')

@section('title')
    Add Update AMC | AMPERE
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
                    <form name="amcMasterForm"
                        action="{{ isset($amcMaster) ? route('amc-master.update', @$amcMaster->id) : route('amc-master.store') }}"
                        method="post">
                        @csrf
                        @if (isset($amcMaster))
                            @method('PUT')
                        @endif
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">{{ isset($amcMaster) ? 'Update AMC Master' : 'Add AMC Master' }}</h5>
                                <div class="card-tools">
                                    <div class="input-group date">
                                        <input type="text" id="amc_display_number" name="amc_display_number"
                                            class="form-control"
                                            value="{{ old('name', $amcMaster->amc_display_number ?? $amcDisplayNumber) }}">
                                    </div>
                                </div>

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
                                    <div class="col-md-2">
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
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Package Type</label>
                                            <select class="form-select" name="amc_package_type_id" id="amc_package_type_id">
                                                <option value="">Select Package</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Vehicle Number</label>
                                            <input type="text" class="form-control" id="vehicle_number"
                                                placeholder="Enter Vehicle Number" name="vehicle_number"
                                                value="{{ old('name', $amcMaster->vehicle_number ?? '') }}">
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Contact Number</label>
                                            <input type="text" class="form-control" id="contact_number"
                                                placeholder="Enter Contact Number" name="contact_number"
                                                value="{{ old('contact_number', $amcMaster->contact_number ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Customer Name</label>
                                            <input type="text" class="form-control" id="customer_name"
                                                placeholder="Enter Customer Name" name="customer_name"
                                                value="{{ old('customer_name', $amcMaster->customer_name ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Address</label>
                                            <textarea class="form-control" rows="2" id="contact_address" placeholder="Enter Address" name="contact_address">{{ @$amcMaster->contact_address }}</textarea>

                                        </div>
                                    </div>

                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>AMC Start Date:</label>
                                            <div class="input-group date">
                                                <input type="text" id="amc_start_date" name="amc_start_date"
                                                    class="form-control">

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>AMC End Date</label>
                                            <input type="text" class="form-control" id="amc_end_date"
                                                placeholder="Enter Contact Number" name="amc_end_date"
                                                value="{{ old('amc_end_date', $amcMaster->amc_end_date ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Payment Type</label>
                                            <select class="form-select" name="payment_type" id="payment_type">
                                                <option value="">Select Payment Type</option>
                                                @forelse (@$paymentTypeArray as $key => $value)
                                                    <option value="{{ $key }}"
                                                        {{ @$amcMaster && $key == $amcMaster->payment_type ? 'selected' : '' }}>
                                                        {{ $value }}</option>
                                                @empty
                                                @endforelse

                                            </select>
                                        </div>

                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Amount</label>
                                            <input type="text" class="form-control" id="amc_basic_price"
                                            placeholder="Amount" name="amc_basic_price"
                                            value="{{ old('amc_basic_price', $amcMaster->amc_basic_price ?? '') }}"
                                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                                     
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Transaction Details</label>
                                            <input type="text" class="form-control" id="transaction_details"
                                                placeholder="Transaction Details" name="transaction_details"
                                                value="{{ old('name', $amcMaster->transaction_details ?? '') }}">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-sm"
                                    id="addUpdateAmcMaster">Submit</button>
                                <button type="reset" class="btn btn-light btn-sm">Cancel</button>
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
        $(document).ready(async function() {
            let amcMaster = @json(@$amcMaster);
            if (amcMaster) {
                $('#vehicle_type').trigger('change');
            }
        
        });
        $(function() {
            flatpickr("#amc_start_date", {
                dateFormat: "d-m-Y", // dd-mm-yyyy format
                defaultDate: new Date() // set today's date
            });
        });
        $(function() {
            flatpickr("#amc_end_date", {
                dateFormat: "d-m-Y", // dd-mm-yyyy format
                defaultDate: new Date() // set today's date
            });
        });
        $(document).on('change', '#vehicle_type', function() {
            let type_id = $(this).val();
            const dropdown = $('#amc_package_type_id');
            dropdown.empty();
            $.ajax({
                url: "{{ route('amc-master.get-amc-package-master') }}",
                method: 'GET',
                data: {
                    type_id: type_id,

                },
                success: function(response) {
                    if (response.code == '1') {
                        const packages = response.data.amcPackageMaster;
                        let amcMaster = @json(@$amcMaster);
                        $.each(packages, function(index, item) {
                            let selected = '';
                            if (amcMaster && amcMaster.amc_package_type_id == item.id) {
                                selected = 'selected';
                            }
                            const text =
                                `${item.service_count} Services`;
                            dropdown.append(
                                `<option value="${item.id}" data-time-period="${item.time_period}" ${selected}>${text}</option>`
                            );
                        });
                        $('#amc_package_type_id').trigger('change');
                    }

                },
                error: function(xhr, status, error) {
                    console.error("Error fetching chart data:", error);
                }
            });
        });

        $(document).on('click', '#addUpdateAmcMaster', function(e) {
            e.preventDefault();

            $('.error-message').remove();
            let chassis_number = $('#chassis_number').val();
            let vehicle_type = $('#vehicle_type').val();
            let vehicle_master_id = $('#vehicle_master_id').val();
            let amc_package_type_id = $('#amc_package_type_id').val();
            let contact_number = $('#contact_number').val();
            let customer_name = $('#customer_name').val();

            let isValid = true;

            if (chassis_number === '') {
                $('#chassis_number').after(
                    '<small class="error-message text-danger">Chassis number is required.</small>');
                isValid = false;
            }
            if (vehicle_type === '') {
                $('#vehicle_type').after(
                    '<small class="error-message text-danger">Please select a vehicle type.</small>');
                isValid = false;
            }
            if (vehicle_master_id === '') {
                $('#vehicle_master_id').after(
                    '<small class="error-message text-danger">Please select a vehicle.</small>');
                isValid = false;
            }
            if (amc_package_type_id === '') {
                $('#amc_package_type_id').after(
                    '<small class="error-message text-danger">Please select package.</small>');
                isValid = false;
            }
            if (contact_number === '') {
                $('#contact_number').after(
                    '<small class="error-message text-danger">Mobile number is required.</small>');
                isValid = false;
            } else if (!/^\d{10}$/.test(contact_number)) {
                $('#contact_number').after(
                    '<small class="error-message text-danger">Enter a valid 10-digit mobile number.</small>');
                isValid = false;
            }

            if (customer_name === '') {
                $('#customer_name').after(
                    '<small class="error-message text-danger">Customer name is required.</small>');
                isValid = false;
            }

            if (isValid) {
                loaderButton('addUpdateAmcMaster', true);
                $('form[name="amcMasterForm"]').submit();
            }
        });


        function calculateAmcEndDate() {
            let timePeriod = parseInt($('#amc_package_type_id option:selected').data('time-period'));
            let startDateStr = $('#amc_start_date').val();

            if (timePeriod && startDateStr) {
                let [day, month, year] = startDateStr.split('-');
                let startDate = new Date(`${year}-${month}-${day}`);

                if (isNaN(startDate.getTime())) return;

                let endDate = new Date(startDate.setMonth(startDate.getMonth() + timePeriod));

                // Format as dd-mm-yyyy
                let formatted = ("0" + endDate.getDate()).slice(-2) + "-" +
                    ("0" + (endDate.getMonth() + 1)).slice(-2) + "-" +
                    endDate.getFullYear();

                $('#amc_end_date').val(formatted);
            } else {
                $('#amc_end_date').val('');
            }
        }

        function checkChassisNumber(){
            $.ajax({
                url: "{{ route('amc-master.get-amc-package-master') }}",
                method: 'GET',
                data: {
                    type_id: type_id,

                },
                success: function(response) {
                    if (response.code == '1') {
                        const packages = response.data.amcPackageMaster;

                        $.each(packages, function(index, item) {
                            const text =
                                `${item.service_count} Services`;
                            dropdown.append(
                                `<option value="${item.id}" data-time-period="${item.time_period}">${text}</option>`
                            );
                        });
                        $('#amc_package_type_id').trigger('change');
                    }

                },
                error: function(xhr, status, error) {
                    console.error("Error fetching chart data:", error);
                }
            });
        }

        $(document).on('change',  '#amc_package_type_id', calculateAmcEndDate);

        $(document).on('change', '#amc_start_date', calculateAmcEndDate);

        $(document).on('change', '#chassis_number', function() {
            let chassis_number = $(this).val();
            const dropdown = $('#amc_package_type_id');
            dropdown.empty();
            $.ajax({
                url: "{{ route('amc-master.check-chassis-number') }}",
                method: 'GET',
                data: {
                    chassis_number: chassis_number,

                },
                success: function(response) {
                    if (response.code == '1') {
                        showToast('error', response.message);
                        $(this).focus();
                        $(this).val('');
                        return false;
                    }else{

                    }

                },
                error: function(xhr, status, error) {
                    console.error("Error fetching chart data:", error);
                }
            });
        });
    </script>
@endsection
