@php 
        $totalAmount = 0;
    $totalInvoices = count($soaData);
@endphp

@foreach ($soaData as $item)
    @php
        $totalAmount += $item->rental_amount;
    @endphp

    <tr data-invoice-id="{{ $item->invoice_id }}" class="soa-row">
        <td style="text-align: center;">
            <input type="checkbox" class="row-checkbox" data-invoice-id="{{ $item->invoice_id }}" checked>
        </td>
        <td>{{ $item->plate_no }}</td>
        <td>
            <a href="{{ route('view.invoice', $item->invoice_id) }}" target="_blank"
                style="color: #0d6efd; text-decoration: underline;">
                {{ $item->invoice_number }}
            </a>
        </td>
        <td>{{ $item->car_details }}</td>
        <td>{{ $item->rental_period }}</td>
        <td class="rental-amount">{{ number_format($item->rental_amount, 2) }}</td>
    </tr>
@endforeach

@if(count($soaData) > 0)
    <tr class="soa-totals-row" style="background-color: #f8f9fa; font-weight: bold;">
        <td></td>
        <td colspan="4" style="text-align: left;">
            <strong>Total Invoices: <span id="totalInvoiceCount">{{ $totalInvoices }}</span></strong>
        </td>
        <td style="text-align: left;">
            <strong>Total Rental Amount: <span id="totalRentalAmount">{{ number_format($totalAmount, 2) }}</span></strong>
        </td>
    </tr>
@endif

<script>
    $(document).ready(function () {
        // Initialize disabled rows from sessionStorage
        let disabledRows = JSON.parse(sessionStorage.getItem('soaDisabledRows') || '[]');

        // Apply disabled state to rows based on sessionStorage
        disabledRows.forEach(function (invoiceId) {
            let row = $('tr[data-invoice-id="' + invoiceId + '"]');
            let checkbox = row.find('.row-checkbox');
            row.addClass('disabled-row').css({
                'opacity': '0.5',
                'text-decoration': 'line-through'
            });
            checkbox.prop('checked', false);
        });

        // Handle checkbox change
        $(document).on('change', '.row-checkbox', function () {
            let invoiceId = $(this).data('invoice-id');
            let row = $('tr[data-invoice-id="' + invoiceId + '"]');
            let isChecked = $(this).is(':checked');

            if (isChecked) {
                // Enable row
                row.removeClass('disabled-row').css({
                    'opacity': '1',
                    'text-decoration': 'none'
                });

                // Remove from disabled list
                disabledRows = disabledRows.filter(function (id) {
                    return id != invoiceId;
                });
            } else {
                // Disable row
                row.addClass('disabled-row').css({
                    'opacity': '0.5',
                    'text-decoration': 'line-through'
                });

                // Add to disabled list
                if (disabledRows.indexOf(invoiceId) === -1) {
                    disabledRows.push(invoiceId);
                }
            }

            // Save to sessionStorage
            sessionStorage.setItem('soaDisabledRows', JSON.stringify(disabledRows));

            // Update select all checkbox state
            updateSelectAllCheckbox();

            // Recalculate totals
            calculateTotals();
        });

        // Handle select all checkbox
        $(document).on('change', '#selectAllRows', function () {
            let isChecked = $(this).is(':checked');
            let disabledRows = [];

            $('.row-checkbox').each(function () {
                $(this).prop('checked', isChecked);
                let invoiceId = $(this).data('invoice-id');
                let row = $('tr[data-invoice-id="' + invoiceId + '"]');

                if (isChecked) {
                    // Enable all rows
                    row.removeClass('disabled-row').css({
                        'opacity': '1',
                        'text-decoration': 'none'
                    });
                } else {
                    // Disable all rows
                    row.addClass('disabled-row').css({
                        'opacity': '0.5',
                        'text-decoration': 'line-through'
                    });
                    disabledRows.push(invoiceId);
                }
            });

            // Save to sessionStorage
            sessionStorage.setItem('soaDisabledRows', JSON.stringify(disabledRows));

            // Recalculate totals
            calculateTotals();
        });

        // Update select all checkbox state
        function updateSelectAllCheckbox() {
            let totalRows = $('.row-checkbox').length;
            let checkedRows = $('.row-checkbox:checked').length;
            $('#selectAllRows').prop('checked', totalRows > 0 && totalRows === checkedRows);
        }

        // Calculate totals function
        function calculateTotals() {
            let total = 0;
            let invoiceCount = 0;

            $('.soa-row:not(.disabled-row)').each(function () {
                invoiceCount++;
                let amount = parseFloat($(this).find('.rental-amount').text().replace(/,/g, ''));
                total += amount;
            });

            // Update total rental amount
            $("#totalRentalAmount").text(total.toFixed(2));

            // Update total invoice count
            $("#totalInvoiceCount").text(invoiceCount);

            // Update other totals if they exist
            if ($("#totalAmount").length) {
                $("#totalAmount").text(total.toFixed(2));
            }
            if ($("#netAmount").length) {
                $("#netAmount").text((total * 0.8).toFixed(2));
            }
            if ($("#printTotalAmount").length) {
                $("#printTotalAmount").text(total.toFixed(2));
            }
            if ($("#printNetAmount").length) {
                $("#printNetAmount").text((total * 0.8).toFixed(2));
            }
        }

        // Initial calculation and select all state
        calculateTotals();
        updateSelectAllCheckbox();
    });
</script>