<?php

namespace App\Jobs;

use App\Models\Calender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveCalenderData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $details;
    protected $listing_id;
    /**
     * Create a new job instance.
     */
    public function __construct($details, $listing_id)
    {
        $this->details = $details;
        $this->listing_id = $listing_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->details as $key => $item) {
            $values = [
                'listing_id' => (int) $this->listing_id,
                'availability' => 1,
                'max_stay' => $item['max_stay'],
                'min_stay_through' => $item['min_stay_through'],
                'rate' => $item['rate'],
                'calender_date' => $key,
            ];
            Calender::create($values);
        }
    }
}
