@extends('admin.master-main')
@section('title', ucfirst(Auth::user()->getRoleNames()->first()." "."Portal"))
@section('content')

<style>
  .spinner-border.custom-blue {
    width: 3rem;
    height: 3rem;
    border-width: 0.4rem; /* default se mota */
    border-top-color: #0d6efd; /* Bootstrap primary blue */
    border-right-color: #0d6efd;
    border-bottom-color: #0d6efd;
    border-left-color: rgba(13, 110, 253, 0.25); /* halki transparency */
  }
</style>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">Payment List</h3>
                        <a href="{{ role_base_route('payment.create') }}" class="btn btn-primary">
                            Create Payment
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
                            <th>Customer</th>
                            <th>Agreement no</th>
                            <th>Payment Method</th>
                            <th>Booking Amount</th>
                            <th>Paid Amount</th>
                            <th>Pending Amount</th>
                            <th>History</th>
                          </tr>
                        </thead>
                        <tbody id="paymentList"></tbody>
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

      $(document).ready(function(){
        $('#paymentList').html(`
            <tr>
            <td colspan="8" class="text-center">
                <div class="spinner-border custom-blue text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </td>
        </tr>
        `);
        $.ajax({
            url: '/get-payment-list',
            type: 'get',
            success:function(response){
                $('#paymentList').html(response);
            },
            error: function (xhr) {
                console.error('Error fetching payment list:', xhr.responseText);
            }
        });
      });

  </script>

@endsection
