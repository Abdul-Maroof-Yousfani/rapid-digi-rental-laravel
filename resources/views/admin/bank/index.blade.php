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
                            <h3 class="mb-0">Bank List</h3>
                            <span>
                                <a href="{{ route('admin.bank.create') }}" class="btn btn-primary">
                                    Add Bank
                                </a>&nbsp;&nbsp;&nbsp;
                            </span>
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
                                        <th>S.no</th>
                                        <th>Bank</th>
                                        <th>Acc Name</th>
                                        <th>Acc No.</th>
                                        <th>IBAN</th>
                                        <th>Swift Code</th>
                                        <th>Branch</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="customerList">
                                    @php $number=1; @endphp
                                    @foreach ($bank as $item)
                                    <tr>
                                        <td>{{ $number }}.</td>
                                        <td>{{ $item->bank_name }}</td>
                                        <td>{{ $item->account_name }}</td>
                                        <td>{{ $item->account_number }}</td>
                                        <td>{{ $item->iban }}</td>
                                        <td>{{ $item->swift_code }}</td>
                                        <td>{{ $item->branch }}</td>
                                        <td>
                                            <a href="{{ url("admin/bank/".$item->id."/edit") }}" class="btn btn-warning btn-sm"><i class="far fa-edit"></i> Edit</a>
                                            <form action="{{ url('admin/bank/'.$item->id) }}" method="POST" style="display:inline;" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i>
                                                    Delete
                                                </button>
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

    </script>

@endsection
