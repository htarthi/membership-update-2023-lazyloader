<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Shop;
use App\Models\SsPlanGroup;
use App\Models\SsPortal;
use App\Models\User;

class ScriptController extends Controller
{


    public function scriptRun($name)
    {

        //===========================START :: liquidFilesUpdate (Custom Reason Functionality) ==============================//
        if ($name === 'notcustomizedstore') {
            $liquidFilePath = resource_path('views/compared_portalliquid.php');
            $liquidFileContent = File::get($liquidFilePath);

            $cssFilePath = resource_path('views/compared_portalcss.php');
            $cssFileContent = File::get($cssFilePath);

            $jsFilePath = resource_path('views/compared_portaljs.php');
            $jsFileContent = File::get($jsFilePath);

            $newjsFilePath = resource_path('views/compared_portalnewjs.php');
            $newjsFileContent = File::get($newjsFilePath);

            $newliquidFilePath = resource_path('views/compared_portalnewliquid.php');
            $newliquidFileContent = File::get($newliquidFilePath);

            $newcssFilePath = resource_path('views/compared_portalnewcss.php');
            $newCssFileContent = File::get($newcssFilePath);


            $shop = Shop::get();
            echo  "<b>Not Customized Store List</b><br></br><hr>";
            foreach ($shop as $vals) {
                $portal = SsPortal::where('shop_id', $vals->id)->first();
                if ($portal) {

                    $databaseFieldContent = $portal->portal_liquid;
                    $normalizedLiquidFileContent = $this->normalizeWhitespace($liquidFileContent);
                    $normalizedDatabaseFieldContent = $this->normalizeWhitespace($databaseFieldContent);

                    $cssdatabaseFieldContent = $portal->portal_css;
                    $normalizedCssFileContent = $this->normalizeWhitespace($cssFileContent);
                    $normalizedDatabaseFieldCssContent = $this->normalizeWhitespace($cssdatabaseFieldContent);

                    $jsdatabaseFieldContent = $portal->portal_js;
                    $normalizedjsFileContent = $this->normalizeWhitespace($jsFileContent);
                    $normalizedDatabaseFieldjsContent = $this->normalizeWhitespace($jsdatabaseFieldContent);


                    $newjsdatabaseFieldContent = $portal->portal_js;
                    $normalizednewjsFileContent = $this->normalizeWhitespace($newjsFileContent);
                    $normalizedDatabaseFieldnewjsContent = $this->normalizeWhitespace($newjsdatabaseFieldContent);

                    $newliquiddatabaseFieldContent = $portal->portal_liquid;
                    $normalizednewLiquidFileContent = $this->normalizeWhitespace($newliquidFileContent);
                    $normalizedDatabaseFieldnewliquidContent = $this->normalizeWhitespace($newliquiddatabaseFieldContent);

                    $newcssdatabaseFieldContent = $portal->portal_css;
                    $normalizednewCssFileContent = $this->normalizeWhitespace($newCssFileContent);
                    $normalizedDatabaseFieldnewCssContent = $this->normalizeWhitespace($newcssdatabaseFieldContent);

                    if ($normalizedLiquidFileContent === $normalizedDatabaseFieldContent || $normalizednewLiquidFileContent === $normalizedDatabaseFieldnewliquidContent) {
                        $C_Liquid = true;
                    } else {
                        $C_Liquid = false;
                    }
                    if ($normalizedCssFileContent === $normalizedDatabaseFieldCssContent || $normalizednewCssFileContent === $normalizedDatabaseFieldnewCssContent) {
                        $C_CSS = true;
                    } else {
                        $C_CSS = false;
                    }
                    if ($normalizedjsFileContent === $normalizedDatabaseFieldjsContent || $normalizednewjsFileContent === $normalizedDatabaseFieldnewjsContent) {
                        $C_JS = true;
                    } else {
                        $C_JS = false;
                    }
                    if ($C_Liquid && $C_CSS && $C_JS) {
                        $user = User::where('id', $vals->user_id)->first();
                        if ($user) {

                            echo   "<b> ID :: </b>" . $vals->user_id . "<b>  Domain:: </b>" . $user->name;
                            echo  "<br></br>";
                        }
                    }
                }
            }
        }

        if ($name === 'customizedstore') {

            $liquidFilePath = resource_path('views/compared_portalliquid.php');
            $liquidFileContent = File::get($liquidFilePath);

            $cssFilePath = resource_path('views/compared_portalcss.php');
            $cssFileContent = File::get($cssFilePath);

            $jsFilePath = resource_path('views/compared_portaljs.php');
            $jsFileContent = File::get($jsFilePath);

            $newjsFilePath = resource_path('views/compared_portalnewjs.php');
            $newjsFileContent = File::get($newjsFilePath);

            $newliquidFilePath = resource_path('views/compared_portalnewliquid.php');
            $newliquidFileContent = File::get($newliquidFilePath);

            $newcssFilePath = resource_path('views/compared_portalnewcss.php');
            $newCssFileContent = File::get($newcssFilePath);


            $shop = Shop::get();
            echo  "<b>Customized Store List</b><br></br><hr>";
            foreach ($shop as $vals) {
                $portal = SsPortal::where('shop_id', $vals->id)->first();
                if ($portal) {

                    $databaseFieldContent = $portal->portal_liquid;
                    $normalizedLiquidFileContent = $this->normalizeWhitespace($liquidFileContent);
                    $normalizedDatabaseFieldContent = $this->normalizeWhitespace($databaseFieldContent);

                    $cssdatabaseFieldContent = $portal->portal_css;
                    $normalizedCssFileContent = $this->normalizeWhitespace($cssFileContent);
                    $normalizedDatabaseFieldCssContent = $this->normalizeWhitespace($cssdatabaseFieldContent);

                    $jsdatabaseFieldContent = $portal->portal_js;
                    $normalizedjsFileContent = $this->normalizeWhitespace($jsFileContent);
                    $normalizedDatabaseFieldjsContent = $this->normalizeWhitespace($jsdatabaseFieldContent);


                    $newjsdatabaseFieldContent = $portal->portal_js;
                    $normalizednewjsFileContent = $this->normalizeWhitespace($newjsFileContent);
                    $normalizedDatabaseFieldnewjsContent = $this->normalizeWhitespace($newjsdatabaseFieldContent);

                    $newliquiddatabaseFieldContent = $portal->portal_liquid;
                    $normalizednewLiquidFileContent = $this->normalizeWhitespace($newliquidFileContent);
                    $normalizedDatabaseFieldnewliquidContent = $this->normalizeWhitespace($newliquiddatabaseFieldContent);

                    $newcssdatabaseFieldContent = $portal->portal_css;
                    $normalizednewCssFileContent = $this->normalizeWhitespace($newCssFileContent);
                    $normalizedDatabaseFieldnewCssContent = $this->normalizeWhitespace($newcssdatabaseFieldContent);

                    if ($normalizedLiquidFileContent === $normalizedDatabaseFieldContent || $normalizednewLiquidFileContent === $normalizedDatabaseFieldnewliquidContent) {
                        $C_Liquid = false;
                    } else {
                        $C_Liquid = true;
                    }
                    if ($normalizedCssFileContent === $normalizedDatabaseFieldCssContent || $normalizednewCssFileContent === $normalizedDatabaseFieldnewCssContent) {
                        $C_CSS = false;
                    } else {
                        $C_CSS = true;
                    }
                    if ($normalizedjsFileContent === $normalizedDatabaseFieldjsContent || $normalizednewjsFileContent === $normalizedDatabaseFieldnewjsContent) {
                        $C_JS = false;
                    } else {
                        $C_JS = true;
                    }
                    if ($C_Liquid || $C_CSS || $C_JS) {
                        echo   "<b> ID :: </b>" . $vals->id . "<b>  Domain:: </b>" . $vals->myshopify_domain;
                        echo  "<br></br>";
                    }
                }
            }
        }

        // if($name === 'liquidFilesUpdate') {
        //     $liquidFilePath = resource_path('views/test.php');
        //     $liquidFileContent = File::get($liquidFilePath);
        //     $shop = Shop::get();
        //     foreach($shop as $vals){
        //         $portal = SsPortal::where('shop_id', $vals->id)->first();
        //         if($portal){
        //             $databaseFieldContent = $portal->portal_liquid;
        //             $normalizedLiquidFileContent = $this->normalizeWhitespace($liquidFileContent);
        //             $normalizedDatabaseFieldContent = $this->normalizeWhitespace($databaseFieldContent);
        //             if ($normalizedLiquidFileContent === $normalizedDatabaseFieldContent) {
        //                 // $portal->portal_liquid = getPortalLiquidH();
        //                 // $portal->portal_css = getPortalCssH();
        //                 // $portal->portal_js = getPoratlJsH();
        //                 // $portal->save();
        //                 echo  "<b>Updated Shop  :: </b>" . $vals->name;
        //                 echo  "<br></br>";
        //             } else {
        //                 echo "Content Not Match :: " . $vals->name;
        //                 echo  "<br></br>";
        //             }
        //         }
        //     }
        // }
        //===========================END   :: liquidFilesUpdate (Custom Reason Functionality) ==============================//
    }

    function normalizeWhitespace($string)
    {
        return preg_replace('/\s+/', ' ', trim($string));
    }

    public function discountStatusChange(){

        return SsPlanGroup::where('discount_code', '!=', null)
          ->update([
              'discount_type' => 3
        ]);

        // return SsPlanGroup::where('discount_type',0)
        //     ->update([
        //         'discount_type' => 1
        //     ]);

        // return SsPlanGroup::where(['discount_code' => null ])->update([
        //     'discount_type' => 1
        // ]);

    }
}
