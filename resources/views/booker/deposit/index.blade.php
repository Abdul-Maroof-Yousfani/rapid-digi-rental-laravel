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
                                    {{ $deposits->links('pagination::bootstrap-4') }}
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
        $(document).ready(function () {
            $('#search').on('keyup', function () {
                let search = $(this).val();
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
                    data: { search: search },
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

                                const agreementNo = (data.booking && data.booking.agreement_no)
                                    ? data.booking.agreement_no
                                    : ((data.transferred_booking && data.transferred_booking.agreement_no)
                                        ? data.transferred_booking.agreement_no
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

                                html += `
                                    <tr>
                                        <td>${data.id}</td>
                                        <td>${initialDeposit.toFixed(2)}</td>
                                        <td>${statusText}</td>
                                        <td>${isTransferred}</td>
                                        <td>${transferredBookingId}</td>
                                        <td>${agreementNo}</td>
                                        <td>${data.created_at || 'N/A'}</td>
                                        <td>${transferButton}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            html = `<tr><td colspan="8" class="text-center">No results found</td></tr>`;
                        }
                        $('#depositList').html(html);
                    },
                    error: function () {
                        $('#depositList').html(`<tr><td colspan="8" class="text-center text-danger">Error loading data</td></tr>`);
                    }
                });
            });
        });
    </script>
@endsection

