<?php

namespace App\Jobs;

use App\Exports\SubscribersExport;
use App\Mail\ExportSubscribersCsvMail;
use Illuminate\Support\Str;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
class ExportSubscribersCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $type;
    public $s;
    public $p;
    public $lp;
    public $shopID;
    public $email;


    /**
     * Create a new job instance.
     */
    public function __construct($type, $s, $p, $lp,$shopID , $email)
    {
        $this->type = $type;
        $this->s = $s;
        $this->p = $p;
        $this->lp = $lp;
        $this->shopID = $shopID;
        $this->email = $email;

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        logger($this->shopID);
        $shop = Shop::find($this->shopID);
        $random = Str::random(16);
        $shop_name   = strstr($shop->domain, '.myshopify.com', true);
        $file_name = 'exports/' . $shop->domain  . '/' . $shop_name .  '_exports_' . $random .  '.csv';
        Excel::store(new SubscribersExport($this->type, $this->s, $this->p,$this->lp, $this->shopID), $file_name, 's3');

        $expires = Carbon::now()->addWeek(); // URL expiration time

        $url =   Storage::disk('s3')->temporaryUrl($file_name, $expires);

        Mail::to($this->email)->send(new ExportSubscribersCsvMail($url));
    }
}
