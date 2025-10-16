<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Hostaboard;
use App\Mail\HostRentalLeaseReminderMail;
use Carbon\Carbon;
use App\Models\HostRentalLease;
use Illuminate\Support\Facades\Mail;

class hostrentalleasecommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:hostrentallease';

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
        $today = Carbon::today()->toDateString();
        $this->info("Today's Date: " . $today);

        // Get hostaboards with pending leases
        $hostaboards = Hostaboard::join('host_rental_lease', 'hostaboard.id', '=', 'host_rental_lease.hostaboard_id')
            ->whereNotNull('hostaboard.host_rental_lease')
            ->where('host_rental_lease.status', 'pending') 
            ->select('hostaboard.*', 'host_rental_lease.id as lease_task_id')
            ->get();

        $this->info("Total Records Found: " . $hostaboards->count());

        // Reminder sequence in days
        $reminderDays = [60, 45, 30];

        foreach ($hostaboards as $activation) {

            $lease = HostRentalLease::where('id', $activation->lease_task_id)
                        ->where('status', 'pending')
                        ->first();

            if (!$lease) {
                $this->info("⏩ Lease not pending, skipping: {$activation->title}");
                continue;
            }

            $leaseDate = Carbon::parse($activation->host_rental_lease);

            $this->info("Checking Hostaboard: {$activation->title}");
            $this->info("Lease Date: {$leaseDate->toDateString()}");

            $reminderSent = false;

            foreach ($reminderDays as $days) {
                $reminderDate = $leaseDate->copy()->subDays($days)->toDateString();

                if ($reminderDate === $today) {
                    $this->info("✅ Sending {$days}-day reminder for {$activation->title}");

                    Mail::to("pawan@livedin.co")->send(
                        new HostRentalLeaseReminderMail($activation, $lease)
                    );

                    notifyheader(
                        9,
                        'Host Rental Lease',
                        $activation->lease_task_id,
                        "{$activation->title} Host Rental Lease",
                        "Review",
                        url("/host-rental-lease/{$activation->lease_task_id}/edit"),
                        false
                    );
                    // Update email status if first reminder or as needed
                    $lease->email_status = 1;
                    $lease->visible = 1;
                    $lease->save();

                    

                    $this->info("📧 Email sent successfully!");
                    $reminderSent = true;
                    break; // stop checking smaller reminder days
                }
            }

            if (!$reminderSent) {
                $this->info("⏩ No reminder today for this record.");
            }
        }

        $this->info("Command execution finished!");
    }

}
