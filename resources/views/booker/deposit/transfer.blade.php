@extends('admin.master-main')
@php $userRole = Auth::user()->getRoleNames()->first(); @endphp
@section('title', ucfirst($userRole . " " . "Portal"))
@section('content')

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Transfer Deposit</h4>
                            </div>
                            <div class="card-body">
                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        {{ session('error') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif

                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif

                                <form action="{{ route('deposit.transfer.store', $deposit->id) }}" method="POST">
                                    @csrf

                                    <!-- Source Booking Info -->
                                    <div class="form-group">
                                        <label><strong>From Booking:</strong></label>
                                        <div class="card bg-light p-3">
                                            @if ($deposit->booking)
                                                <p class="mb-1"><strong>Booking ID:</strong> {{ $deposit->booking->id }}</p>
                                                <p class="mb-1"><strong>Agreement No:</strong> {{ $deposit->booking->agreement_no ?? 'N/A' }}</p>
                                                <p class="mb-0"><strong>Customer:</strong> {{ $deposit->booking->customer->customer_name ?? 'N/A' }}</p>
                                            @else
                                                <p class="mb-0 text-muted">No booking associated</p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Deposit Info -->
                                    <div class="form-group">
                                        <label><strong>Deposit Information:</strong></label>
                                        <div class="card bg-light p-3">
                                            <p class="mb-1"><strong>Initial Deposit:</strong> {{ number_format($deposit->initial_deposit, 2) }}</p>
                                            <p class="mb-0"><strong>Remaining Deposit:</strong> <span class="text-primary">{{ number_format($deposit->deposit_amount, 2) }}</span></p>
                                        </div>
                                    </div>

                                    <!-- Destination Booking Selection -->
                                    <div class="form-group">
                                        <label for="to_booking_id">Transfer To Booking <span class="text-danger">*</span></label>
                                        <select name="to_booking_id" id="to_booking_id" class="form-control select2" required>
                                            <option value="">Select Booking</option>
                                            @foreach ($bookings as $booking)
                                                <option value="{{ $booking->id }}" {{ old('to_booking_id') == $booking->id ? 'selected' : '' }}>
                                                    Booking #{{ $booking->id }} - {{ $booking->agreement_no ?? 'N/A' }} 
                                                    ({{ $booking->customer->customer_name ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('to_booking_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Transfer Amount -->
                                    <div class="form-group">
                                        <label for="transfer_amount">Transfer Amount <span class="text-danger">*</span></label>
                                        <input type="number" 
                                               name="transfer_amount" 
                                               id="transfer_amount" 
                                               class="form-control" 
                                               step="0.01" 
                                               min="0.01" 
                                               max="{{ $deposit->deposit_amount }}" 
                                               value="{{ old('transfer_amount', $deposit->deposit_amount) }}" 
                                               required>
                                        <small class="form-text text-muted">
                                            Maximum transferable: {{ number_format($deposit->deposit_amount, 2) }}
                                        </small>
                                        @error('transfer_amount')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-exchange-alt"></i> Transfer Deposit
                                        </button>
                                        <a href="{{ route('get.deposit') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endsection

