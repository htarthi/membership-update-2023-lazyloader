<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Feature\FeatureController;
use App\Http\Controllers\Installation\InstallationController;
use App\Http\Controllers\Migrate\MigrateController;
use App\Http\Controllers\Migrate\UpdateContractController;
use App\Http\Controllers\Plan\PlanController;
use App\Http\Controllers\Portal\LiquidPortalController;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\Setting\SettingController;
use App\Http\Controllers\Setting\TranslationController;
use App\Http\Controllers\Shipping\ShippingController;
use App\Http\Controllers\Subscriber\SubscriberController;
use App\Http\Controllers\Test\MissingContractsController;
use App\Http\Controllers\Test\ScriptController;
use App\Http\Controllers\Test\TestController;
use App\Http\Controllers\User\SuperUserController;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Route::get('reports',[DashboardController::class,'getReportsData'])->name('getReportsData');

Route::get('/login', [AuthController::class, 'login'])->name('login');

// Route::group(['middleware' => 'verify.shopify'], function () {
Route::post('/event', [PlanController::class, 'addEvent'])->name('event');
Route::get('/app-plan/{userID}', [PlanController::class, 'appPlanIndex'])->name('app-plan');

Route::get('/mbilling/{plan?}/{name?}', [PlanController::class, 'appPlanChange'])->name('mbilling');

Route::get('/freebilling/{action}/{userId?}', [PlanController::class, 'appFreePlan'])->name('freebilling');
//change plan
Route::get('/change-plan-db/{id?}', [PlanController::class, 'changePlanDB'])->name('change-plan-db');

Route::get('/subscriptions', [DashboardController::class,'subscriptionsBladeindex'])->middleware(['verify.shopify']);



Route::group(['middleware' => ['verify.shopify']], function () {

    Route::get('/subscriber/test', function () {
        return response()->json([
            "success" => true,
            "data" => [
                "Some" => "Thing"
            ]
        ], 200);
    });

    //Export
    Route::get('/subscribers/export/{shopID}/{type}/{p}/{lp}/{s?}', [SubscriberController::class, 'export']);

    //dashboard
    Route::group([], function () {
        Route::middleware('secure.headers')->resource('dashboard', DashboardController::class);
        Route::post('replace-lineitems', [DashboardController::class, 'replaceLineItem'])->name('replace-lineitems');
    });

    //subscriber
    Route::resource('/subscriber', SubscriberController::class);
    Route::get('/billng-attempts/{id}', [SubscriberController::class, 'billingattempts']);
    Route::put('/subscriber/{id}/shipping/update', [SubscriberController::class, 'uppdateCustomerDetail'])->name('update-shiiping-details');
    Route::post('/save-comment', [SubscriberController::class, 'saveComment'])->name('save-comment');
    Route::post('/save-lineitems', [SubscriberController::class, 'saveLineItem'])->name('save-lineitems');
    Route::get('/country/{country?}', [SubscriberController::class, 'getCountry'])->name('country');

    //plans
    Route::get('/check-activePlan', [PlanController::class, 'checkActivePlan'])->name('check-activePlan');
    Route::get('/plan-group', [PlanController::class, 'planGroupIndex'])->name('plan-group');
    Route::post('/plan-group', [PlanController::class, 'planGroupStore'])->name('plan-group');
    Route::post('/tiers/store', [PlanController::class, 'storeAllTiers'])->name('tiers.store');
    Route::get('/plans/export/{shopID}', [PlanController::class, 'plansExport'])->name('plans.export');
    Route::get('/plan-group.edit/{id?}', [PlanController::class, 'planGroupEdit'])->name('plan-group.edit');
    Route::get('/restricated-contents',[PlanController::class,'restricated_contents'])->name('restricated.contents');
    Route::delete('/plan-group/{id}/delete', [PlanController::class, 'planGroupDestroy'])->name('plan-group.delete');

    // plan
    Route::post('/plan', [PlanController::class, 'storeTier'])->name('plan.store');
    Route::get('/plan/{id}/edit', [PlanController::class, 'planEdit'])->name('plan.edit');
    Route::get('/check-is-selling-plan-exists/{id}', [PlanController::class, 'checkIsSellingPlanExists'])->name('plan.checkIsSellingPlanExists');
    Route::delete('/plan/{id}/delete', [PlanController::class, 'planDestroy'])->name('plan.delete');

    // contract
    Route::get('/update-contract', [UpdateContractController::class, 'index'])->name('update-contract');

    //product
    Route::post('/assign-product', [PlanController::class, 'assignProduct'])->name('assign-product');

    //position
    Route::post('/position', [PlanController::class, 'position'])->name('position');

    // Migration from app
    Route::post('/merchantmigrate', [MigrateController::class, 'merchantMembershipMigration'])->name('merchantmigrate');

    //settings
    Route::resource('/setting', SettingController::class);
    Route::post('/addNewReason', [SettingController::class, 'addNewReason'])->name('addNewReason');

    //mail
    Route::post('/mail', [SettingController::class, 'sendMail'])->name('mail');
    Route::post('/email-body', [SettingController::class, 'emailBody'])->name('email-body');

    //images
    Route::post('/upload-image', [SettingController::class, 'uploadImage'])->name('upload-image');

    //customer portal
    Route::get('/portal-status', [SettingController::class, 'changePortal'])->name('portal-status');

    //theme install
    Route::post('/install-theme', [SettingController::class, 'installTheme'])->name('install-theme');

    //Installations
    Route::get('/get-installation-config', [InstallationController::class, 'index']);
    Route::post('/install-widget', [InstallationController::class, 'installWidget']);

    //Shipping
    Route::resource('shipping', ShippingController::class);

    // translations
    Route::get('/translations', [TranslationController::class, 'index'])->name('translations.index');
    Route::post('/translations', [TranslationController::class, 'store'])->name('translations.store');

    // Portal
    Route::get('/get-theme-files', [LiquidPortalController::class, 'getThemeFiles']);
    Route::post('/store-theme-files', [LiquidPortalController::class, 'storeThemeFiles']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'dashboardgetdata']);
    Route::get('/upcoming-renewals', [DashboardController::class, 'upcoming_renewals'])->name('upcoming-renewals');
    Route::get('/recent_billing_attempts', [DashboardController::class, 'recent_billing_attempts'])->name('recent_billing_attempts');
    Route::get('/other-reports', [DashboardController::class, 'getReportsData']);
    Route::get('/newest_members', [DashboardController::class, 'newest_members'])->name('newest_members');
    Route::get('/recent_cancellation', [DashboardController::class, 'recent_cancellation'])->name('recent_cancellation');
    Route::get('/getUserDetails', [DashboardController::class, 'getUserDetails']);
    Route::get('/reports/export/{shopID}/{email}/{selectedSegmentIndex}', [DashboardController::class, 'reportExport']);




    Route::group(['namespace' => 'Dashboard'], function () {
        Route::get('/update-products', [DashboardController::class,'updateProductsData'])->name('update-products-data');
    });
    Route::get('/in-maintanace', [DashboardController::class,'checkMaintenance'])->name('check-maintenace');
    // Active Plans
    Route::get('/ActivePlans', [DashboardController::class, 'active_plans'])->name('ActivePlans');
});

Route::get('/test', [TestController::class, 'test']);
Route::get('/scriptRun', [ScriptController::class, 'discountStatusChange']);



//feature
Route::group(['prefix' => '/feature'], function () {
    Route::get('/add/{name}', [FeatureController::class, 'add']);
    Route::get('/enable/{name}/{id}', [FeatureController::class, 'enableFor']);
    Route::get('/disable/{name}/{id}', [FeatureController::class, 'disableFor']);
    Route::get('/remove/{name}/{id}', [FeatureController::class, 'remove']);
    Route::get('/addcustomplan/{user_id}/{plan_id}', [FeatureController::class, 'createCustomPlan']);
});

//proxy
Route::group(['prefix' => 'memberships', 'middleware' => 'auth.proxy'], function () {
    Route::get('/', [PortalController::class, 'index']);
});

//super user
Route::get('super-user', [SuperUserController::class, 'index'])->name('super-user');
Route::group(['prefix' => 'superuser'], function () {
    Route::get('get-shop', [SuperUserController::class, 'getShop']);
    Route::post('upload-csv', [SuperUserController::class, 'uploadCsv']);
});


Route::get('/keep-alive', function () {
    return response()->json(['ok' => true]);
})->middleware('auth');

//flush session
Route::get('flush', function () {
    request()->session()->flush();
});

    // Route::get('/dashboard', [DashboardController::class, 'dashboardgetdata'])->middleware(['verify.shopify', 'billable']) ;


Route::get('/update-contract', [UpdateContractController::class, 'index'])->name('update-contract');
// missing-contract
Route::get('/add-missing-contract/{shopName}', [MissingContractsController::class, 'addMissingContract'])->name('addMissingContract');
Route::get('/register-missing-shop/{shopName}', [MissingContractsController::class, 'regesterMissingShop'])->name('regesterMissingShop');

// Add missing Cusromer Tags
Route::get('/add-missing-customer-tags/{shopName}', [MissingContractsController::class, 'addMissingCustomerTags'])->name('regesterMissingShop');

// Add Missing contract records which is already created in shopify
Route::get('/add-missing-contract-records', [MissingContractsController::class, 'addMissingContractRecord'])->name('addMissingcontractRecord');
Route::post('/add-missing-contract-records', [MissingContractsController::class, 'addMissingContractRecords'])->name('addMissingcontractRecords');

Route::get('/check-successful-billing-attempt/{billingAttempt}', [MissingContractsController::class, 'checkSuccessfulBillingAttempt'])->name('checkSuccessfulBillingAttempt');

// Migration
Route::post('migrate', [MigrateController::class, 'index']);
Route::post('onetime-migrate', [MigrateController::class, 'onetimeMigrate']);

Route::post('/updatePriceForSC', [UpdateContractController::class, 'updatePriceForSC2']);
Route::get('/checkPlans', [TestController::class, 'checkPlans'])->middleware(['verify.shopify']);

// Migrate Missing Contracts
Route::get('/create-contract', [TestController::class, 'createMissingMembershipShopify']);


Route::get('/pbilling/{plan?}/{name?}', [PlanController::class, 'appPlanChange'])->name('pbilling');



Route::get('/getmetafields/{name?}', [TranslationController::class, 'getmetafields'])->name('getmetafields');
Route::get('/getCustomerShop/{id?}', [TranslationController::class, 'getCustomerShop'])->name('getCustomerShop');

// Please keep this route snippet last
Route::controller(AuthController::class)->group(function (Router $router) {
    $router->get('/', 'index')->name('home')->middleware(['verify.shopify']);
    $router->get('/{any}', 'index')->where('any', '(.+)?');
});
