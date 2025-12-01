@extends('master')

@section('title')
    Add Inquiry | AMPERE
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
                    <form name="inquiryForm" action="{{ route('inquiry.save-inquiry') }}" method="post">
                        @csrf

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">Add Inquiry</h5>
                                <div class="card-tools">

                                </div>

                            </div>
                            <div class="card-body">
                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" class="form-control" id="name"
                                                placeholder="Enter Name" name="name">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Mobile Number</label>
                                            <input type="text" class="form-control" id="mobile"
                                                placeholder="Enter Mobile Number" name="mobile"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '');" maxlength="10">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Vehicle Number</label>
                                            <input type="text" class="form-control" id="vehicle_no"
                                                placeholder="Enter Vehicle Number" name="vehicle_no">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Branch</label>
                                            <select class="form-select" name="branch_id" id="branch_id">
                                                <option value="">Select Branch</option>
                                                @forelse (@$branches as $key => $value)
                                                    <option value="{{ $key }}">
                                                        {{ $value }}</option>
                                                @empty
                                                @endforelse

                                            </select>
                                        </div>

                                    </div>
                                </div>
                                <div class="row mt-2">

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Service Type</label>
                                            <select class="form-select" name="service_type_id" id="service_type_id">
                                                <option value="">Select Service Type</option>
                                                @forelse (@$serviceTypes as $key => $value)
                                                    <option value="{{ $key }}">
                                                        {{ $value }}</option>
                                                @empty
                                                @endforelse

                                            </select>
                                        </div>

                                    </div>


                                </div>


                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-sm"
                                    id="addInquiry">Submit</button>
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
                console.log(amcMaster)
                $('#vehicle_type').trigger('change');
                flatpickr("#amc_start_date", {
                    dateFormat: "Y-m-d", // parse backend date
                    altInput: true, // show pretty format
                    altFormat: "d-m-Y", // display format
                    defaultDate: amcMaster.amc_start_date
                });

                flatpickr("#amc_end_date", {
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "d-m-Y",
                    defaultDate: amcMaster.amc_end_date
                })
            } else {
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
            }

        });

        function cleanDate(dateStr) {
            if (!dateStr) return null;
            return dateStr.split(" ")[0]; // take only YYYY-MM-DD
        }

        $(document).on('change', '#vehicle_master_id', function() {
            let type_id = $('#vehicle_type').val();
            let vehicle_master_id = $("#vehicle_master_id").val();
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

                            if (vehicle_master_id == '4') {
                                // TVS King EV Max  16 service
                                if (item.service_count == 16) {
                                    var text =
                                        `${item.service_count} Services`;
                                    dropdown.append(
                                        `<option value="${item.id}" data-time-period="${item.time_period}" ${selected} data-amount="${item.price}">${text}</option>`
                                    );
                                }

                            } else if (vehicle_master_id == '5') {
                                // TVS King Duramax Plus
                                if (item.service_count == 11) {
                                    var text =
                                        `${item.service_count} Services`;
                                    dropdown.append(
                                        `<option value="${item.id}" data-time-period="${item.time_period}" ${selected} data-amount="${item.price}">${text}</option>`
                                    );
                                }

                            } else if (vehicle_master_id == '6') {
                                // TVS King Deluxe
                                if (item.service_count == 15) {
                                    var text =
                                        `${item.service_count} Services`;
                                    dropdown.append(
                                        `<option value="${item.id}" data-time-period="${item.time_period}" ${selected} data-amount="${item.price}">${text}</option>`
                                    );
                                }

                            } else {
                                if (item.service_count <= 9) {
                                    var text =
                                        `${item.service_count} Services`;
                                    dropdown.append(
                                        `<option value="${item.id}" data-time-period="${item.time_period}" ${selected} data-amount="${item.price}">${text}</option>`
                                    );
                                }
                            }


                        });
                        $('#amc_package_type_id').trigger('change');
                    }

                },
                error: function(xhr, status, error) {
                    console.error("Error fetching chart data:", error);
                }
            });
        });

        $(document).on('click', '#addInquiry', function(e) {
            e.preventDefault();

            $('.error-message').remove();
            let name = $('#name').val();
            let mobile = $('#mobile').val();
            let vehicle_no = $('#vehicle_no').val();
            let branch_id = $('#branch_id').val();
            let service_type_id = $('#service_type_id').val();
            let customer_name = $('#customer_name').val();
            let amc_basic_price = $('#amc_basic_price').val();
            let payment_type = $('#payment_type').val();
            let amc_start_km = $('#amc_start_km').val();

            let isValid = true;

            if (name === '') {
                $('#name').after(
                    '<small class="error-message text-danger">Name is required.</small>');
                isValid = false;
            }
            if (mobile === '') {
                $('#mobile').after(
                    '<small class="error-message text-danger">Mobile Number is required.</small>');
                isValid = false;
            } else if (!/^\d{10}$/.test(mobile)) {
                $('#mobile').after(
                    '<small class="error-message text-danger">Enter a valid 10-digit mobile number.</small>');
                isValid = false;
            }
            if (vehicle_no === '') {
                $('#vehicle_no').after(
                    '<small class="error-message text-danger">Vehicle Number is required.</small>');
                isValid = false;
            }
            if (branch_id === '') {
                $('#branch_id').after(
                    '<small class="error-message text-danger">Please select branch.</small>');
                isValid = false;
            }
            if (service_type_id === '') {
                $('#service_type_id').after(
                    '<small class="error-message text-danger">Service Type is required.</small>');
                isValid = false;
            }



            if (isValid) {
                loaderButton('addInquiry', true);
                $('form[name="inquiryForm"]').submit();
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

        $(document).on('change', '#amc_package_type_id', function() {
            calculateAmcEndDate();
            let timePeriod = parseInt($(this).find(':selected').data('time-period'));
            let amount = parseInt($(this).find(':selected').data('amount'));
            if (timePeriod) {
                $('#amc_start_date').attr('max', moment().add(timePeriod, 'months').format('DD-MM-YYYY'));
            } else {
                $('#amc_start_date').removeAttr('max');
            }
            $('#amc_basic_price').val(amount);
        });

        $(document).on('change', '#amc_start_date', calculateAmcEndDate);

        $(document).on('change', '#chassis_number', function() {
            let chassis_number = $(this).val();

            $.ajax({
                url: "{{ route('amc-master.check-chassis-number') }}",
                method: 'GET',
                data: {
                    chassis_number: chassis_number,

                },
                success: function(response) {
                    if (response.code == '1') {
                        showToast('error', response.message);
                        $('#chassis_number').after(
                            '<small class="error-message text-danger">Chassis number already exist,Please enter new.</small>'
                        );
                        $(this).focus();
                        $(this).val('');
                        $('#addInquiry').attr('disabled', true);
                        return false;
                    } else {
                        $('#addInquiry').attr('disabled', false);
                        // Remove any existing error message
                        $('#chassis_number').next('.error-message').remove();
                    }

                },
                error: function(xhr, status, error) {
                    console.error("Error fetching chart data:", error);
                }
            });
        });
    </script>
@endsection
