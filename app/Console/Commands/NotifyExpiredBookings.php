<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Console\Command;
use App\Events\BookingCreated;

class NotifyExpiredBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-expired-bookings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
        $now = Carbon::now();
        $expiredBookings = Booking::whereHas('bookingData', function ($query) use ($now) {
            $query->where('end_date', '<', $now);
        })
        ->where(function ($query) {
            $query->where('booking_status', 'overdue')
                ->orWhereNull('booking_status');
        })
        ->with(['bookingData' => function ($query) {
            $query->orderBy('end_date', 'desc');
        }])
        ->get();

        foreach ($expiredBookings as $booking) {
            $latestEndDate = optional($booking->bookingData->first())->end_date;
            if ($latestEndDate && Carbon::parse($latestEndDate)->isPast()) {
                $roles = ['admin', 'booker'];
                foreach ($roles as $role) {
                    $alreadyNotified = Notification::where('booking_id', $booking->id)
                        ->where('type', 'booking_expired')
                        ->where('role', $role) // 👈 role-based tracking
                        ->exists();

                    $message = "Booking Agreement# {$booking->agreement_no} has expired (End: $latestEndDate)";

                    // 🔥 Send toaster to all users with this role
                    $users = User::role($role)->get();
                    foreach ($users as $user) {
                        event(new BookingCreated($message, $user->id, $booking->id));
                    }

                    // ✅ DB main sirf 1 record per role
                    if (! $alreadyNotified) {
                        Notification::create([
                            'message' => $message,
                            'vehicle_id' => $booking->bookingData->first()->vehicle_id ?? null,
                            'booking_id' => $booking->id,
                            'type' => 'booking_expired',
                            'role' => $role, // 👈 save role instead of user_id
                        ]);
                    }
                }
            }
        }




        $this->info('Expired booking notifications sent successfully.');
    }
}
