@extends('admin.master-main')
@section('title', ucfirst(Auth::user()->getRoleNames()->first() . " " . "Portal"))
@section('content')

    <style>
        .spinner-border.custom-blue {
            width: 3rem;
            height: 3rem;
            border-width: 0.4rem;
            /* default se mota */
            border-top-color: #0d6efd;
            /* Bootstrap primary blue */
            border-right-color: #0d6efd;
            border-bottom-color: #0d6efd;
            border-left-color: rgba(13, 110, 253, 0.25);
            /* halki transparency */
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
                                <h3 class="mb-0">Payment Voucher List</h3>
                                <a href="{{ route('payment.create') }}" class="btn btn-primary">
                                    Create Payment
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- <form action="{{ route('payments.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label for="xlsx_file">Upload XLSX File</label>
                        <input type="file" name="xlsx_file" id="xlsx_file" accept=".xlsx">
                        @error('xlsx_file')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit">Upload</button>
                </form>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert
                     alert-danger">{{ session('error') }}</div>
                @endif --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="filterForm">
                                    <div class="row">
                                        <div class="col-3 ml-auto">
                                            <input type="text" placeholder="Search Booking No. / Customer" class="form-control" id="search">
                                        </div>
                                    </div><br>
                                </form>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th>S. No</th>
                                                <th>Customer</th>
                                                <th>Booking No</th>
                                                <th>Invoice No</th>
                                                <th>Payment Method</th>
                                                <th>Booking Amount</th>
                                                <th>Paid Amount</th>
                                                <th>Pending Amount</th>
                                                <th>History</th>
                                            </tr>
                                        </thead>
                                        <tbody id="paymentList"></tbody>
                                    </table>
                                    {{-- {{ $payment->links('pagination::bootstrap-4') }} --}}

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Create Model Code -->
    <div class="modal fade" id="paymentHistoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment History</h5>
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
        // Use event delegation for dynamically added delete buttons
        $(document).on('click', '.delete-confirm', function (e) {
            e.preventDefault(); // Stop form submit
            const form = $(this).closest('form');
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

        $(document).ready(function () {

            function loadPaymentList(url = '/get-payment-list', search = '') {
                $('#paymentList').html(`
                <tr>
                    <td colspan="9" class="text-center">
                        <div class="spinner-border custom-blue text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </td>
                </tr>
            `);

                // Add search parameter to URL if provided
                if (search) {
                    url += (url.includes('?') ? '&' : '?') + 'search=' + encodeURIComponent(search);
                }

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (response) {
                        $('#paymentList').html(response);
                    },
                    error: function (xhr) {
                        console.error('Error fetching payment list:', xhr.responseText);
                        $('#paymentList').html(`<tr><td colspan="9" class="text-center text-danger">Error loading payments</td></tr>`);
                    }
                });
            }

            // Initial load
            loadPaymentList();

            // Handle pagination click
            $(document).on('click', '.pagination a', function (e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const search = $('#search').val();
                if (url) loadPaymentList(url, search);
            });


            $('#search').on('keyup', function () {
                let search = $(this).val().trim();
                
                // If search is empty, reload the full list
                if (search === '') {
                    loadPaymentList();
                    return;
                }

                // Show loader while data is loading
                $('#paymentList').html(`
                    <tr>
                        <td colspan="9" class="text-center">
                            <div class="spinner-border custom-blue text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </td>
                    </tr>
                `);
                $.ajax({
                    url: '/search-payment',
                    method: 'get',
                    data: { search: search },
                    success: function (response) {
                        let html = '';
                        let number = 1;
                        if (response.payments && response.payments.length > 0) {
                            $.each(response.payments, function (index, data) {
                                html += `
                                    <tr>
                                        <td>${number}.</td>
                                        <td>${data.booking && data.booking.customer ? data.booking.customer.customer_name : 'No Customer'}</td>
                                        <td>${data.booking ? data.booking.id : '-'}</td>
                                        <td>${data.booking && data.booking.invoice ? (data.booking.invoice.zoho_invoice_number || '-') : '-'}</td>
                                        <td>${data.payment_method ? data.payment_method.name : '-'}</td>
                                        <td>${parseFloat(data.booking_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                        <td>${parseFloat(data.paid_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                        <td>${parseFloat(data.pending_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <div class="dropdown-menu">
                                                    <button type="button" class="dropdown-item paymentHistory" data-payment-id="${data.id}" data-toggle="modal" data-target="#paymentHistoryModal">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    <form action="/payment/${data.id}" method="POST" style="display:inline;" class="delete-form">
                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <button type="submit" class="dropdown-item delete-confirm text-danger">
                                                            <i class="far fa-trash-alt"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                                number++;
                            });
                            $('#paymentList').html(html);
                        } else {
                            html = `<tr><td colspan="9" class="text-center">No results found</td></tr>`;
                            $('#paymentList').html(html);
                        }
                    },
                    error: function (xhr) {
                        console.error('Error searching payments:', xhr.responseText);
                        $('#paymentList').html(`<tr><td colspan="9" class="text-center text-danger">Error loading payments</td></tr>`);
                    }
                });
            });



        });

        $(document).on('click', '.paymentHistory', function (e) {
            e.preventDefault();
            var paymentId = $(this).data('payment-id');

            $.ajax({
                url: '/get-payment-history/' + paymentId,
                type: 'GET',
                success: function (response) {
                    if (response.success) {
                        let paymentHistory = response.data;

                        let html = `<hr>`;
                        html += `<table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>S. No</th>
                                            <th>Method</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

                        $.each(paymentHistory, function (index, item) {
                            let dateObj = new Date(item.payment_date);
                            let day = dateObj.getDate().toString().padStart(2, '0'); // 26
                            let month = dateObj.toLocaleString('default', { month: 'long' }); // June
                            let year = dateObj.getFullYear(); // 2025
                            let formattedDate = `${day}-${month}-${year}`;

                            html += `<tr>
                                        <td>${index + 1}</td>
                                        <td>${item.payment_method ? item.payment_method.name : ''}</td>
                                        <td>${Number(item.paid_amount).toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            })}</td>
                                        <td>${formattedDate ?? 'N/A'}</td>
                                    </tr>`;
                        });

                        html += `</tbody></table>`;

                        $('#paymentHistoryModal .modal-body').html(html);
                        $('#paymentHistoryModal').modal('show');
                    } else {
                        $('#paymentHistoryModal .modal-body').html('<p class="text-danger">Invoice not found</p>');
                        $('#paymentHistoryModal').modal('show');
                    }
                }
            });
        });

    </script>

@endsection