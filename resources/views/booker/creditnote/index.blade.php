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
                                <h3 class="mb-0">Credit Note List</h3>
                                <a href="{{ route('credit-note.create') }}" class="btn btn-primary">
                                    Create CN
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- <form action="{{ route('credit-note.upload') }}" method="POST" enctype="multipart/form-data">
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
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('deposit.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label for="xlsx_file">Upload XLSX File</label>
                        <input type="file" name="xlsx_file" id="xlsx_file" accept=".xlsx">
                        @error('xlsx_file')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit">Upload Deposit</button>
                </form>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('vehicles.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label for="xlsx_file">Upload XLSX File</label>
                        <input type="file" name="xlsx_file" id="xlsx_file" accept=".xlsx">
                        @error('xlsx_file')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit">Upload Vehicles</button>
                </form>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif --}}

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
                                                <th>Credit No.</th>
                                                <th>Customer</th>
                                                <th>Agreement no</th>
                                                <th>Refund Method</th>
                                                <th>Initial Deposit</th>
                                                <th>Remaining</th>
                                                <th>Refund</th>
                                                <th>View</th>
                                            </tr>
                                        </thead>
                                        <tbody id="creditNoteList">
                                            @foreach ($creditNote as $item)
                                                <tr>
                                                    <td>{{ $item->credit_note_no }}</td>
                                                    <td>{{ $item->booking->customer->customer_name ?? 'N/A' }}</td>
                                                    <td>{{ $item->booking->agreement_no ?? 'N/A' }}</td>
                                                    <td>{{ $item->paymentMethod->name ?? 'N/A' }}</td>
                                                    <td>{{ number_format($item->booking->deposit?->initial_deposit ?? 0, 2) }}
                                                    </td>
                                                    <td>{{ number_format($item->remaining_deposit, 2) }}</td>
                                                    <td>{{ number_format($item->refund_amount, 2) }}</td>
                                                    <td>
                                                        <a href="{{ url('view-credit-note/' . $item->id) }}"
                                                            class="btn btn-sm btn-primary">
                                                            <i class="fas fa-eye"></i>
                                                            View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    {{ $creditNote->links('pagination::bootstrap-4') }}
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
                $('#creditNoteList').html(`
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="spinner-border custom-blue text-primary" style="width: 3rem; height: 3rem;" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    `);
                $.ajax({
                    url: '/search-creditnote',
                    method: 'get',
                    data: { search: search },
                    success: function (response) {
                        let html = '';
                        if (response.creditNote.length > 0) {
                            $.each(response.creditNote, function (index, data) {
                                html += `
                                        <tr data-id="${data.id}">
                                            <td>${data.credit_note_no}</td>
                                            <td>${data.booking.customer.customer_name}</td>
                                            <td>${data.booking.agreement_no}</td>
                                            <td>${data.booking?.paymentMethod?.name ?? 'N/A'}</td>
                                            <td>${data.booking.deposit.deposit_amount}</td>
                                            <td>${data.remaining_deposit}</td>
                                            <td>${data.refund_amount}</td>
                                            <td>
                                                <a href="view-credit-note/${data.id}" class="btn btn-sm btn-primary"> <i class="fas fa-eye"></i>
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    `;
                            });
                        } else {
                            html = `<tr><td colspan="8" class="text-center">No results found</td></tr>`;
                        }


                        $('#creditNoteList').html(html);
                    }
                });
            });
        });

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
    </script>

@endsection