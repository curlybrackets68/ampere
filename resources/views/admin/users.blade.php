@extends('admin.master')

@section('title')
    AMPERE | Users
@endsection

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Users</h5>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary" id="addNewUser">Add New</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-hover" style="width:100%" id="userTable">
                                <thead>
                                    <tr>
                                        <th style="text-align: left;">Sr. No</th>
                                        <th style="text-align: left;">Name</th>
                                        <th style="text-align: left;">Mobile</th>
                                        <th style="text-align: left;">Email</th>
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

    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" id="userId">
                        <div class="col-md-12">
                            <label>Full Name</label>
                            <input type="text" id="user_name" class="form-control">
                        </div>
                        <div class="col-md-12 mt-3">
                            <label>Short Name (Login Name)</label>
                            <input type="text" id="name" class="form-control">
                        </div>
                        <div class="col-md-12 mt-3" id="passwordDiv">
                            <label>Password</label>
                            <input type="text" id="password" class="form-control">
                        </div>
                        <div class="col-md-12 mt-3">
                            <label>Mobile</label>
                            <input type="text" id="mobile" class="form-control">
                        </div>
                        <div class="col-md-12 mt-3">
                            <label>Email</label>
                            <input type="text" id="email" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveUserBtn">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>

        $(document).on("keyup", ".form-control", function () {
            $(this).removeClass("error-message");
            $(this).next(".error-message").remove();
        });

        $(document).ready(function () {
            userList();
        });

        function userList() {
            $('#userTable').DataTable({
                serverSide: true,
                processing: true,
                destroy: true,
                responsive: true,
                ajax: {
                    url: '{{ route('admin.users') }}',
                },
                columns: [{
                    data: 'DT_RowIndex',
                    name: 'id',
                    searchable: false
                },
                {
                    data: 'user_name',
                    name: 'user_name'
                },
                {
                    data: 'mobile',
                    name: 'mobile'
                },
                {
                    data: 'email',
                    name: 'email'
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

        $(document).on('click', '#addNewUser', function () {
            $('#passwordDiv').removeClass('d-none');
            $('#addUserModal').modal('show');
        });

        $(document).on('click', '#saveUserBtn', function () {
            let userId = $('#userId').val();
            let fullName = $('#user_name').val();
            let shortName = $('#name').val();
            let password = $('#password').val();
            let mobile = $('#mobile').val();
            let email = $('#email').val();

            let isValid = true;

            if (fullName == '') {
                $('#user_name').after('<small class="error-message text-danger">Full Name is required.</small>');
                isValid = false;
            }
            if (shortName == '') {
                $('#name').after('<small class="error-message text-danger">Short Name is required.</small>');
                isValid = false;
            }
            if (userId == '' && password == '') {
                $('#password').after('<small class="error-message text-danger">Password is required.</small>');
                isValid = false;
            }
            if (mobile == '') {
                $('#mobile').after('<small class="error-message text-danger">Mobile is required.</small>');
                isValid = false;
            }

            let data = {
                _token: '{{ csrf_token() }}',
                userId: userId,
                fullName: fullName,
                shortName: shortName,
                password: password,
                mobile: mobile,
                email: email,
            };

            if (isValid) {
                $.ajax({
                    url: '{{ route('admin.add-edit-user') }}',
                    method: 'POST',
                    data: data,
                    success: function (response) {
                        if (response.status == '1') {
                            $('#addUserModal').modal('hide');
                            userList();
                        }
                    },
                    error: function (xhr) {
                        alert('Update failed');
                    }
                });
            }
        });

        $(document).on('click', '.edit-user', function () {
            let id = $(this).data('id');

            let url = '{{ route('admin.edit-user', ['id' => 'ID']) }}';
            url = url.replace('ID', id);

            $.ajax({
                url: url,
                method: 'GET',
                success: function (response) {
                    $('#addUserModal').modal('show');
                    $('#userId').val(id);
                    $('#user_name').val(response.user_name);
                    $('#name').val(response.name);
                    $('#passwordDiv').addClass('d-none');
                    $('#mobile').val(response.mobile);
                    $('#email').val(response.email);
                },
                error: function (xhr) {
                    alert('failed');
                }
            });
        });
    </script>
@endsection