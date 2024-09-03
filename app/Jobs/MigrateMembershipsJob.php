<?php

namespace App\Jobs;

use App\Imports\MigrateMembershipImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Session;

class MigrateMembershipsJob implements ShouldQueue
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
        try{
            logger("=================== START :: MigrateMembershipsJob =================");
            session(['MissingCustomers' => []]);
            $res = Excel::import(new MigrateMembershipImport($this->user_id), $this->file);

            logger("============ END :: MigrateMembershipsJob ============");
        }catch(\Exception $e){
            logger("=================== ERROR :: MigrateMembershipsJob =================");
            logger($e);
        }
    }
}
