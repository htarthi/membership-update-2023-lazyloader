<?php

namespace App\Imports;

use App\Traits\MigrateTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use App\Models\User;

class MigrateMembershipFromAppImport implements ToModel, WithHeadingRow
{
	use MigrateTrait;

    private $user_id;
    private $data;
    private $sessionKey;

    public function __construct($user_id, $data, $key)
    {
        \Log::info('metafieldImport');
        $this->user_id = $user_id;
        $this->data = $data;
        $this->sessionKey = $key;
    }

    public function model(array $row)
    {
    	$user = User::find($this->user_id);
    	$this->data['firstname'] = $row['firstname'];
    	$this->data['lastname'] = $row['lastname'];
    	$this->data['email'] = $row['email'];
        $this->migrateMember($this->data, $user, $this->sessionKey);
    }
}
