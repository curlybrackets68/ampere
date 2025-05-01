@extends('master')

@section('title')
    Add Update AMC Service | AMPERE
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
                    <form name="amcMasterForm" action="{{ route('amc-master-service.handle') }}" enctype="multipart/form-data"
                        method="post">
                        @csrf

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">
                                    {{ isset($amcMaster) ? 'Update AMC Master' : 'Add AMC Service Details' }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="chassis_number"
                                                placeholder="Enter Chassis Number" name="chassis_number" value="">
                                            <span class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="getServiceData">Get
                                                    Service</button>
                                            </span>

                                        </div>
                                        <div id="errorContainer" style="color: red; display: none;"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <strong>Contract Number</strong><br>
                                            <label id="amcContractId" class="form-control-static"></label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <strong>Customer Name</strong><br>
                                            <label id="amcCustomerName" class="form-control-static"></label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <strong>Customer Contact</strong><br>
                                            <label id="amcCustomerNumber" class="form-control-static"></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <input type="hidden" value="" id="amc_id" name="amc_id" />
                                    <input type="hidden" value="" id="service_id" name="service_id" />
                                    <div class="col-md-3 d-none">
                                        <label>Status</label>
                                        <select class="form-select" id="status_id" name="status_id">
                                            <option value="2">Completed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <strong>Service No</strong><br>
                                            <label id="amcServiceNo" class="form-control-static"></label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Attachment</label>
                                        <input type="file" id="myFile" name="filename" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Remark</label>
                                        <textarea class="form-control" rows="2" id="service_remark" placeholder="Enter Status Remark"
                                            name="service_remark"></textarea>
                                    </div>
                                    <div class="col-md-3 mt-5">
                                        <button type="submit" class="btn btn-primary btn-sm"
                                            id="addUpdateAmcMaster">Submit</button>
                                        <button type="reset" class="btn btn-light btn-sm">Cancel</button>
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
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" style="width:100%"
                                            id="amcMasterSeriveTable">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: left;">Sr. No</th>
                                                    <th style="text-align: left;">Service Date</th>
                                                    <th style="text-align: left;">Customer Number</th>
                                                    <th style="text-align: left;">Remark</th>
                                                    <th style="text-align: left;">Attchment</th>
                                                    <th style="text-align: left;">Service By</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
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
        $(document).on('click', '#getServiceData', function() {
            let chassis_number = $('#chassis_number').val();
            getserviceForUpdateStatus(chassis_number);
            //  getserviceList(chassis_number);

        });

        function getserviceForUpdateStatus(chassis_number) {
            $.ajax({
                url: "{{ route('amc-master-service.get-service-details-by-chassis-number') }}",
                method: 'GET',
                data: {
                    chassis_number: chassis_number,
                },
                success: function(response) {
                    if (response.code == '1') {


                        let serviceFlag = response.data.serviceFlag;
                        let serveiceDataList = response.data.serveiceDataList;


                        if (serviceFlag) {
                            let serveiceData = response.data.serveiceData;
                            let amc_master_details = serveiceData.amc_master_details;
                            $('#amcContractId').html(amc_master_details.amc_display_number);
                            $('#amcCustomerName').html(amc_master_details.customer_name);
                            $('#amcCustomerNumber').html(amc_master_details.contact_number);
                            $('#amcServiceNo').html(serveiceData.service_no);
                            $('#amc_id').val(amc_master_details.id);
                            $('#service_id').val(serveiceData.id);


                        } else {
                            showToast('error', 'Sorry no pending service');
                        }
                        $('#amcMasterSeriveTable tbody').html('');
                        $.each(serveiceDataList, function(index, item) {
                            $('#amcMasterSeriveTable tbody').append(`
                                <tr>
                                    <td>${index + 1}</td> 
                                    <td>${item.display_service_date}</td>
                                    <td>${item.amc_master_details.customer_name}</td>
                                    <td>${item.service_remark ?? ''}</td>
                                    <td>
                                        ${item.attachment_url ? `
                                                    <a href="${item.attachment_url}" download target="_blank" class="btn btn-sm btn-primary">
                                                        <i class="fa fa-download"></i> Download
                                                    </a>
                                                ` : ''}
                                    </td>
                                    <td>${item.service_by ?? ''}</td>
                                </tr>
                                `);
                        });
                    } else {
                        $('#amcContractId').html('');
                        $('#amcCustomerName').html('');
                        $('#amcCustomerNumber').html('');
                        $('#amcServiceNo').html('');
                        $('#amc_id').val('');
                        $('#service_id').val('');
                        $('#amcMasterSeriveTable tbody').html('')
                    }

                },
                error: function(xhr, status, error) {
                    console.error("Error fetching chart data:", error);
                }
            });
        }

        function getserviceList(chassis_number) {
            let url = '{{ route('orders.get-history', ['type_id' => 'ID']) }}';
            url = url.replace('ID', type_id);
            $('#orderHistoryTable').DataTable({
                serverSide: false,
                processing: true,
                destroy: true,
                responsive: true,
                scrollX: true,
                ajax: {
                    url: url,
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'id',
                        searchable: false
                    },
                    {
                        data: 'display_action',
                        name: 'display_action'
                    }, {
                        data: 'display_date',
                        name: 'created_at'
                    }, {
                        data: 'remark',
                        name: 'remark'
                    }, {
                        data: 'created_by_name',
                        name: 'created_by_name'
                    }

                ],
                order: [
                    [0, 'desc']
                ],

            });
        }
    </script>
@endsection
