<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Traits\ShopifyTrait;

class PriceUpdateForSCAppImport implements ToModel, WithHeadingRow
{
    use ShopifyTrait;
    private $user_id;
    private $sessionKey;
    private $sessionKey2;

    public function __construct($user_id, $key,$key2)
    {
        Log::info(' ');
        $this->user_id = $user_id;
        $this->sessionKey = $key;
        $this->sessionKey2 = $key2;
    }

    public function model(array $row)
    {
        $user = User::find($this->user_id);
        $this->data['contract_id'] = $row['contract_id'];
        $this->data['price'] = $row['price'];
        $this->updateImportPriceForSC($this->data, $user, $this->sessionKey, $this->sessionKey2);
    }
}
