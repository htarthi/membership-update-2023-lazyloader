<?php

namespace App\Jobs;


use App\Imports\MigrateMembershipFromAppImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Shop;
use App\Traits\ShopifyTrait;
use Illuminate\Support\Facades\Cache;

class MigrateMembershipFromAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ShopifyTrait;

    private $file;
    private $user_id;
    private $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($file_path, $user_id, $data)
    {
        $this->file = $file_path;
        $this->user_id = $user_id;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            logger("=================== START :: MigrateMembershipFromAppJob =================");

            $sessionKey = 'MissingCustomers' . $this->user_id;
            session([$sessionKey => []]);

            $res = Excel::import(new MigrateMembershipFromAppImport($this->user_id, $this->data, $sessionKey), $this->file);

            $shop = Shop::where('user_id', $this->user_id)->first();

            $from = config('notify-mails.notify_from_email');
            $to = $shop->email;
            $fromName = config('notify-mails.NOTIFY_FROM_NAME');
            $subject = 'Memberships imported successfully';

            $user = User::find($this->user_id);
            $data['key'] = $sessionKey;
            $data['shop'] = $user->name;

            $user = User::find($this->user_id);
            $expired = $this->is_membership_expired($user);
            $this->membershipexpireMetaUpdate($user, $expired );


            $res = Mail::send('mail.migrate', $data, function ($message) use ($subject, $from, $to, $fromName) {
                $message->from($from, $fromName);
                $message->to($to);
                $message->subject($subject);
            });
            unlink($this->file);
            Cache::forget($shop->id);
            logger("=================== END :: MigrateMembershipFromAppJob =================");
        } catch (\Exception $e) {
            logger("=================== ERROR :: MigrateMembershipFromAppJob =================");
            logger($e);
        }
    }
}
