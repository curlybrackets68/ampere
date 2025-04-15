@extends('admin.master')

@section('title')
    AMPERE | Module
@endsection

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Modules</h5>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary" id="addNewModule">Add New</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-hover" style="width:100%" id="moduleTable">
                                <thead>
                                    <tr>
                                        <th style="text-align: left;">Sr. No</th>
                                        <th style="text-align: left;">Name</th>
                                        <th style="text-align: left;">Config Key</th>
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

    <div class="modal fade" id="addModuleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title module-title" id="exampleModalLabel">Add Module</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" id="moduleId">
                        <div class="col-md-12">
                            <label>Name</label>
                            <input type="text" id="moduleName" class="form-control">
                        </div>
                        <div class="col-md-12 mt-3">
                            <label>Config Key</label>
                            <input type="text" id="configKey" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveModuleBtn">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        $(document).ready(function () {
            moduleList();
        });

        function moduleList() {
            $('#moduleTable').DataTable({
                serverSide: true,
                processing: true,
                destroy: true,
                responsive: true,
                ajax: {
                    url: '{{ route('admin.modules') }}',
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
                    data: 'config_key',
                    name: 'config_key'
                },
                {
                    data: 'action',
                    name: 'action'
                }
                ],
                order: [
                    [0, 'asc']
                ],
                createdRow: function (row, data, index) {
                    $('td', row).eq(0).css('text-align', 'left');
                    $('td', row).eq(1).css('text-align', 'left');
                    $('td', row).eq(2).css('text-align', 'left');
                    $('td', row).eq(3).css('text-align', 'left');
                },
            });
        }

        $(document).on('click', '.edit-module', function () {
            $('.module-title').text('Edit Module');
            let id = $(this).data('id');
            let name = $(this).data('name');
            let config = $(this).data('config');

            $('#moduleId').val(id);
            $('#moduleName').val(name);
            $('#configKey').val(config);

            $('#addModuleModal').modal('show');
        });

        $(document).on('click', '#addNewModule', function () {
            $('.module-title').text('Add Module');
            $('#addModuleModal').modal('show');
        });

        $(document).on('click', '#saveModuleBtn', function () {
            let moduleId = $('#moduleId').val();
            let moduleName = $('#moduleName').val();
            let configKey = $('#configKey').val();

            if (!moduleName || !configKey) {
                alert('Please fill all fields.');
                return;
            }

            let data = {
                _token: '{{ csrf_token() }}',
                moduleId: moduleId ? moduleId : 0,
                moduleName: moduleName,
                configKey: configKey,
            };

            $.ajax({
                url: '{{ route('admin.add-edit-modules') }}',
                method: 'POST',
                data: data,
                success: function (response) {
                    if (response.status == '1') {
                        $('#addModuleModal').modal('hide');
                        moduleList();
                        showToast('success', response.msg);
                    }
                },
                error: function (xhr) {
                    alert('Update failed');
                }
            });

        });

        $('#addModuleModal').on('hidden.bs.modal', function (e) {
            $('#moduleId').val('');
            $('#moduleName').val('');
            $('#configKey').val('');
        });
    </script>
@endsection