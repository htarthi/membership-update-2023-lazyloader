<?php

namespace App\Exports;

use App\Models\SsContract;
use App\Models\SsAnswer;
use App\Models\SsActivityLog;
use App\Models\SsBillingAttempt;
use App\Models\SsCancellation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubscribersExport implements FromCollection, WithHeadings
{
    private $shopID;
    private $type;
    private $s;
    private $p;
    private $lp;
    public function __construct($type, $s, $p,$lp, $id)
    {
        \Log::info('Export maatwebsite');
        $this->shopID = $id;
        $this->type = $type;
        $this->s = $s;
        $this->lp = $lp;
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

    /**
     * @return mixed
     */
    public function getData()
    {
        $s = $this->s;
        $p = $this->p;
        $f = $this->type;
        $lp = $this->lp;
        $plansArray = explode(',', $p);

        $plans_status_Array = explode(',', $p);


        $subscriber = SsContract::select('ss_contracts.id', 'ss_contracts.shop_id', 'ss_contracts.ss_customer_id', 'ss_contracts.shopify_customer_id', 'ss_contracts.shopify_contract_id', 'ss_contracts.status', 'next_order_date', 'ss_contracts.order_count', 'ss_customers.first_name', 'ss_customers.last_name', 'ss_customers.email', 'ss_customers.phone', 'ss_customers.date_first_order', 'ss_contracts.created_at', 'ss_plan_groups.name As plan_name', 'ss_contracts.member_number', 'ss_contracts.status_display', 'ss_contracts.cc_id')
            ->join('ss_plan_groups', 'ss_contracts.ss_plan_groups_id', '=', 'ss_plan_groups.id')
            ->join('ss_customers', 'ss_contracts.ss_customer_id', '=', 'ss_customers.id')
            ->where(function ($query) use ($f) {
                if ($f != 'all') {
                    $query->where('ss_contracts.status', 'LIKE', '%' . $f . '%');
                }
            })
            ->where(function ($query) use ($p , $plansArray) {
                if ($p != 'All Plans') {
                    // $query->where('ss_plan_groups.name', 'LIKE', '%' . $p . '%');

                    $query->whereIn('ss_plan_groups.name', $plansArray);

                }
            })->where(function ($query) use ($lp) {
                if ($lp != '') {
                    $query->where('ss_contracts.lastPaymentStatus', $lp);
                }
            })
            ->where(function ($query) use ($s) {
                $query->where('order_count', 'LIKE', '%' . $s . '%')
                    ->orWhere('ss_contracts.id', 'LIKE', '%' . $s . '%')
                    ->orWhere('ss_contracts.shopify_contract_id', 'LIKE', '%' . $s . '%')
                    ->orWhere('first_name', 'LIKE', '%' . $s . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $s . '%')
                    ->orWhere('email', 'LIKE', '%' . $s . '%')
                    ->orWhere('ss_contracts.member_number', $s)
                    ->orWhere('phone', 'LIKE', '%' . $s . '%');
            })->where('ss_contracts.shop_id', $this->shopID)
            ->where('ss_customers.shop_id', $this->shopID)
            ->orderBy('ss_contracts.created_at', 'desc')->get();


        $subscriber = (count($subscriber) > 0) ? $subscriber : collect([]);
        return $subscriber;
    }

    /**
     * @param $entities
     * @return mixed
     */
    public function createFile($entities)
    {
        \Log::info('----------create File--------------');
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
