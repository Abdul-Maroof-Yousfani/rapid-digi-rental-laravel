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
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" style="width:100%;">
                        <thead>
                          <tr>
                            <th>S no.</th>
                            <th>Roles</th>
                            <th>Permissions</th>
                            <th>assign</th>
                          </tr>
                        </thead>
                        <tbody>
                            @php $number= 1; @endphp
                            @foreach ($roles as $item)
                            <tr>
                                <td>{{ $number }}.</td>
                                <td>{{ $item->name }}</td>
                                <td>
                                    @if($item->permissions->count() > 0)
                                        @foreach($item->permissions as $index => $perm)
                                            <i class="fas fa-check text-success me-1"></i>&nbsp;{{ $perm->name }}&nbsp;
                                            @if(($index + 1) % 5 == 0)
                                                <br> {{-- 👈 Line break after every 5 permissions --}}
                                            @endif
                                        @endforeach
                                    @else
                                        <span class="text-muted">No permissions assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ url('assign-permission/'.$item->id) }}" class="btn btn-warning btn-sm">
                                        <i class="far fa-edit"></i> Assign
                                    </button>
                                </td>
                            </tr>
                            @php $number++; @endphp
                            @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
    </div>
@endsection
