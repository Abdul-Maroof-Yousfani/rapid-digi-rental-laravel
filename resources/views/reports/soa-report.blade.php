@extends('admin.master-main')
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

<div class="main-content">
    <section class="section">
        <div class="section-body">
            <form method="get" id="reportForm" class="mb-4">
                <div class="form-row align-items-end">
                    <div class="col-md-3">
                        <label for="month">Month</label>
                        <input type="month" name="month" id="month" class="form-control" value="">
                    </div>
                    <div class="col-md-3">
                        <label for="investor_id">Investor</label>
                        <select name="investor_id" class="form-control select2" id="investor_id">
                            <option value="">Select Investor</option>
                            @foreach ($investor as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary mt-4 w-100">Filter</button>
                    </div>
                </div>
            </form>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th>Plate no.</th>
                                            <th>Car Make - Model & Year</th>
                                            <th>Rental Period</th>
                                            <th>Rental Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="reportList">
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
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
