<?php

namespace App\Exports;

use App\Models\SsContract;
use App\Models\SsForm;
use App\Models\SsPlan;
use App\Models\SsPlanGroupVariant;
use App\Models\SsPosDiscounts;
use App\Models\SsRule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class PlansExport implements  FromCollection, WithEvents,WithHeadings
{
    private $shopID;

    public function __construct($id)
    {
        $this->shopID = $id;

    }

    /**
     *
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $entities = $this->getData();
        $entity = $this->createFile($entities);
        return collect($entity);
    }

    public  function getData(){
        $plans  = SsPlanGroupVariant::select('ss_plan_group_variants.product_title','ss_plan_groups.name','ss_plan_groups.tag_customer','ss_plan_groups.tag_order','ss_plan_groups.id','ss_plan_groups.discount_code','ss_plan_groups.discount_code_members','ss_plan_groups.is_display_on_cart_page','ss_plan_groups.is_display_on_member_login')->join('ss_plan_groups','ss_plan_group_variants.ss_plan_group_id', '=','ss_plan_groups.id')->where('ss_plan_group_variants.shop_id',$this->shopID )->get();
        return $plans;

    }
    public  function createfile($entities){
        if($entities){
            $entity = $entities->map(function($name){
                $lengths = SsPlan::where('ss_plan_group_id',$name->id)->get()->toArray();
                $que = SsForm::where('ss_plan_group_id',$name->id)->get()->toArray();
                $restrictions  = SsRule::where('ss_plan_group_id',$name->id)->get()->toArray();
                $pos = SsPosDiscounts::where('ss_plan_groups_id',$name->id)->get()->toArray();
                $data = [
                    'product'=>$name->product_title,
                    'name'=>$name->name,
                    'customer_tag'=>$name->tag_customer,
                    'order_tag'=>$name->tag_order,
                    'active_members' => SsContract::where("ss_plan_groups_id",$name->id)->count(),
                    'lenghts'=>json_encode($lengths,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
                    'Questions' => (count($que) > 0) ? json_encode($que,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : '',
                    'Restrictions' => (count($restrictions) > 0) ? json_encode($restrictions,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : '',
                    'online_discount_code' => $name->discount_code,
                    'online_discount_description' => $name->discount_code_members,
                    'online_discount_show_cart' => $name->is_display_on_cart_page,
                    'online_discount_show_login' => $name->is_display_on_member_login,
                    'pos_discounts' => (count($pos) > 0) ? json_encode($pos,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : '',
                ];
                return $data;

            });
        }
        return $entity;
    }
    public function headings(): array
    {
        return [
        	'Product',
            'Name',
            'Customer Tag',
            'Order Tag',
            'Active Members',
            'Lengths',
            'Questions',
            'Restrictions',
            'Online Discount Code',
            'Online Discount Desription',
            'Online Discount Cart',
            'Online Discount Login',
            'Pos Discounts',

        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Apply background color to a range of cells
                $event->sheet->getDelegate()->getStyle('A1:B2')->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'FF0000', // Specify your desired color code here
                        ],
                    ],
                ]);

                // Apply background color to a single cell
                $event->sheet->getDelegate()->getStyle('C3')->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => '00FF00', // Specify your desired color code here
                        ],
                    ],
                ]);
            },
        ];
    }

}
