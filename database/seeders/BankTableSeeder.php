<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            [
                'bank_name'      => 'Emirates NBD',
                'account_name'   => 'Rapid Digi LLC',
                'account_number' => '101234567890',
                'iban'           => 'AE120260000101234567890',
                'swift_code'     => 'EBILAEAD',
                'branch'         => 'Deira Branch',
                'currency'       => 'AED',
                'notes'          => 'Primary business account',
                'status'         => 1,
            ],
            [
                'bank_name'      => 'Dubai Islamic Bank',
                'account_name'   => 'Rapid Digi LLC',
                'account_number' => '202345678901',
                'iban'           => 'AE370240000202345678901',
                'swift_code'     => 'DUIBAEAD',
                'branch'         => 'Al Rigga Branch',
                'currency'       => 'AED',
                'notes'          => 'Islamic banking services',
                'status'         => 1,
            ],
            [
                'bank_name'      => 'Abu Dhabi Commercial Bank',
                'account_name'   => 'Rapid Digi LLC',
                'account_number' => '303456789012',
                'iban'           => 'AE660030000303456789012',
                'swift_code'     => 'ADCBAEAA',
                'branch'         => 'Bur Dubai Branch',
                'currency'       => 'AED',
                'notes'          => 'Secondary account for vendor payments',
                'status'         => 1,
            ],
            [
                'bank_name'      => 'Mashreq Bank',
                'account_name'   => 'Rapid Digi LLC',
                'account_number' => '404567890123',
                'iban'           => 'AE450330000404567890123',
                'swift_code'     => 'BOMLAEAD',
                'branch'         => 'JLT Branch',
                'currency'       => 'AED',
                'notes'          => 'Used for salary processing',
                'status'         => 1,
            ],
            [
                'bank_name'      => 'RAKBANK',
                'account_name'   => 'Rapid Digi LLC',
                'account_number' => '505678901234',
                'iban'           => 'AE290400000505678901234',
                'swift_code'     => 'RAKBAEAD',
                'branch'         => 'Karama Branch',
                'currency'       => 'AED',
                'notes'          => 'Backup account',
                'status'         => 1,
            ],
        ];

        foreach ($banks as $bank) {
            Bank::create($bank);
        }
    }
}
