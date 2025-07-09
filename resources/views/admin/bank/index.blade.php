@extends('admin.master-main')
@section('title', ucfirst(Auth::user()->getRoleNames()->first()." "."Portal"))
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
                            <h3 class="mb-0">Bank List</h3>
                            @can('create bank')
                            <span>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#bankModal">
                                    Add Bank
                                </button>
                            </span>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

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
                                <table class="table table-striped table-hover" style="width:100%;" id="bankResponseList">
                                    <thead>
                                        <tr>
                                            <th>S No.</th>
                                            <th>Bank</th>
                                            <th>Acc Name</th>
                                            <th>Acc No.</th>
                                            <th>IBAN</th>
                                            <th>Swift Code</th>
                                            <th>Branch</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="responseList">
                                        @php $number=1; @endphp
                                        @foreach ($bank as $item)
                                        <tr data-id="{{ $item->id }}">
                                            <td>{{ $number }}.</td>
                                            <td>{{ $item->bank_name }}</td>
                                            <td>{{ $item->account_name }}</td>
                                            <td>{{ $item->account_number }}</td>
                                            <td>{{ $item->iban }}</td>
                                            <td>{{ $item->swift_code }}</td>
                                            <td>{{ $item->branch }}</td>
                                            <td>
                                                {{-- <a href="{{ url("admin/bank/".$item->id."/edit") }}" class="btn btn-warning btn-sm"><i class="far fa-edit"></i> Edit</a> --}}
                                                <button type="button" class="btn btn-warning btn-sm ajax-edit-btn" data-id="{{ $item->id }}" data-modal-id="editBankModal">
                                                    <i class="far fa-edit"></i> Edit
                                                </button>
                                                <form action="{{ url('bank/'.$item->id) }}" method="POST" style="display:inline;" class="delete-form">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <input type="hidden" name="_method" value="DELETE">
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

    <!--Create Modal Form-->
    <div class="modal fade" id="bankModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <form class="ajax-form" data-url="{{ route('bank.store') }}" data-target-table="#bankResponseList" data-render-function="renderBankRow" data-modal-id="bankModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Bank</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <label>Bank name <span class="text-danger">*</span></label>
                                                    <input type="text" value="{{ old('bank_name') }}" name="bank_name" class="form-control" required>
                                                    @error('bank_name') <span class="text-danger">{{ $message }}</span> @enderror
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label>Account no. <span class="text-danger">*</span></label>
                                                    <input type="number" value="{{ old('account_no') }}" name="account_no" class="form-control account_no" required>
                                                    @error('account_no')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <label>Branch </label>
                                                    <input type="text" name="branch" class="form-control branch">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label>Swift Code </label>
                                                    <input type="text" name="swift_code" class="form-control swift_code">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <label>Account Name <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" value="{{ old('account_name') }}" name="account_name" class="form-control"  required>
                                                    </div>
                                                    @error('account_name')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label>IBAN <span class="text-danger">*</span></label>
                                                        <input type="text" value="{{ old('iban') }}" name="iban" class="form-control iban"  required>
                                                    @error('iban')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <label>Currency </label><br>
                                                    <select name="currency" class="form-control select2">
                                                        <option value="">Select Currency</option>
                                                        <option>PKR</option>
                                                        <option selected>AED</option>
                                                        <option>USD</option>
                                                        <option>UAE</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label>Notes </label>
                                                    <textarea name="notes" cols="30" class="form-control" rows="10">{{ old('notes') }}</textarea>
                                                    @error('notes')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

        <!-- Edit Model Code -->
    <div class="modal fade" id="editBankModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <form id="bankEditForm" method="POST" class="ajax-update-form"
                        data-url="{{ url('bank') }}/:id"
                        data-fetch-url="{{ url('get-bank-for-edit-form/:id') }}"
                        data-target-table="#bankResponseList"
                        data-render-function="renderBankRow"
                        data-modal-id="editBankModal"
                        data-callback="populateBankForm">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Bank</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <label>Bank name <span class="text-danger">*</span></label>
                                                    <input type="text" value="{{ old('bank_name') }}" name="bank_name" class="form-control" required>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label>Account no. <span class="text-danger">*</span></label>
                                                    <input type="number" value="{{ old('account_no') }}" name="account_no" class="form-control account_no" required>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <label>Branch </label>
                                                    <input type="text" name="branch" class="form-control branch">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label>Swift Code </label>
                                                    <input type="text" name="swift_code" class="form-control swift_code">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <label>Account Name <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" value="{{ old('account_name') }}" name="account_name" class="form-control"  required>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label>IBAN <span class="text-danger">*</span></label>
                                                    <input type="text" value="{{ old('iban') }}" name="iban" class="form-control iban"  required>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <label>Currency </label><br>
                                                    <select name="currency" class="form-control select2">
                                                        <option value="">Select Currency</option>
                                                        <option>PKR</option>
                                                        <option selected>AED</option>
                                                        <option>USD</option>
                                                        <option>UAE</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label>Notes </label>
                                                    <textarea name="notes" cols="30" class="form-control" rows="10">{{ old('notes') }}</textarea>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('script')
<script src="{{ asset('assets/js/forms-format.js') }}"></script>
<script type="text/javascript">

        $(document).ready(function () {
             $('#search').on('keyup', function () {
                let search = $(this).val();
                $('#responseList').html(`
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="spinner-border custom-blue text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </td>
                    </tr>
                `);
                $.ajax({
                    url:'/search-bank',
                    method: 'get',
                    data: { search : search },
                    success:function(response){
                        let html = '';
                        let number = 1;
                        if (response.banks.length > 0) {
                            $.each(response.banks, function (index, data) {
                                html += `
                                    <tr data-id="${data.id}">
                                        <td>${number}.</td>
                                        <td>${data.bank_name}</td>
                                        <td>${data.account_name}</td>
                                        <td>${data.account_number}</td>
                                        <td>${data.iban}</td>
                                        <td>${data.swift_code}</td>
                                        <td>${data.branch}</td>
                                        <td>


                                            <button type="button" class="btn btn-warning btn-sm ajax-edit-btn" data-id="${data.id}" data-modal-id="editBankModal">
                                                <i class="far fa-edit"></i> Edit
                                            </button>


                                            <form action="/bank/${data.id}" method="POST" style="display:inline;" class="delete-form">
                                                <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i> Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                `;
                                number++;
                            });
                        } else {
                            html = `<tr><td colspan="8" class="text-center">No results found</td></tr>`;
                        }

                        $('#responseList').html(html);
                    }
                });
             });
        });
</script>

@endsection
