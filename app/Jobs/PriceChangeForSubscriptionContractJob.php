<?php

namespace App\Jobs;

use App\Imports\PriceUpdateForSCAppImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Shop;

class PriceChangeForSubscriptionContractJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $file;
    private $user_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($file_path, $user_id)
    {
        $this->file = $file_path;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            logger("=================== START :: PriceChangeForSubscriptionContractJob =================");

            $sessionKey = 'PriceUpdateForSCFailed';
            $sessionKey2 = 'PriceUpdateForSCPassed';
            session([$sessionKey => []]);
            session([$sessionKey2 => []]);

            $res = Excel::import(new PriceUpdateForSCAppImport($this->user_id, $sessionKey ,$sessionKey2), $this->file);
            $shop = Shop::where('user_id', $this->user_id)->first();

            $from = config('notify-mails.notify_from_email');
            $to = $shop->email;
            $fromName = config('notify-mails.NOTIFY_FROM_NAME');
            $subject = '    ';

            $user = User::find($this->user_id);
            $data['key'] = $sessionKey;
            $data['key2'] = $sessionKey2;
            $data['shop'] = $user->name;

            $res = Mail::send('mail.priceUpdate', $data, function ($message) use ($subject, $from, $to, $fromName) {
                $message->from($from, $fromName);
                $message->to($to);
                $message->subject($subject);
            });
            unlink($this->file);
            logger("=================== END :: PriceChangeForSubscriptionContractJob =================");
        } catch (\Exception $e) {
            logger("=================== ERROR :: PriceChangeForSubscriptionContractJob =================");
            logger($e);
        }
    }
}
