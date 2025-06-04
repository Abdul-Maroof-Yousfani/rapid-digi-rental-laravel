@foreach ($bookingData as $item)
    <tr>
        <td>{{ $item->vehicle->number_plate }}</td>
        <td>
            {{ $item->vehicle->temp_vehicle_detail ??
            ($item->vehicle->vehicle_name.' '.$item->vehicle->car_make .' '.$item->vehicle->year) }}</td>
        <td>Rented</td>
        <td>{{ $item->price }}</td>
    </tr>
@endforeach
