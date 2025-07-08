@extends('admin.master-main')
@section('title', ucfirst(Auth::user()->getRoleNames()->first()." "."Portal"))
@section('content')

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                      <h3 class="mb-0">Assigned Vehicles List</h3>
                      @can('assign vehicle status')
                        <a href="{{ route('status.form') }}" class="btn btn-primary">
                            Assign Vehicles
                        </a>
                      @endcan
                    </div>
                  </div>
                </div>
            </div>
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                        <thead>
                          <tr>
                            <th>S.no</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            @if(auth()->user()->can('vehicle update status') || auth()->user()->can('vehicle remove status'))
                                <th>Action</th>
                            @endif
                          </tr>
                        </thead>
                        <tbody>
                          @php $number=1; @endphp
                            @foreach ($vehicles as $item)
                            <tr>
                                <td>{{ $number }}.</td>
                                <td>{{ $item->number_plate }} | {{ $item->vehicle_name ?? $item->temp_vehicle_detail }}</td>
                                <td>{{ $item->vehiclestatus->name ?? '-' }}</td>
                                @if(auth()->user()->can('vehicle update status') || auth()->user()->can('vehicle remove status'))
                                <td>
                                    @can('vehicle update status')
                                        <a href='{{ url("vehicle-assigned/".$item->id."/edit") }}' class="btn btn-warning btn-sm"><i class="far fa-edit"></i> Edit</a>
                                    @endcan
                                    @can('vehicle remove status')
                                        <form action="{{ url("vehicle-assigned/".$item->id."/delete") }}" method="POST" style="display:inline;" class="delete-form">
                                            {{-- @csrf --}}
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i> Remove</button>
                                        </form>
                                    @endcan
                                </td>
                                @endif
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


@section('script')
    <script type="text/javascript">
      document.addEventListener('DOMContentLoaded', function () {
          const deleteButtons = document.querySelectorAll('.delete-confirm');
          deleteButtons.forEach(button => {
              button.addEventListener('click', function (e) {
                  e.preventDefault(); // Stop form submit
                  const form = this.closest('form');
                  Swal.fire({
                      title: 'Are you sure?',
                      text: "You won't be able to revert this!",
                      icon: 'warning',
                      showCancelButton: true,
                      confirmButtonColor: '#d33',
                      cancelButtonColor: '#3085d6',
                      confirmButtonText: 'Yes, delete it!'
                  }).then((result) => {
                      if (result.isConfirmed) {
                          form.submit();
                      }
                  });
              });
          });
      });
  </script>

@endsection
