<?php

namespace App\Jobs;

use App\Exports\NewestMembersReportExport;
use App\Exports\RecentBillingAttemptsReportExport;
use App\Exports\RecentCancellationReportExport;
use App\Exports\UpcomingRenewalsReportExport;
use App\Mail\ExportSubscribersCsvMail;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Str;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportReportsCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $shopID;
    public $email;
    public $selectedSegmentIndex;
    public $p;
    public $lp;
    public $em;
    public $s;
    /**
     * Create a new job instance.
     */
    public function __construct($shopID, $email, $selectedSegmentIndex, $p, $lp, $em, $s)
    {
        $this->shopID = $shopID;
        $this->email = $email;
        $this->selectedSegmentIndex = $selectedSegmentIndex;
        $this->p = $p;
        $this->lp = $lp;
        $this->em = $em;
        $this->s = $s;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $shop = Shop::find($this->shopID);
            $random = Str::random(16);
            $shop_name = strstr($shop->domain, '.myshopify.com', true);
            $file_name = 'exports/' . $shop->domain . '/' . $shop_name . '_exports_' . $random . '.csv';


            if ($this->selectedSegmentIndex == 1) {
                Excel::store(new UpcomingRenewalsReportExport($this->shopID, $this->selectedSegmentIndex, $this->s, $this->p), $file_name, 's3');
            } else if ($this->selectedSegmentIndex == 2) {
                Excel::store(new RecentBillingAttemptsReportExport($this->shopID, $this->selectedSegmentIndex, $this->s, $this->lp, $this->em), $file_name, 's3');
            } else if ($this->selectedSegmentIndex == 3) {
                Excel::store(new NewestMembersReportExport($this->shopID, $this->selectedSegmentIndex, $this->s), $file_name, 's3');
            } else if ($this->selectedSegmentIndex == 4) {
                Excel::store(new RecentCancellationReportExport($this->shopID, $this->selectedSegmentIndex, $this->s), $file_name, 's3');
            }


            $expires = Carbon::now()->addWeek(); // URL expiration time
            $url = Storage::disk('s3')->temporaryUrl($file_name, $expires);
            Mail::to($this->email)->send(new ExportSubscribersCsvMail($url));


        } catch (\Exception $e) {
            logger('======Error in exporting CSV or sending email======>'.$e);
        }
    }
}
