// function getVehicleByType() {
//     var id = $("#vehicletypes").val();
//     $('#vehicle').empty().append('<option value="">Loading...</option>');
//     $.ajax({
//         url: '/get-vehicle-by-Type/' + id,
//         type: 'GET',
//         success: function(response) {
//             $('#vehicle').empty().append('<option value="">Select Vehicle</option>');
//             $.each(response, function(key, vehicle) {
//                 $('#vehicle').append(
//                     '<option value="' + vehicle.id + '">' + (vehicle.temp_vehicle_detail ?? vehicle.vehicle_name) + '</option>'
//                 );
//             });
//         }
//     });
// }
