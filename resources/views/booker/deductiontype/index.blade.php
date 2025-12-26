@extends('admin.master-main')
@php $userRole= Auth::user()->getRoleNames()->first(); @endphp
@section('title', ucfirst($userRole." "."Portal"))
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
                            <h3 class="mb-0">Invoice Type List</h3>
                            <a href="{{ route('invoice-type.create') }}" class="btn btn-primary">
                                Create IT
                            </a>
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
                                <table class="table table-striped table-hover" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th>S.no</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    @php $index = 1; @endphp
                                    <tbody id="deductionTypeList">
                                        @foreach ($deductionType as $item)

                                        <tr>
                                            <td>{{ $index++ }}</td>
                                            <td>{{ $item->name ?? 'N/A' }}</td>
                                            <td>{{ $item->status == 1 ? 'Active' : 'Inactive' }}</td>

                                            <td>
                                                <a href='{{ url("invoice-type/".$item->id."/edit") }}' class="btn btn-warning btn-sm"><i class="far fa-edit"></i>Edit</a>
                                                <form action="{{ url('invoice-type/'.$item->id) }}" method="POST" style="display:inline;" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i>Delete</button>
                                                </form>
                                            </td>
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
    $(document).ready(function() {
        let currentSearch = '';
        let currentPage = 1;

        function loadDeductionTypes(search = '', page = 1) {
            currentSearch = search;
            currentPage = page;

            $('#deductionTypeList').html(`
                <tr>
                    <td colspan="4" class="text-center">
                        <div class="spinner-border custom-blue text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </td>
                </tr>
            `);

            $.ajax({
                url: '/search-deductiontype',
                method: 'get',
                data: {
                    search: search,
                    page: page
                },
                success: function(response) {
                    let html = '';
                    let number = (page - 1) * 10 + 1;
                    if (response.deductionType && response.deductionType.length > 0) {
                        $.each(response.deductionType, function(index, data) {
                            html += `
                                <tr data-id="${data.id}">
                                    <td>${number}</td>
                                    <td>${data.name || 'N/A'}</td>
                                    <td>${data.status == 1 ? 'Active' : 'Inactive'}</td>
                                    <td>
                                        <a href='invoice-type/${data.id}/edit' class="btn btn-warning btn-sm"><i class="far fa-edit"></i>Edit</a>
                                        <form action="invoice-type/${data.id}" method="POST" style="display:inline;" class="delete-form">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i>Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            `;
                            number++;
                        });
                    } else {
                        html = `<tr><td colspan="4" class="text-center">No results found</td></tr>`;
                    }

                    $('#deductionTypeList').html(html);

                    // Update pagination
                    if (response.pagination && response.pagination.last_page > 1) {
                        let paginationHtml = '<nav><ul class="pagination justify-content-center">';
                        
                        // Previous button
                        if (response.pagination.current_page > 1) {
                            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${response.pagination.current_page - 1}">Previous</a></li>`;
                        } else {
                            paginationHtml += `<li class="page-item disabled"><span class="page-link">Previous</span></li>`;
                        }

                        // Page numbers
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
                error: function() {
                    $('#deductionTypeList').html(`<tr><td colspan="4" class="text-center text-danger">Error loading data</td></tr>`);
                    $('#paginationContainer').html('');
                }
            });
        }

        // Initial load
        loadDeductionTypes();

        // Search on keyup
        $('#search').on('keyup', function() {
            loadDeductionTypes($(this).val(), 1);
        });

        // Handle pagination clicks
        $(document).on('click', '#paginationContainer .pagination a', function(e) {
            e.preventDefault();
            let page = $(this).data('page');
            if (page) {
                loadDeductionTypes(currentSearch, page);
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-confirm');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
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