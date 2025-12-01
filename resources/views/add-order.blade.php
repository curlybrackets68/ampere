@extends('master')

@section('title')
    Add Order | AMPERE
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
                    <form name="inquiryForm" action="{{route('orders.orders-save')}}" method="post">
                        @csrf

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">Add Order</h5>
                                <div class="card-tools">

                                </div>

                            </div>
                            <div class="card-body">
                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" class="form-control" id="customer_name"
                                                placeholder="Enter Name" name="customer_name">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Mobile Number</label>
                                            <input type="text" class="form-control" id="customer_mobile"
                                                placeholder="Enter Mobile Number" name="customer_mobile"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '');" maxlength="10">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Vehicle Number</label>
                                            <input type="text" class="form-control" id="customer_vehicle_no"
                                                placeholder="Enter Vehicle Number" name="customer_vehicle_no">
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
                                            <label>Part Name</label>
                                            <input type="text" class="form-control" id="order_name"
                                                placeholder="Enter Part Name" name="order_name">
                                        </div>
                                    </div>


                                </div>


                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-sm"
                                    id="addOrder">Submit</button>
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

        $(document).on('click', '#addOrder', function(e) {
            e.preventDefault();

            $('.error-message').remove();
            let customer_name = $('#customer_name').val();
            let customer_mobile = $('#customer_mobile').val();
            let customer_vehicle_no = $('#customer_vehicle_no').val();
            let branch_id = $('#branch_id').val();
            let order_name = $('#order_name').val();

            let isValid = true;

            if (customer_name === '') {
                $('#customer_name').after(
                    '<small class="error-message text-danger">Customer name is required.</small>');
                isValid = false;
            }
            if (customer_mobile === '') {
                $('#customer_mobile').after(
                    '<small class="error-message text-danger">Customer mobile is required.</small>');
                isValid = false;
            } else if (!/^\d{10}$/.test(customer_mobile)) {
                $('#customer_mobile').after(
                    '<small class="error-message text-danger">Enter a valid 10-digit mobile number.</small>');
                isValid = false;
            }



            if (customer_vehicle_no === '') {
                $('#customer_vehicle_no').after(
                    '<small class="error-message text-danger">Vehicle number is required.</small>');
                isValid = false;
            }

            if (branch_id === '') {
                $('#branch_id').after(
                    '<small class="error-message text-danger">Branch is required.</small>');
                isValid = false;
            }if (order_name === '') {
                $('#order_name').after(
                    '<small class="error-message text-danger">Order name is required.</small>');
                isValid = false;
            }



            if (isValid) {
                loaderButton('addOrder', true);
                $('form[name="inquiryForm"]').submit();
            }
        });




    </script>
@endsection
