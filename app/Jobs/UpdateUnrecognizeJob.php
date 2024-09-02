<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
class UpdateUnrecognizeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user_id = '';
    private $data = '';
    private $user = '';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $user_id)
    {
        $this->data = $data;
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
            logger('============== START:: UpdateUnrecognizeJob ===========');
            $this->user = User::find($this->user_id);
            $theme_id = $this->data['id'];
            $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/themes/' . $theme_id . '/assets.json';
            $assets = $this->user->api()->rest('GET', $endPoint);
            if (!$assets['errors']) {
                $assets = $assets['body']->container['assets'];
                $this->getLiquids($assets);
            }
            logger('============== END:: UpdateUnrecognizeJob ===========');
        } catch (\Exception $e) {
            logger('============== ERROR:: UpdateUnrecognizeJob ===========');
            logger($e->getMessage());
        }
    }

    public function getLiquids($assets)
    {
        try {
            if (count($assets) > 0) {
                foreach ($assets as $key => $asset) {
                    $extension = substr(strrchr($asset['key'], '.'), 1);
                    if ($extension == 'liquid') {
                        $res = $this->updateAssetData($asset['key']);
                    }
                }
            }
        } catch (\Exception $e) {
            logger($e);
        }
    }

    /**
     */
    public function updateAssetData($key)
    {
        try {
            $asset = getLiquidAssetH($this->data['id'], $this->user->id, $key);
            $formIndex = strpos($asset, "form 'product'");

            $isUpdate = false;
            if ($formIndex !== false) {
                if (!strpos($asset, "{% render 'simplee-widget', product:product %}")) {
                    $isUpdate = true;
                    $endFormIndex = (strpos($asset, "%}", $formIndex) + 2);

                    $newAsset = substr_replace($asset, "\n{% render 'simplee-widget', product:product %}", $endFormIndex, 0);
                }
            }

            if ($key == 'templates/customers/account.liquid') {
                if (!strpos($asset, '<a href="/tools/memberships" class="simplee_msl_box">My Membership</a>')) {
                    $isUpdate = true;

                    $newAsset = str_replace('</h1>', "</h1>\n" . '<a href="/tools/memberships" class="simplee_msl_box">My Membership</a>', $asset);
                }
            }

            if ($isUpdate) {
                $parameter['asset']['key'] = $key;
                $parameter['asset']['value'] = $newAsset;
                $result = $this->user->api()->rest('PUT', 'admin/themes/' . $this->data['id'] . '/assets.json', $parameter);
            }
        } catch (\Exception $e) {
            logger($e);
        }
    }
}
