@extends('admin.master')

@section('title')
    AMPERE | Rights
@endsection

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Assign Rights to <span class="fw-bold">{{ $user->user_name }}</span></h5>
                        </div>
                        <div class="card-body">
                            <form id="userRightsForm" action="{{ route('admin.save-rights') }}" method="post">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-center align-middle">
                                        <thead>
                                            <tr>
                                                <th>
                                                    All<br>
                                                    <input type="checkbox" id="selectAllAll">
                                                </th>
                                                <th>Module</th>
                                                <th>
                                                    Add<br>
                                                    <input type="checkbox" class="select-all-column" data-type="add">
                                                </th>
                                                <th>
                                                    Edit<br>
                                                    <input type="checkbox" class="select-all-column" data-type="edit">
                                                </th>
                                                <th>
                                                    Delete<br>
                                                    <input type="checkbox" class="select-all-column" data-type="delete">
                                                </th>
                                                <th>
                                                    View<br>
                                                    <input type="checkbox" class="select-all-column" data-type="view">
                                                </th>
                                                <th>
                                                    View All<br>
                                                    <input type="checkbox" class="select-all-column" data-type="view_all">
                                                </th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($modules as $module)
                                                @php $right = $rights[$module->id] ?? null; @endphp
                                                <tr>
                                                    <td><input type="checkbox" class="select-all-row"
                                                            data-id="{{ $module->id }}">
                                                    </td>
                                                    <td>{{ $module->name }}</td>
                                                    <td>
                                                        <input type="checkbox" name="permissions[]"
                                                            value="add_{{ $module->id }}"
                                                            class="checkbox-add checkbox-{{ $module->id }}"
                                                            {{ $right && $right->role_add ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" name="permissions[]"
                                                            value="edit_{{ $module->id }}"
                                                            class="checkbox-edit checkbox-{{ $module->id }}"
                                                            {{ $right && $right->role_edit ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" name="permissions[]"
                                                            value="delete_{{ $module->id }}"
                                                            class="checkbox-delete checkbox-{{ $module->id }}"
                                                            {{ $right && $right->role_delete ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" name="permissions[]"
                                                            value="view_{{ $module->id }}"
                                                            class="checkbox-view checkbox-{{ $module->id }}"
                                                            {{ $right && $right->role_view ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" name="permissions[]"
                                                            value="viewAll_{{ $module->id }}"
                                                            class="checkbox-view_all checkbox-{{ $module->id }}"
                                                            {{ $right && $right->role_viewAll ? 'checked' : '' }}>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">Save Rights</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            // Select all by column (vertical)
            $('.select-all-column').on('change', function() {
                const type = $(this).data('type');
                $('.checkbox-' + type).prop('checked', this.checked);
                updateMasterSelectAll();
            });

            // Select all by row (horizontal)
            $('.select-all-row').on('change', function() {
                const id = $(this).data('id');
                $('.checkbox-' + id).prop('checked', this.checked);
                updateMasterSelectAll();
            });

            // Master Select All
            $('#selectAllAll').on('change', function() {
                $('input[type="checkbox"]').not(this).prop('checked', this.checked);
            });

            // When any individual checkbox is clicked
            $('input[type="checkbox"]').not('#selectAllAll, .select-all-row, .select-all-column').on('change',
                function() {
                    updateRowCheckboxes();
                    updateColumnCheckboxes();
                    updateMasterSelectAll();
                });

            function updateRowCheckboxes() {
                $('.select-all-row').each(function() {
                    const id = $(this).data('id');
                    const checkboxes = $('.checkbox-' + id);
                    const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
                    $(this).prop('checked', allChecked);
                });
            }

            function updateColumnCheckboxes() {
                $('.select-all-column').each(function() {
                    const type = $(this).data('type');
                    const checkboxes = $('.checkbox-' + type);
                    const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
                    $(this).prop('checked', allChecked);
                });
            }

            function updateMasterSelectAll() {
                const allCheckboxes = $('input[type="checkbox"]').not('#selectAllAll');
                const allChecked = allCheckboxes.length === allCheckboxes.filter(':checked').length;
                $('#selectAllAll').prop('checked', allChecked);
            }

            // ✅ INIT: Sync checkboxes on page load
            updateRowCheckboxes();
            updateColumnCheckboxes();
            updateMasterSelectAll();
        });
    </script>
@endsection
