<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Models\Vehicle;

class VehiclesImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    /**
     * The header row of your Excel sheet
     */
    public function headingRow(): int
    {
        return 2;
    }

    /**
     * Handle each row from the Excel file
     */
    public function collection(Collection $rows)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        foreach ($rows as $row) {
            // dd($row);
            try {
                DB::beginTransaction();

                $numberPlate = trim($row['plate_no'] ?? '');
                $carMakeModelYear = trim($row['car_make_model_year'] ?? '');

                $year = preg_replace('/[^0-9]/', '', $carMakeModelYear);

                $carMakeModel = trim(preg_replace('/\s*\b\d{4}\b\s*/', '', $carMakeModelYear));

                $vehicleName = $carMakeModel;


                $investorId = 13;
                $investorId = 14;

                Vehicle::create([
                    'vehicle_name' => $vehicleName,
                    'temp_vehicle_detail' => $carMakeModelYear,
                    'vehicletypes' => 1,
                    'investor_id' => $investorId,
                    'car_make' => $vehicleName,
                    'year' => $year,
                    'number_plate' => $numberPlate,
                ]);

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Vehicle import error: " . $e->getMessage());
            }
        }
    }

    /**
     * Process large files efficiently (chunks)
     */
    public function chunkSize(): int
    {
        return 500;
    }
}
