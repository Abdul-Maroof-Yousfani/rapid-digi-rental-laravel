view-payment-history
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
                            <th>Payment Amount</th>
                            <th>Method</th>
                          </tr>
                        </thead>
                        @php $number= 1; @endphp
                        @foreach ($paymentHistory as $item)
                        <tbody>
                            <tr>
                                <td>{{ $number }}.</td>
                                <td>{{ $item->paid_amount }}</td>
                                <td>{{ $item->paymentMethod->name }}</td>
                            </tr>
                        </tbody>
                        @php $number++ @endphp
                        @endforeach
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
