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
                        <h3 class="mb-0">Invoices for Booking #{{$booking->id}}</h3>
                        <a href="{{ url('booker/booking/'.$booking->id.'/create-invoice') }}" class="btn btn-primary">
                            Create Invoice
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
                            <th>S. No</th>
                            <th>Invoice No</th>
                            <th>Total Price</th>
                            <th>Date</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                            @php $number=1; @endphp
                            @foreach ($invoice as $item)
                            <tr>
                                <td>{{ $number }}.</td>
                                <td>{{ $item->zoho_invoice_number }}</td>
                                <td>{{ $item->total_amount }}</td>
                                <td>{{ $item->created_at->format('d-M-Y') }}</td>
                                <td>
                                    <a href="{{ url('booker/booking/view-invoice/'.$item->id) }}" class="btn btn-primary btn-sm"> View</a>
                                    @php
                                        $hasNonType1 = $item->bookingData->where('transaction_type', '!=', 1)->count() > 0;
                                    @endphp

                                    @if ($hasNonType1)
                                        <a href="{{ url('booker/booking/'.$item->id.'/edit-invoice') }}" class="btn btn-warning btn-sm"><i class="far fa-edit"></i> Edit</a>
                                    @else
                                        <a href="{{ url('booker/customer-booking/'.$item->id.'/edit') }}" class="btn btn-warning btn-sm"> <i class="far fa-edit"></i> Edit </a>
                                    @endif

                                    <form action="" method="POST" style="display:inline;" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i>Delete</button>
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


        document.addEventListener('DOMContentLoaded', function () {
          const deleteButtons = document.querySelectorAll('.status-confirm');
          deleteButtons.forEach(button => {
              button.addEventListener('click', function (e) {
                  e.preventDefault(); // Stop form submit
                  const form = this.closest('form');
                  Swal.fire({
                      title: 'Are you sure?',
                      text: "You won't be able to revert this!",
                      icon: 'success',
                      showCancelButton: true,
                      confirmButtonColor: '#28a745',
                      cancelButtonColor: '#3085d6',
                      confirmButtonText: 'Yes, Send It!'
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
