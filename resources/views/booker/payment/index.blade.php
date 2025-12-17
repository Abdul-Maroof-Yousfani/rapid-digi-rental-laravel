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
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Details</h5>
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
        // CSRF Token for AJAX requests
        var csrfToken = '{{ csrf_token() }}';
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
                url: '/get-payment-data/' + paymentId,
                type: 'GET',
                success: function (response) {
                    if (response.success) {
                        let paymentData = response.data;

                        if (paymentData.length === 0) {
                            $('#paymentHistoryModal .modal-body').html('<p class="text-center text-muted">No payment data found</p>');
                            $('#paymentHistoryModal').modal('show');
                            return;
                        }

                        let html = `<div class="table-responsive">`;
                        html += `<table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>S. No</th>
                                            <th>Invoice Number</th>
                                            <th>Invoice Amount</th>
                                            <th>Paid Amount</th>
                                            <th>Pending Amount</th>
                                            <th>Status</th>
                                            <th>Payment Method</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

                        $.each(paymentData, function (index, item) {
                            let invoiceNumber = item.invoice ? (item.invoice.zoho_invoice_number || '-') : '-';
                            let invoiceAmount = parseFloat(item.invoice_amount || 0);
                            let paidAmount = parseFloat(item.paid_amount || 0);
                            let pendingAmount = parseFloat(item.pending_amount || 0);
                            let status = item.status || 'pending';
                            let paymentMethod = '-';
                            if (item.payment) {
                                if (item.payment.payment_method && item.payment.payment_method.name) {
                                    paymentMethod = item.payment.payment_method.name;
                                } else if (item.payment.paymentMethod && item.payment.paymentMethod.name) {
                                    paymentMethod = item.payment.paymentMethod.name;
                                }
                            }
                            
                            let dateObj = item.created_at ? new Date(item.created_at) : new Date();
                            let day = dateObj.getDate().toString().padStart(2, '0');
                            let month = dateObj.toLocaleString('default', { month: 'short' });
                            let year = dateObj.getFullYear();
                            let formattedDate = `${day}-${month}-${year}`;

                            let statusBadge = status === 'paid' 
                                ? '<span class="badge badge-success">Paid</span>' 
                                : '<span class="badge badge-warning">Pending</span>';

                            html += `<tr>
                                        <td>${index + 1}</td>
                                        <td>${invoiceNumber}</td>
                                        <td>AED ${invoiceAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                        <td>AED ${paidAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                        <td>AED ${pendingAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                        <td>${statusBadge}</td>
                                        <td>${paymentMethod}</td>
                                        <td>${formattedDate}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger delete-payment-data" 
                                                data-payment-data-id="${item.id}" 
                                                data-payment-id="${paymentId}"
                                                title="Delete this payment entry">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>`;
                        });

                        html += `</tbody></table></div>`;

                        $('#paymentHistoryModal .modal-body').html(html);
                        $('#paymentHistoryModal').modal('show');
                    } else {
                        $('#paymentHistoryModal .modal-body').html('<p class="text-danger">Payment data not found</p>');
                        $('#paymentHistoryModal').modal('show');
                    }
                },
                error: function(xhr) {
                    $('#paymentHistoryModal .modal-body').html('<p class="text-danger">Error loading payment data</p>');
                    $('#paymentHistoryModal').modal('show');
                }
            });
        });

        // Delete PaymentData
        $(document).on('click', '.delete-payment-data', function (e) {
            e.preventDefault();
            var paymentDataId = $(this).data('payment-data-id');
            var paymentId = $(this).data('payment-id');
            var row = $(this).closest('tr');

            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this payment entry? This will update the payment totals.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/payment-data/' + paymentDataId,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire('Deleted!', response.message, 'success');
                                row.fadeOut(300, function() {
                                    $(this).remove();
                                    // Reload payment list to update totals
                                    loadPaymentList();
                                });
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            let message = 'Error deleting payment data';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            Swal.fire('Error!', message, 'error');
                        }
                    });
                }
            });
        });

    </script>

@endsection