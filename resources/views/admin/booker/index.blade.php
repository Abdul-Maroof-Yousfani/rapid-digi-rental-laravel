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
                      <h3 class="mb-0">Booking User List</h3>
                      <a href="{{ route('booker.create') }}" class="btn btn-primary">
                        Add Booking user
                      </a>
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>CNIC</th>
                            <th>Status</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          @php $number=1; @endphp
                            @foreach ($bookers as $item)
                            <tr>
                                <td>{{ $number }}.</td>
                                <td>{{ $item->user->name }}</td>
                                <td>{{ $item->user->email }}</td>
                                <td>{{ $item->phone }}</td>
                                <td>{{ $item->cnic }}</td>
                                <td>{{ $item->status==1 ? 'Active' : 'Inactive' }}</td>
                                <td>
                                    <a href='{{ url("booker/".$item->id."/edit") }}' class="btn btn-warning btn-sm"><i class="far fa-edit"></i> Edit</a>
                                    <form action="{{ url('booker/'.$item->id) }}" method="POST" style="display:inline;" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i> Delete</button>
                                    </form>
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
