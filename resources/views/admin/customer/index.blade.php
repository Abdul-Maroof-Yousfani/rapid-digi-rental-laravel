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
                            <h3 class="mb-0">Customer List</h3>
                            <span>
                                <a href="{{ auth()->user()->hasRole('admin') ? route('admin.customer.create') : route('booker.customer.create') }}" class="btn btn-primary">
                                    Add Customer
                                </a>&nbsp;&nbsp;&nbsp;
                                <a href="{{ auth()->user()->hasRole('admin') ? role_base_route('syncCustomersFromZoho') : role_base_route('syncCustomersFromZoho') }}" class="btn btn-primary {{ $shouldEnableSync ? '' : 'disabled pointer-events-none opacity-50' }}" >
                                    Sync From Zoho
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                    <div class="card-body">
                        <form class="filterForm">
                            <div class="row">
                                <div class="col-2">
                                    <label for="">From Date</label>
                                    <input type="date" class="form-control" id="fromDate">
                                </div>
                                <div class="col-2">
                                    <label for="">.</label>
                                    <input type="text" placeholder="Between" class="form-control" disabled>
                                </div>
                                <div class="col-2">
                                    <label for="">To Date</label>
                                    <input type="date" class="form-control" id="toDate">
                                </div>
                                <div class="col-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary" onclick="filterdata()">
                                        Filter Data
                                    </button>
                                </div>
                            </div><br>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover" /*id="tableExport"*/ style="width:100%;">
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
                                <tbody id="customerList">
                                    @php $number=1; @endphp
                                    @foreach ($customers as $item)
                                    <tr>
                                        <td>{{ $number }}.</td>
                                        <td>{{ $item->customer_name }}</td>
                                        <td>{{ $item->email }}</td>
                                        <td>{{ $item->phone }}</td>
                                        <td>{{ $item->cnic }}</td>
                                        <td>{{ $item->status==1 ? 'Active' : 'Inactive' }}</td>
                                        <td>
                                            <a href='@can('manage customers') {{ role_base_url("customer/".$item->id."/edit") }} @endcan' class="btn btn-warning btn-sm"><i class="far fa-edit"></i> Edit</a>
                                            <form action="{{ auth()->user()->hasRole('admin') ? url('admin/customer/'.$item->id) : url('booker/customer/'.$item->id) }}" method="POST" style="display:inline;" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i>
                                                    Delete
                                                </button>
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

        function filterdata(){
            let fromDate= $('#fromDate').val();
            let toDate= $('#toDate').val();
            let data= { fromDate : fromDate , toDate : toDate };
            if (!fromDate || !toDate) {
                alert('Please select both dates.');
                return;
            }
            $.ajax({
                url: '/booker/getCustomerList',
                method: 'get',
                data: data,
                success:function(response){
                    $('#customerList').html(response);
                }
            });
        }


    </script>

@endsection
