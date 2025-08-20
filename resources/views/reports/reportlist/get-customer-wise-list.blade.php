@php
    $number = 1;
    $subtot = 0;
    $subamt = 0;
    $subpnd = 0;
@endphp
@foreach ($booking as $item)
    @php

    $price = $item->bookingData()->first()?->price ?? 0;
     $paidAmount = $item->payment?->paid_amount ?? 0;
      if($price <= $paidAmount){
        $paid_amt = $price;
        $rece_amt = 0;
      }
      else if($price > $paidAmount){
        $paid_amt = $paidAmount;
        $rece_amt = $price - $paidAmount;
      }
      
        $childCount = $item->bookingData->count();
        $itemTotal = $item->item_total ?? 0;
        $paidAmount = $paidAmount ?? 0;
        $pendingAmount = $item->payment->pending_amount ?? 0;

        $subtot += $price;
        $subamt += $paid_amt;
        $subpnd += $rece_amt;
    @endphp

    {{-- Parent Row --}}
    <tr>
        {{-- S. No with rowspan --}}
        <td class="align-middle">{{ $number }}.</td>
        <td>{{ $item->customer->customer_name ?? 'N/A' }} | {{ $item->agreement_no }}</td>
        <td>{{ $item->id }}</td>
        <td class="text-right">{{ number_format($price, 2) }}</td>
        <td class="text-right">{{ number_format($paid_amt, 2) }}</td>
        <td class="text-right">{{ number_format($rece_amt, 2) }}</td>
    </tr>


    {{-- Child Rows --}}
    {{--
    @foreach ($item->bookingData as $bd)
    @php $description= $bd->description; @endphp
        <tr>
          
            <td class="px-5" colspan="2">
                <div class="d-flex justify-content-between">
                    <span>
                        {{ $bd->vehicle->vehicle_name ?? $bd->vehicle->temp_vehicle_detail }} <br>
                        @if (trim(strtolower($description)) != 'fine' && trim(strtolower($description)) != 'salik')
                            {{ $description }}
                        @endif
                    </span>

                    <span>
                        @switch($bd->transaction_type)
                            @case(1) Rent @break
                            @case(2) Renew @break
                            @case(3) Fine &nbsp; Qty:{{ $bd->quantity }} @break
                            @case(4) Salik &nbsp; Qty:{{ $bd->quantity }} @break
                        @endswitch
                    </span>

                </div>

            </td>
            <td class="text-right">Price: {{ number_format($bd->item_total, 2) }}</td>
            <td class="text-right"></td>
            <td class="text-right"></td>
        </tr>
    @endforeach

    <tr>
        <td style="background: #f8f8f8" class="text-light" colspan="6">.</td>
    </tr>
    --}}
    @php $number++; @endphp
@endforeach

{{-- Footer total row --}}
<tr class="text-right">
    <td colspan="3"><b>Sub Total</b></td>
    <td>{{ number_format($subtot, 2) }}</td>
    <td>{{ number_format($subamt, 2) }}</td>
    <td>{{ number_format($subpnd, 2) }}</td>
</tr>
