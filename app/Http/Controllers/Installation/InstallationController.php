<?php

namespace App\Http\Controllers\Installation;

use App\Http\Controllers\Controller;
use App\Models\SsThemeInstall;
use App\Jobs\UpdateUnrecognizeJob;
use Illuminate\Http\Request;
use App\Traits\ShopifyTrait;

class InstallationController extends Controller
{
    use ShopifyTrait;
    public function index()
    {
        $shop = getShopH();
        $response['eligibleForSubscriptions'] = $this->shopFeature($shop->user_id);
        $response['themes'] = $this->getThemes();
        $response['name'] = $shop->myshopify_domain;
        return response()->json(['data' => $response], 200);
    }

    public function installWidget(Request $request)
    {
        try {
            $shop = getShopH();
            $data = $request->data;

            $schema = $this->getThemeSchema($shop->user_id, $data['id']);

            if ($schema['status']) {
                $schema = $schema['data'][0];

                $themeInstall = new SsThemeInstall;
                $themeInstall->shop_id = $shop->id;
                $themeInstall->theme_id =  $data['id'];
                $themeInstall->theme_name =  (@$schema->theme_name) ? $schema->theme_name : 'Unrecognized theme';
                $themeInstall->theme_version =  (@$schema->theme_version) ? $schema->theme_version : 'Unrecognized theme';
                $themeInstall->theme_author =  (@$schema->theme_author) ? $schema->theme_author : 'Unrecognized theme';
                $themeInstall->install_status = 'started';
                $themeInstall->save();

                $data['version'] = (@$schema->theme_version) ? $schema->theme_version : '1.0.0';

                $data['name'] = (@$schema->theme_name) ? $schema->theme_name : '';

                installWidgetH($data, $shop->user_id, 'default');

                $themeInstall->install_status = 'files created';
                $themeInstall->save();

                $constants = getConstantH($data);

                if (!@$schema->theme_name || $constants == '') {
                    UpdateUnrecognizeJob::dispatch($data, $shop->user_id)->onQueue('UpdateServer');
                    return response()->json(['data' => 'Theme updated successfully', 'status' => true], 200);
                }

                $res = updateFilesH($data, $shop->user_id);

                if ($res['success']) {
                    $themeInstall->install_status = 'success';
                    $themeInstall->save();
                    return response()->json(['data' => $res['msg'], 'status' => true], 200);
                } else {
                    $themeInstall->install_status = 'failed';
                    $themeInstall->install_messages = $res['msg'];
                    $themeInstall->save();
                    return response()->json(['data' => $res['msg'], 'status' => false], 200);
                }
            } else
                return response()->json(['data' => $schema['data'], 'status' => false], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  installWidget =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }
}
