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
use Illuminate\Support\Facades\DB;

class RecentCancellationReportExport implements FromCollection, WithHeadings
{
    private $shopID;
    private $email;
    private $s;
    private $selectedSegmentIndex;
    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct($id, $selectedSegmentIndex, $s)
    {
        $this->shopID = $id;
        $this->selectedSegmentIndex = $selectedSegmentIndex;
        $this->s = $s;
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


            $recent_cancelation = SsCancellation::join(DB::raw('(SELECT ss_contract_id, MAX(created_at) AS max_created_at FROM ss_contract_line_items GROUP BY ss_contract_id) AS latest_joined'), function ($join) {
                $join->on('ss_cancellations.ss_contract_id', '=', 'latest_joined.ss_contract_id');
            })
                ->join('ss_contracts', 'ss_cancellations.ss_contract_id', '=', 'ss_contracts.id')
                ->join('ss_contract_line_items', function ($join) {
                    $join->on('ss_contracts.id', '=', 'ss_contract_line_items.ss_contract_id')
                        ->on('ss_contract_line_items.created_at', '=', 'latest_joined.max_created_at');
                })
                ->join('ss_plans', 'ss_contracts.ss_plan_id', '=', 'ss_plans.id')
                ->join('ss_customers', 'ss_contracts.ss_customer_id', '=', 'ss_customers.id')
                ->where(['ss_contracts.shop_id' => $this->shopID, 'ss_contracts.status' => 'cancelled'])

                ->select(['ss_cancellations.id', 'ss_cancellations.ss_contract_id', 'ss_cancellations.created_at', 'ss_cancellations.cancelled_by', 'ss_customers.first_name', 'ss_customers.last_name', 'ss_plans.name', 'ss_contract_line_items.discount_amount', 'ss_contracts.order_count',
                'ss_plans.name as plan_name','ss_contracts.next_order_date','ss_contracts.created_at','ss_contracts.shopify_contract_id','ss_contracts.status', 'ss_contracts.status_display', 'ss_customers.email','ss_customers.phone', 'ss_customers.shopify_customer_id','ss_contracts.cc_id','ss_contracts.order_count'
                ])
                ->orderBy('ss_cancellations.created_at', 'desc');



            if (isset($s) && !empty($s)) {
                $recent_cancelation = $recent_cancelation->where(function ($query) use ($s) {
                    $query->Where('ss_customers.first_name', 'LIKE', '%' . $s . '%')
                        ->orWhere('ss_customers.last_name', 'LIKE', '%' . $s . '%');
                });
            }

            $results = $recent_cancelation->get();

            return $results;

        } catch (\Exception $e) {
            logger('Error retrieving upcoming renewals data:'. $e);
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
