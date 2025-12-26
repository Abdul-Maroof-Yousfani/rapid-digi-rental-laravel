@extends('admin.master-main')
@php $userRole = Auth::user()->getRoleNames()->first(); @endphp
@section('title', ucfirst($userRole . " " . "Portal"))
@section('content')

    <style>
        .spinner-border.custom-blue {
            width: 3rem;
            height: 3rem;
            border-width: 0.4rem;
            border-top-color: #0d6efd;
            border-right-color: #0d6efd;
            border-bottom-color: #0d6efd;
            border-left-color: rgba(13, 110, 253, 0.25);
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
                                <h3 class="mb-0">Deposit List</h3>
                            </div>
                        </div>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="filterForm">
                                    <div class="row">
                                        <div class="col-3 ml-auto">
                                            <input type="text" placeholder="Search" class="form-control" id="search">
                                        </div>
                                    </div><br>
                                </form>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th>S. No</th>
                                                <th>Initial Deposit</th>
                                                <th>Status / Remaining Deposit</th>
                                                <th>Is Transferred</th>
                                                <th>Transferred Booking ID</th>
                                                <th>Booking ID</th>
                                                <th>Created At</th>
                                                <th>Action</th>
                                                {{-- <th>Updated At</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody id="depositList">
                                        @php $index=1; @endphp
                                            @foreach ($deposits as $deposit)
                                                @php
                                                    $depositAmount = (float) ($deposit->deposit_amount ?? 0);
                                                    $initialDeposit = (float) ($deposit->initial_deposit ?? 0);

                                                    if ($depositAmount === 0.0 && $initialDeposit > 0) {
                                                        // fully used
                                                        $statusText = 'Used (0.00 remaining)';
                                                    } elseif ($depositAmount === $initialDeposit && $depositAmount > 0) {
                                                        // not used at all
                                                        $statusText = 'Not used (' . number_format($depositAmount, 2) . ' remaining)';
                                                    } elseif ($depositAmount > 0 && $depositAmount < $initialDeposit) {
                                                        // partially used
                                                        $statusText = 'Partial used (' . number_format($depositAmount, 2) . ' remaining)';
                                                    } else {
                                                        // fallback / invalid data
                                                        $statusText = 'N/A';
                                                    }
                                                @endphp
                                                <tr>
                                                    <td>{{ $index++ }}</td>
                                                    <td>{{ number_format($deposit->initial_deposit, 2) }}</td>
                                                    <td>{{ $statusText }}</td>
                                                    <td>
                                                        @if ($deposit->is_transferred == 1)
                                                            <span class="badge badge-success">Yes</span>
                                                        @else
                                                            <span class="badge badge-secondary">No</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($deposit->transferred_booking_id)
                                                            {{ $deposit->transferred_booking_id }}
                                                        @else
                                                            <span class="text-muted">NULL</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($deposit->booking)
                                                            {{ $deposit->booking->id ?? 'N/A' }}
                                                        @elseif ($deposit->transferredBooking)
                                                            {{ $deposit->transferredBooking->id ?? 'N/A' }}
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $deposit->created_at ? $deposit->created_at->format('Y-m-d') : 'N/A' }}</td>
                                                    <td>
                                                        @if ($deposit->is_transferred != 1 && $depositAmount > 0)
                                                            <a href="{{ route('deposit.transfer', $deposit->id) }}" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-exchange-alt"></i> Transfer
                                                            </a>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    {{-- <td>{{ $deposit->updated_at ? $deposit->updated_at->format('Y-m-d H:i:s') : 'N/A' }}</td> --}}
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div id="paginationContainer"></div>
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
        let currentPage = 1;
        let currentSearch = '';

        $(document).ready(function () {
            function loadDeposits(search = '', page = 1) {
                currentSearch = search;
                currentPage = page;
                $('#depositList').html(`
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="spinner-border custom-blue text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </td>
                    </tr>
                `);
                
                $.ajax({
                    url: '/search-deposit',
                    method: 'get',
                    data: { 
                        search: search,
                        page: page
                    },
                    success: function (response) {
                        let html = '';
                        if (response.deposits && response.deposits.length > 0) {
                            $.each(response.deposits, function (index, data) {
                                const isTransferred = data.is_transferred == 1
                                    ? '<span class="badge badge-success">Yes</span>'
                                    : '<span class="badge badge-secondary">No</span>';

                                const transferredBookingId = data.transferred_booking_id
                                    ? data.transferred_booking_id
                                    : '<span class="text-muted">NULL</span>';

                                const bookingId = (data.booking && data.booking.id)
                                    ? data.booking.id
                                    : ((data.transferred_booking && data.transferred_booking.id)
                                        ? data.transferred_booking.id
                                        : '<span class="text-muted">N/A</span>');

                                const depositAmount = parseFloat(data.deposit_amount ?? 0);
                                const initialDeposit = parseFloat(data.initial_deposit ?? 0);
                                let statusText = 'N/A';

                                if (!isNaN(initialDeposit) && initialDeposit > 0) {
                                    if (depositAmount === 0) {
                                        statusText = 'Used (0.00 remaining)';
                                    } else if (depositAmount === initialDeposit) {
                                        statusText = `Not used (${depositAmount.toFixed(2)} remaining)`;
                                    } else if (depositAmount > 0 && depositAmount < initialDeposit) {
                                        statusText = `Partial used (${depositAmount.toFixed(2)} remaining)`;
                                    }
                                }

                                const transferButton = (data.is_transferred != 1 && depositAmount > 0)
                                    ? `<a href="/deposit/${data.id}/transfer" class="btn btn-sm btn-primary"><i class="fas fa-exchange-alt"></i> Transfer</a>`
                                    : '<span class="text-muted">-</span>';

                                // Format date to YYYY-MM-DD
                                let formattedDate = 'N/A';
                                if (data.created_at) {
                                    const date = new Date(data.created_at);
                                    if (!isNaN(date.getTime())) {
                                        const year = date.getFullYear();
                                        const month = String(date.getMonth() + 1).padStart(2, '0');
                                        const day = String(date.getDate()).padStart(2, '0');
                                        formattedDate = `${year}-${month}-${day}`;
                                    }
                                }

                                html += `
                                    <tr>
                                        <td>${data.id}</td>
                                        <td>${initialDeposit.toFixed(2)}</td>
                                        <td>${statusText}</td>
                                        <td>${isTransferred}</td>
                                        <td>${transferredBookingId}</td>
                                        <td>${bookingId}</td>
                                        <td>${formattedDate}</td>
                                        <td>${transferButton}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            html = `<tr><td colspan="8" class="text-center">No results found</td></tr>`;
                        }
                        $('#depositList').html(html);

                        // Update pagination
                        if (response.pagination && response.pagination.last_page > 1) {
                            let paginationHtml = '<nav><ul class="pagination justify-content-center">';
                            
                            // Previous button
                            if (response.pagination.current_page > 1) {
                                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${response.pagination.current_page - 1}">Previous</a></li>`;
                            } else {
                                paginationHtml += `<li class="page-item disabled"><span class="page-link">Previous</span></li>`;
                            }

                            // Page numbers - show max 10 pages around current page
                            let startPage = Math.max(1, response.pagination.current_page - 5);
                            let endPage = Math.min(response.pagination.last_page, response.pagination.current_page + 5);
                            
                            if (startPage > 1) {
                                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                                if (startPage > 2) {
                                    paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                                }
                            }

                            for (let i = startPage; i <= endPage; i++) {
                                if (i === response.pagination.current_page) {
                                    paginationHtml += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                                } else {
                                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                                }
                            }

                            if (endPage < response.pagination.last_page) {
                                if (endPage < response.pagination.last_page - 1) {
                                    paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                                }
                                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${response.pagination.last_page}">${response.pagination.last_page}</a></li>`;
                            }

                            // Next button
                            if (response.pagination.current_page < response.pagination.last_page) {
                                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${response.pagination.current_page + 1}">Next</a></li>`;
                            } else {
                                paginationHtml += `<li class="page-item disabled"><span class="page-link">Next</span></li>`;
                            }

                            paginationHtml += '</ul></nav>';
                            paginationHtml += `<div class="text-center mt-2"><small>Showing ${response.pagination.from || 0} to ${response.pagination.to || 0} of ${response.pagination.total} entries</small></div>`;
                            $('#paginationContainer').html(paginationHtml);
                        } else {
                            $('#paginationContainer').html('');
                        }
                    },
                    error: function () {
                        $('#depositList').html(`<tr><td colspan="8" class="text-center text-danger">Error loading data</td></tr>`);
                        $('#paginationContainer').html('');
                    }
                });
            }

            // Initial load
            loadDeposits();

            // Handle search
            $('#search').on('keyup', function () {
                currentSearch = $(this).val();
                currentPage = 1; // Reset to first page on new search
                loadDeposits(currentSearch, currentPage);
            });

            // Handle pagination clicks
            $(document).on('click', '#paginationContainer .pagination a', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page) {
                    currentPage = parseInt(page);
                    loadDeposits(currentSearch, currentPage);
                }
            });
        });
    </script>
@endsection

