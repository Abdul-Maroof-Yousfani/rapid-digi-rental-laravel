@php
    $number = 1;
    $subtot = 0;
    $subamt = 0;
    $subpnd = 0;
@endphp
@foreach ($booking as $item)
    @php
        // Use the pre-calculated totals from controller
        $itemTotal = $item->total_price ?? 0;
        $paidAmount = $item->paid_amount ?? 0;

        if ($itemTotal <= $paidAmount) {
            $paid_amt = $itemTotal;
            $rece_amt = 0;
        } else {
            $paid_amt = $paidAmount;
            $rece_amt = $itemTotal - $paidAmount;
        }

        $subtot += $itemTotal;
        $subamt += $paid_amt;
        $subpnd += $rece_amt;
    @endphp

    {{-- Parent Row --}}
    <tr>
        <td class="align-middle">{{ $number }}.</td>
        <td>
            <a
                href="{{ route('customerWiseDetailReport', ['customer_id' => $item->customer->id ?? '', 'fromDate' => $fromDate, 'toDate' => $toDate]) }}">
                {{ $item->customer->customer_name ?? 'N/A' }}
            </a>
        </td>
        {{-- <td>{{ $item->id }}</td> --}}
        <td class="text-right">{{ number_format($itemTotal, 2) }}</td>
        <td class="text-right">{{ number_format($paid_amt, 2) }}</td>
        <td class="text-right">{{ number_format($rece_amt, 2) }}</td>
    </tr>

    @php $number++; @endphp
@endforeach


{{-- Footer total row --}}
<tr class="text-right">
    <td colspan="2"><b>Sub Total</b></td>
    <td>{{ number_format($subtot, 2) }}</td>
    <td>{{ number_format($subamt, 2) }}</td>
    <td>{{ number_format($subpnd, 2) }}</td>
</tr>