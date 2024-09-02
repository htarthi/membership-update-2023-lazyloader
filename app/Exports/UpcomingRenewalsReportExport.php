<?php

namespace App\Exports;

use App\Models\SsContract;
use App\Models\SsPlanGroup;
use App\Models\Shop;
use App\Models\SsAnswer;
use App\Models\SsActivityLog;
use App\Models\SsBillingAttempt;
use App\Models\SsCancellation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class UpcomingRenewalsReportExport implements FromCollection, WithHeadings
{
    private $shopID;
    private $email;
    private $s;
    private $selectedSegmentIndex;
    private $lp;

    public function __construct($id, $selectedSegmentIndex, $s, $p)
    {
        $this->shopID = $id;
        $this->selectedSegmentIndex = $selectedSegmentIndex;
        $this->s = $s;
        $this->p = $p;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $entities = $this->getData();
        $entity = $this->createFile($entities);
        return collect($entity);
    }

    public function getData()
    {
        try {
            $s = $this->s;

            $upcoming_renewals = SsContract::join('ss_customers', 'ss_contracts.ss_customer_id', '=', 'ss_customers.id')
                ->join('shops', 'ss_customers.shop_id', '=', 'shops.id')->join('ss_plans', 'ss_contracts.ss_plan_id', '=', 'ss_plans.id')
                ->where('ss_contracts.status', 'active')->whereDate('ss_contracts.next_processing_date', '>', Carbon::today())
                ->join('ss_contract_line_items', 'ss_contracts.id', '=', 'ss_contract_line_items.ss_contract_id')
                ->where('ss_contracts.shop_id', $this->shopID)->where('ss_contracts.shopify_contract_id', '!=', null)
                ->where('ss_contracts.is_onetime_payment', 0)
                ->select([
                    'ss_contracts.id',
                    'ss_contracts.member_number',
                    'ss_contracts.next_processing_date',
                    'ss_contracts.next_order_date',
                    'ss_contracts.pricing_adjustment_value',
                    'ss_contracts.failed_payment_count',
                    'ss_contracts.created_at',
                    'ss_contracts.order_count',
                    'ss_contracts.trial_available',
                    'ss_contracts.pricing2_adjustment_value',
                    'ss_customers.first_name',
                    'ss_customers.last_name',
                    'ss_plans.name',
                    'ss_plans.trial_days',
                    'ss_plans.pricing2_after_cycle',
                    'shops.domain',
                    'shops.iana_timezone',
                    'shops.currency_symbol',
                    'ss_customers.shopify_customer_id',
                    'ss_contracts.currency_code',
                    'ss_contract_line_items.discount_amount',
                    'ss_plans.name as plan_name',
                    'ss_contracts.shopify_contract_id',
                    'ss_contracts.status',
                    'ss_contracts.status_display',
                    'ss_customers.email',
                    'ss_customers.phone',
                    'ss_contracts.cc_id',
                    'ss_contracts.order_count'

                ])
                ->orderBy('ss_contracts.next_order_date', 'asc');

            if (!empty($s)) {
                $upcoming_renewals->where(function ($query) use ($s) {
                    $query->Where('ss_customers.first_name', 'LIKE', '%' . $s . '%')
                        ->orWhere('ss_customers.last_name', 'LIKE', '%' . $s . '%');
                });
            }

            $results = $upcoming_renewals->get();
            return $results;
        } catch (\Exception $e) {
            logger('Error retrieving upcoming renewals data:' . $e);
            return collect([]);
        }
    }

    /**
     * @param $entities
     * @return mixed
     */
    public function createFile($entities)
    {
        logger('----------create File--------------');
        if ($entities) {
            $entity = $entities->map(function ($name) {
                $queans = SsAnswer::select('question', 'answer')->where('ss_contract_id', $name->id)->get()->toArray();
                $activityLog = SsActivityLog::select('created_at as date', 'message as log')->where('ss_contract_id', $name->id)->where('shop_id', $this->shopID)->where('user_type', 'user')->get()->toArray();
                $billing = SsBillingAttempt::select('status', 'errorMessage')->where('ss_contract_id', $name->id)->orderBy('created_at', 'desc')->first();
                $cancellation = SsCancellation::select('reason', 'created_at')->where('ss_contract_id', $name->id)->where('shop_id', $this->shopID)->orderBy('created_at', 'desc')->first();

                $data = [
                    'Member Number' => str_pad($name->member_number, 6, "0", STR_PAD_LEFT),
                    'Contract ID' => $name->shopify_contract_id,
                    'status' => ucfirst($name->status),
                    'Status - Extended' => ucfirst($name->status_display),
                    'Customer Name' => $name->first_name . ' ' . $name->last_name,
                    'Customer Email' => $name->email,
                    'Customer Phone' => $name->phone,
                    'Customer ID' => $name->shopify_customer_id,
                    'Payment Method ID' => $name->cc_id,
                    'Membership Plan Name' => $name->plan_name,
                    'Start Date' => $name->created_at,
                    'Renewal Date' => $name->next_order_date,
                    'Order Count' => $name->order_count,
                    'Customer Answers' => (count($queans) > 0) ? json_encode($queans, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '',
                    'Activity Log' => (count($activityLog) > 0) ? json_encode($activityLog) : '',
                    'Last Billing Attempt Status' => ($billing) ? $billing->status : '',
                    'Payment Failure Reason' => ($billing) ? $billing->errorMessage : '',
                    'Cancellation Reason' => ($cancellation) ? $cancellation->reason : '',
                    'Cancellation Date' => ($cancellation) ? $cancellation->created_at : ''
                ];
                logger("COUNT===>");
                logger($data);


                return $data;
            });
        }
        return $entity;
    }

    public function headings(): array
    {
        return [
            'Member Number',
            'Contract ID',
            'status',
            'Status - Extended',
            'Customer Name',
            'Customer Email',
            'Customer Phone',
            'Customer ID',
            'Payment Method ID',
            'Membership Plan Name',
            'Start Date',
            'Renewal Date',
            'Order Count',
            'Customer Answers',
            'Activity Log',
            'Last Billing Attempt Status',
            'Payment Failure Reason',
            'Cancellation Reason',
            'Cancellation Date',
        ];
    }
}
