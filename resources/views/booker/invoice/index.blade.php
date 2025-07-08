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
                        @if ($booking->booking_status != 'closed')
                            <a href="{{ url('booking/'.$booking->id.'/create-invoice') }}" class="btn btn-primary">
                                Create Invoice
                            </a>
                        @endif
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
                                    <div class="dropdown">
                                        <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                                            Actions
                                        </button>
                                        <div class="dropdown-menu">
                                            <button type="button" class="invDetail dropdown-item" data-invoice-id="{{ $item->id }}" data-toggle="modal" data-target="#invoiceModal">
                                                Detail
                                            </button>

                                            <a href="{{ url('booking/view-invoice/'.$item->id) }}" class="dropdown-item"> <i class="fas fa-eye"></i> View</a>

                                            @if ($booking->booking_status != 'closed')
                                                @php $hasNonType1 = $item->bookingData->where('transaction_type', '!=', 1)->count() > 0; @endphp
                                                @if ($hasNonType1)
                                                    @can ('edit invoice') <a href="{{ url('booking/'.$item->id.'/edit-invoice') }}" class="dropdown-item"><i class="far fa-edit"></i> Edit</a> @endcan
                                                @else
                                                    @can ('edit booking') <a href="{{ url('customer-booking/'.$item->id.'/edit') }}" class="dropdown-item"> <i class="far fa-edit"></i> Edit </a> @endcan
                                                @endif
                                            @endif
                                            @can('delete invoice')
                                                <form action="" method="POST" style="display:inline;" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item delete-confirm text-danger"><i class="far fa-trash-alt"></i> Delete</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
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

        <!-- Create Model Code -->
        <div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Invoice Detail</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                    </div>
                </div>
            </div>
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

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = date.toLocaleString('default', { month: 'long' }); // Full month name
        const year = date.getFullYear();
        return `${day} ${month} ${year}`;
    }

    $(document).on('click', '.invDetail', function (e) {
        e.preventDefault();
        var invoiceId = $(this).data('invoice-id');

        $.ajax({
            url: '/get-invoice-detail/' + invoiceId,
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    let invoice = response.data.invoice;
                    let bookingData = response.data.booking_data;

                    let html = `<hr>`;
                    html += `<table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Vehicle</th>
                                        <th>No Plate</th>
                                        <th>From</th>
                                        <th>To</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    $.each(bookingData, function (index, item) {
                        html += `<tr>
                                    <td>${index + 1}</td>
                                    <td>
                                        ${item.vehicle ? (item.vehicle.vehicle_name ?? item.vehicle.temp_vehicle_detail ?? 'N/A') : 'N/A'}
                                    </td>
                                    <td>${item.vehicle.number_plate}</td>
                                    <td>${formatDate(item.start_date)}</td>
                                    <td>${formatDate(item.end_date)}</td>
                                </tr>`;
                    });

                    html += `</tbody></table>`;

                    $('#invoiceModal .modal-body').html(html);
                    $('#invoiceModal').modal('show');
                } else {
                    $('#invoiceModal .modal-body').html('<p class="text-danger">Invoice not found</p>');
                    $('#invoiceModal').modal('show');
                }
            }
        });
    });
  </script>

@endsection
