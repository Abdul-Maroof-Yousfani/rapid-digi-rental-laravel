$(document).ready(function () {
    const role = window.USER_ROLE;
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Render Vehicle Status Row In Table
    window.renderVehicleStatusRow = function (data, index) {
        return `
            <tr>
                <td>${index+1}.</td>
                <td>${data.name}</td>
                <td>
                    <a href='/admin/vehicle-status/${data.id}/edit' class="btn btn-warning btn-sm"><i class="far fa-edit"></i>Edit</a>
                    <form action="/admin/vehicle-status/${data.id}) }}" method="POST" style="display:inline;" class="delete-form">
                        <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i> Delete</button>
                    </form>
                </td>
            </tr>`;
    };

    // Render Sale Person Row In Table
    window.renderSaleMenRow = function (data, index) {
        return `
            <tr>
                <td>${index + 1}.</td>
                <td>${data.name}</td>
                <td>${data.status==1 ? 'Active' : 'Inactive' }</td>
                <td>
                    <a href="/admin/sale-person/${data.id}/edit" class="btn btn-warning btn-sm"><i class="far fa-edit"></i>Edit</a>
                    <form action="/admin/sale-person/${data.id}" method="POST" style="display:inline;" class="delete-form">
                        <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i> Delete</button>
                    </form>
                </td>
            </tr>`;
    };

    // Render Bank Row In Table
    window.renderBankRow = function(data, index) {
        return `
            <tr>
                <td>${index + 1}</td>
                <td>${data.bank_name}</td>
                <td>${data.account_name}</td>
                <td>${data.account_number}</td>
                <td>${data.iban}</td>
                <td>${data.swift_code}</td>
                <td>${data.branch}</td>
                <td>
                    <a href="/admin/bank/${data.id}/edit" class="btn btn-warning btn-sm"><i class="far fa-edit"></i> Edit</a>
                    <form action="/admin/bank/${data.id}" method="POST" style="display:inline;" class="delete-form">
                        <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i> Delete</button>
                    </form>
                </td>
            </tr>`;
    };

    // Render Vehicle Row In Table
    window.renderVehicleRow = function(data, index = 0){
        return `
            <tr data-id="${data.id}">
                <td>${index + 1}.</td>
                <td>${data.vehicle_name ?? data.temp_vehicle_detail}</td>
                <td>${data.vehicletype?.name}</td>
                <td>${data.investor?.name}</td>
                <td>${data.number_plate}</td>
                <td>${data.car_make}</td>
                <td>${data.year}</td>
                <td>${data.status==1 ? 'Active' : 'Inactive' }</td>
                <td>
                    <button type="button" class="btn btn-warning btn-sm edit-vehicle-btn" data-id="${data.id}"> <i class="far fa-edit"></i> Edit </button>
                    <form action="/admin/vehicle/${data.id}" method="POST" style="display:inline;" class="delete-form">
                        <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i> Delete</button>
                    </form>
                </td>
            </tr>`;
    };

    // Render Customer Row In Table
    window.renderCustomerRow = function(data, index){
        return `
        <tr>
            <td>${index+1}.</td>
            <td>${data.customer_name}</td>
            <td>${data.email}</td>
            <td>${data.phone}</td>
            <td>${data.cnic}</td>
            <td>${data.status==1 ? 'Active' : 'Inactive'}</td>
            <td>
                <a href="/${role}/customer/${data.id}/edit" class="btn btn-warning btn-sm"><i class="far fa-edit"></i> Edit</a>
                <form action="/${role}/customer/${data.id}" method="POST" style="display:inline;" class="delete-form">
                    <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger delete-confirm btn-sm"><i class="far fa-trash-alt"></i>
                        Delete
                    </button>
                </form>

            </td>
        </tr>`;
    };

    // Create And View Ajax Global Function
    $(".ajax-form").on("submit", function (e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();
        var url = form.data("url");
        var targetTable = form.data("target-table");
        var renderFunctionName = form.data("render-function");
        var modalId = form.data("modal-id");

        // Loader start
        let submitBtn = form.find("button[type='submit']");
        let originalText = submitBtn.html();
        submitBtn.prop("disabled", true).html('Saving... <i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function (response) {
                // console.log("Full Response:", response);
                if (response.success) {
                    if (modalId) { $('#' + modalId).modal('hide'); }
                    form[0].reset();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.success,
                        confirmButtonText: 'OK'
                    });
                    let currentRowCount = $(targetTable + " tbody tr").length;
                    if (response.data && typeof window[renderFunctionName] === 'function') {
                        let newRow = window[renderFunctionName](response.data, currentRowCount);
                        $(targetTable + " tbody").prepend(newRow);
                    }  else {
                        console.error("Render function not found or response data missing");
                    }
                    submitBtn.prop("disabled", false).html(originalText);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.error || 'Something went wrong',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function (xhr) {
                let errors = xhr.responseJSON?.errors || {};
                let errorMsg = '';
                $.each(errors, function (key, value) {
                    errorMsg += value + '\n';
                });

                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: errorMsg || 'Please check your input.',
                    confirmButtonText: 'OK'
                });

                submitBtn.prop("disabled", false).html(originalText);

            }
        });
    });

    // Delete Ajax Global Function
    $(document).on("submit", ".delete-form", function (e) {
        e.preventDefault();

        let form = $(this);
        let actionUrl = form.attr('action');

        Swal.fire({
            title: 'Are you sure?',
            text: "Yeh record permanently delete ho jayega!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: actionUrl,
                    type: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        if (response.success) {
                            form.closest("tr").remove();
                            Swal.fire('Deleted!', response.success, 'success');
                        } else {
                            Swal.fire('Error!', response.error || 'Kuch ghalat hogaya.', 'error');
                        }
                    },
                    error: function (xhr) {
                        console.log('AJAX Error Response:', xhr);
                        let message = 'Server ya validation issue.';
                        try {
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                message = xhr.responseJSON.error;
                            } else {
                                // Agar responseJSON na ho, toh responseText parse karo
                                let res = JSON.parse(xhr.responseText);
                                if (res.error) {
                                    message = res.error;
                                }
                            }
                        } catch (e) {
                            console.warn('Error parsing response:', e);
                        }

                        Swal.fire('Error!', message, 'error');

                    }
                });
            }
        });
    });


    // Open Edit Form In Modal
    $(document).on('click', '.edit-vehicle-btn', function (e) {
        e.preventDefault();
        let id = $(this).data('id');
        window.handleEdit(id, '/get-vehicle-for-edit-form/:id', 'editVehicleModal', window.populateVehicleForm);
    });

    // Populate Data in Vehicle Edit Form
    window.populateVehicleForm = function (data) {
        let form = $('#vehicleEditForm');
        const urlWithId = form.data('url').replace(':id', data.id);
        form.attr('action', urlWithId);
        form.find('input[name="vehicle_name"]').val(data.vehicle_name);
        form.find('input[name="car_make"]').val(data.car_make);
        form.find('select[name="vehicletypes"]').val(data.vehicletypes).trigger('change');
        form.find('select[name="investor_id"]').val(data.investor_id).trigger('change');
        form.find('input[name="year"]').val(data.year);
        form.find('input[name="number_plate"]').val(data.number_plate);
        form.find('input[name="status"][value="' + data.status + '"]').prop('checked', true);
    };


    window.handleEdit = function (id, fetchUrl, modalId, populateCallback) {
        $.ajax({
            url: fetchUrl.replace(':id', id),
            type: 'GET',
            success: function (response) {
                console.log("AJAX Response:", response);
                if (response.success && response.data) {
                populateCallback(response.data);
                $('#' + modalId).modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.error || 'Could not fetch data',
                    });
                }
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong while fetching data.',
                });
            }
        });
    };


    // Update Form Handler
    $(".ajax-update-form").on("submit", function (e) {
        e.preventDefault();

        let form = $(this);
        let formData = form.serialize();
        let url = form.attr("action");
        let targetTable = form.data("target-table");
        let renderFunctionName = form.data("render-function");
        let modalId = form.data("modal-id");

        let submitBtn = form.find("button[type='submit']");
        let originalText = submitBtn.html();
        submitBtn.prop("disabled", true).html('Updating... <i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            success: function (response) {
                if (response.success) {
                    if (modalId) $('#' + modalId).modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: response.success
                    });

                    if (response.data && typeof window[renderFunctionName] === 'function') {
                        let rowSelector = `${targetTable} tbody tr[data-id="${response.data.id}"]`;
                        let originalIndex = $(rowSelector).index();
                        let updatedRow = window[renderFunctionName](response.data, originalIndex);
                        $(rowSelector).replaceWith(updatedRow);
                    }

                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.error || 'Update failed' });
                }

                submitBtn.prop("disabled", false).html(originalText);
            },
            error: function (xhr) {
                let errors = xhr.responseJSON?.errors || {};
                let errorMsg = Object.values(errors).join('\n');

                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: errorMsg || 'Please check your input.'
                });

                submitBtn.prop("disabled", false).html(originalText);
            }
        });
    });



});
