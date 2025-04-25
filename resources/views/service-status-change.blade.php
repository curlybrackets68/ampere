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
                      
                        method="post">
                        @csrf
                      
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
                                                value="">
                                        </div>
                                    </div>
                                  
                                </div>
                           

                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">AMC Service List</h5>
                                

                            </div>
                            <div class="card-body">
                                <div class="row mt-3">
                                    <table class="table table-bordered table-hover" style="width:100%" id="amcMasterTable">
                                        <thead>
                                            <tr>
                                                <th style="text-align: left;">Sr. No</th>
                                                <th style="text-align: left;">Customer Name</th>
                                                <th style="text-align: left;">Customer Number</th>
                                                <th style="text-align: left;">AMC Number</th>
                                                <th style="text-align: left;">AMC Start Date</th>
                                                <th style="text-align: left;">AMC End Date</th>
                                                <th style="text-align: left;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
    
                                        </tbody>
                                    </table>
                           

                            </div>
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
      
        $(document).on('change', '#chassis_number', function() {
            let chassis_number = $(this).val();
           
            $.ajax({
                url: "{{ route('amc-master-service.get-service-details-by-chassis-number') }}",
                method: 'GET',
                data: {
                    chassis_number: chassis_number,

                },
                success: function(response) {
                    if (response.code == '1') {
                      
                    }

                },
                error: function(xhr, status, error) {
                    console.error("Error fetching chart data:", error);
                }
            });
        });

    
    </script>
@endsection
