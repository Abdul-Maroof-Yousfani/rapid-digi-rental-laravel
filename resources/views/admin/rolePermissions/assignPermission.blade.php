@extends('admin.master-main')

@section('content')


      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                      <h3 class="mb-0">Roles</h3>
                    </div>
                  </div>
                </div>
            </div>
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                      <form action="{{ url('assign-permission/'. $role->id) }}" method="post">
                        @csrf
                          <table>
                              <tbody>
                                  <tr>
                                      <td colspan="2">
                                          <div class="form-group">
                                            <label class="">Role </label>
                                            <input type="text" class="form-control w-30" value="{{ $role->name }}" id="" readonly>
                                            <input type="hidden" value="{{ $role->id }}" name="role_id">
                                          </div>
                                      </td>
                                  </tr>
                                  <tr>
                                    <td colspan="2">
                                        <label><strong>Assign Permissions</strong></label>
                                        <div class="row">
                                            @foreach($permissions as $permission)
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input"
                                                            type="checkbox"
                                                            name="permissions[]"
                                                            value="{{ $permission->name }}"
                                                            id="perm_{{ $permission->id }}"
                                                            {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                        <label for="perm_{{ $permission->id }}" class="form-check-label">
                                                            {{ $permission->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                  </tr>
                              </tbody>
                          </table><br>
                          <input type="submit" name="submit" class="btn btn-primary" value="Update">
                      </form>
                  </div>
                </div>
              </div>
            </div>



           </div>
        </section>
    </div>
@endsection
