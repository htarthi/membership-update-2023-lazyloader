<?php

namespace App\Exports;
use App\Models\SsAnswer;
use App\Models\SsActivityLog;
use App\Models\SsBillingAttempt;
use App\Models\SsCancellation;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\FromCollection;

class RecentBillingAttemptsReportExport implements FromCollection, WithHeadings
{
    private $shopID;
    private $email;
    private $selectedSegmentIndex;
    private $s;
    private $lp;
    private $em;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($id,$selectedSegmentIndex,$s,$lp,$em)
    {
        $this->shopID = $id;
        $this->selectedSegmentIndex = $selectedSegmentIndex;
        $this->s = $s;
        $this->lp = $lp;
        $this->lp = $lp;

    }
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
            $lp = $this->lp;
            $em = $this->em;



            $attempts = SsBillingAttempt::join('ss_contracts', 'ss_billing_attempts.ss_contract_id', '=', 'ss_contracts.id')
                ->join('ss_plans', 'ss_contracts.ss_plan_id', '=', 'ss_plans.id')
                ->join('ss_customers', 'ss_contracts.ss_customer_id', '=', 'ss_customers.id')
                ->join('shops', 'ss_customers.shop_id', '=', 'shops.id')
                ->leftJoin('ss_orders', 'ss_billing_attempts.shopify_order_id', '=', 'ss_orders.shopify_order_id')
                ->where('ss_billing_attempts.shop_id', $this->shopID)
                ->select([
                    'ss_billing_attempts.id',
                    'ss_billing_attempts.completedAt',
                    'ss_billing_attempts.status',
                    'ss_contracts.member_number',
                    'ss_contracts.id',
                    'ss_customers.first_name',
                    'ss_customers.last_name',
                    'ss_billing_attempts.errorMessage',
                    'ss_orders.order_amount',
                    'ss_orders.shopify_order_name',
                    'shops.domain',
                    'shops.myshopify_domain',
                    'shops.currency_symbol',
                    'shops.iana_timezone',
                    'ss_orders.shopify_order_id',
                    'ss_plans.name as plan_name','ss_contracts.next_order_date','ss_contracts.created_at','ss_contracts.shopify_contract_id','ss_contracts.status', 'ss_contracts.status_display', 'ss_customers.email','ss_customers.phone', 'ss_customers.shopify_customer_id','ss_contracts.cc_id','ss_contracts.order_count'

                ])
                ->orderBy('ss_billing_attempts.created_at', 'desc');

                if (isset($s) &&!empty($s)) {
                    $attempts->where(function ($query) use ($s) {
                        $query->Where('ss_customers.first_name', 'LIKE', '%' . $s . '%')
                            ->orWhere('ss_customers.last_name', 'LIKE', '%' . $s . '%');
                    });
                }

                if (isset($lp) &&!empty($lp)) {
                    $statusArr = explode(',', $lp);
                    $attempts->whereIn('ss_billing_attempts.status', $statusArr);
                }

                if (isset($em) &&!empty($em)) {
                    $statusArr = explode(',', $em);
                    $attempts->whereIn('ss_billing_attempts.errorMessage', $statusArr);
                }

            $results = $attempts->get();
            return $results;

        } catch (\Exception $e) {
            logger('Error retrieving recent billing attempts data:'. $e);
            return collect([]);
        }
    }

    public function createFile($entities)
    {
        logger('----------create File--------------');
        if ($entities) {
            $entity = $entities->map(function ($name) {

                $queans = SsAnswer::select('question', 'answer')->where('ss_contract_id', $name->id)->get()->toArray();
                $activityLog = SsActivityLog::select('created_at as date', 'message as log')->where('ss_contract_id', $name->id)->where('shop_id', $this->shopID)->where('user_type', 'user')->get()->toArray();
                $billing = SsBillingAttempt::select('status', 'errorMessage')->where('ss_contract_id', $name->id)->orderBy('created_at', 'desc')->first();
                $cancellation = SsCancellation::select('reason','created_at')->where('ss_contract_id', $name->id)->where('shop_id', $this->shopID)->orderBy('created_at', 'desc')->first();

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
