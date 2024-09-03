import { Button, ButtonGroup, Icon, Text, IndexTable, LegacyCard } from '@shopify/polaris'
import React, { useState, useCallback, useEffect } from 'react'
import DragDropCell from './DragDropCell';
import { DeleteIcon, PersonSegmentIcon, InventoryUpdatedIcon } from '@shopify/polaris-icons';
import UpdateMassPrice from './UpdateMassPrice';
import { useSelector } from 'react-redux';

export default function PlansTierList({ planGroup, handleAddMembersChange, handleDeletePlan, lengths, plang_key }) {

    const array = [];
    const [all_lengths, setall_lengths] = useState(array);
    const shop = useSelector((state) => state.plans?.data?.shop?.currency);
    const is_membership_expired = useSelector((state) => state.plans?.data?.shop?.is_membership_expired);


    const [massPriceData, setMassPriceData] = useState({
        showMassPrice: false,
        planName: ''
    });

    const resourceName = {
        singular: "row",
        plural: "rows",
    };

    const handleMassPriceModal = useCallback((planName) => {
        setMassPriceData({
            ...massPriceData,
            showMassPrice: true,
            planName: planName
        })
    }, [massPriceData])

    const rowMarkup = useCallback((data) => {
        return data.length > 0 && data?.map(
            ({ id, shopify_plan_id, trial_available, trial_days, pricing2_adjustment_value, delivery_interval_count, delivery_interval, pricing2_after_cycle, has_many_contracts, name, pricing_adjustment_value, is_onetime_payment, is_set_min, is_set_max, billing_min_cycles, billing_max_cycles }, index) => (

                // <Draggable key={index} draggableId={id.toString()} index={index}>
                //     {(provided) => (
                <IndexTable.Row id={id} key={id} position={index} className="Polaris-IndexTable__TableRow membership_table_tr">
                    <IndexTable.Cell>
                        {/* <div
                                        className="drag_handle_minor_wrap"
                                        ref={provided.innerRef}
                                        {...provided.draggableProps}
                                        {...provided.dragHandleProps}
                                    > */}
                        {/* <Icon
                                source={DragHandleIcon}
                                tone="base"
                            /> */}
                        {/* </div> */}

                    </IndexTable.Cell>
                    <IndexTable.Cell >
                        <Text padding="0" variant='headingSm' as="span" fontWeight='medium' tone='base'>{name.charAt(0).toUpperCase() + name.slice(1)} </Text>
                        <Text variant="bodyLg" as="span" tone="base" fontWeight='regular'>({delivery_interval_count + " " + delivery_interval.charAt(0).toUpperCase() + delivery_interval.slice(1) + "s"})</Text>
                        <br />
                        <Text variant="bodyLg" as="span" fontWeight='regular' tone='base'>ID: {shopify_plan_id}</Text>


                    </IndexTable.Cell>
                    <IndexTable.Cell>

                        <Text padding="0" alignment='end' variant="bodyLg" as="span" fontWeight='regular' tone='base'>{shop + " " + pricing_adjustment_value}</Text>

                    </IndexTable.Cell>

                    <IndexTable.Cell>
                        <Text padding="0" alignment='center' variant="bodyLg" as="span" fontWeight='regular' tone='base'>{has_many_contracts.length}</Text>

                    </IndexTable.Cell>
                    <IndexTable.Cell>

                        {/* IS TRIAL AVALIABLE */}
                        <Text padding="0" variant="bodyLg" as="span" tone="subdued" fontWeight='regular'>{trial_available ? (trial_days !== null ? shop + " " + pricing2_adjustment_value + " " + "for" + " " + trial_days + " " + "day(s)" : shop + " " + pricing2_adjustment_value + " " + "for" + " " + pricing2_after_cycle + " " + "order(s)") : ''}</Text>
                        {trial_available ? <br /> : ""}


                        {/* IS ONETIME PAYMENT */}
                        <Text padding="0" variant="bodyLg" as="span" tone="subdued" fontWeight='regular'>{is_onetime_payment ? "One-time payment" : ""}</Text>
                        {is_onetime_payment ? <br /> : ""}


                        {/* IS SET MIN (BILLING MIN CYCLE) */}

                        {!is_onetime_payment &&
                            <>
                                <Text padding="0" variant='headingSm' as="span" fontWeight='Bold'>{is_set_min ? "Min:" : ""}</Text>
                                <Text padding="0" variant="bodyLg" as="span" tone="subdued" fontWeight='regular'>{is_set_min ? " " + billing_min_cycles + " " + "orders" : ""}</Text>
                                {is_set_min ? <br /> : ""}


                                {/* IS SET MAX (BILLING MAX CYCLE) */}
                                <Text padding="0" variant='headingSm' as="span" fontWeight='Bold'>{is_set_max ? "Expire After:" : ""}</Text>
                                <Text padding="0" variant="bodyLg" as="span" tone="subdued" fontWeight='regular'>{is_set_max ? + " " + billing_max_cycles + " " + "orders" : ""}</Text>
                                {is_set_max ? <br /> : ""}</>}

                        {!trial_available && !is_onetime_payment && !is_set_max && !is_set_min ? "-" : ''}

                    </IndexTable.Cell>

                </IndexTable.Row>
                //         )}
                //     </Draggable>
            )
        );
    }, [])


    const rowMarkup2 = useCallback((data) => {
        return (


            <IndexTable.Row className="Polaris-IndexTable__TableRow membership_table_tr">
                <IndexTable.Cell>


                </IndexTable.Cell>
                <IndexTable.Cell >
                    <Text padding="0" variant='headingSm' as="span" fontWeight='medium'>Created Manually </Text>
                    {/* <Text variant="bodyLg" as="span" tone="subdued" fontWeight='regular'>({delivery_interval_count + " " + delivery_interval.charAt(0).toUpperCase() + delivery_interval.slice(1) + "s"})</Text>
                        <br />
                        <Text variant="bodyLg" as="span" tone="subdued" fontWeight='regular'>ID: {shopify_plan_id}</Text> */}


                </IndexTable.Cell>
                <IndexTable.Cell>

                    <Text padding="0" alignment='end' variant="bodyLg" as="span" fontWeight='medium' >-</Text>

                </IndexTable.Cell>

                <IndexTable.Cell>
                    <Text padding="0" alignment='center' variant="bodyLg" as="span" fontWeight='medium'>{data}</Text>

                </IndexTable.Cell>
                <IndexTable.Cell>

                    -
                </IndexTable.Cell>

            </IndexTable.Row>
        )

        //         )}
        //     </Draggable>

    }, [])





    return <>
        {
            planGroup?.map((plan, index) => {


                return (
                    <div className='plans_tier_list_wrap' key={index}>
                        {/* plans group name & delete or add plans */}
                        <div className='plans_tier_sub_header flex-space-between'>
                            <Text variant="headingLg" as="h5" fontWeight='medium' tone='base'>{plan?.name || '-'}</Text>
                            <ButtonGroup segmented>
                                {/* <Button onClick={() => handleMassPriceModal(plan?.name)}><Icon source={InventoryUpdatedIcon} color="base" /></Button> */}
                                {!is_membership_expired && (
                                    <Button onClick={() => handleAddMembersChange(plan?.id, plan?.name, index, plang_key, plan.has_manual_membership_count)}>
                                        <Icon source={PersonSegmentIcon} color="base" />
                                    </Button>
                                 )}

                                <Button onClick={() => handleDeletePlan(plan?.id)}><Icon source={DeleteIcon} color="base" size="large"/></Button>
                            </ButtonGroup>
                        </div>

                        <div className='plans_tier_options ms-margin-top'>
                            {/* Discount */}
                            <div className='plans_tier_options_col'>
                                <div className='name_wrap'>
                                    <Text variant="bodyLg" as="span" fontWeight='medium'>Discount</Text><br />
                                    <Text variant="bodyLg" as="span" tone="subdued" fontWeight='regular'>  
                                        {
                                            plan?.discount_type == '2' ? "AUTOMATIC" : plan?.discount_type == '3' ? plan?.discount_code ? `Code: ${plan?.discount_code} ` : '-' : '-'
                                        }
                                    </Text>
                                </div>
                            </div>
                            {/* Questions */}
                            <div className='plans_tier_options_col'>
                                <div className='name_wrap'>
                                    <Text variant="bodyLg" as="span" fontWeight='medium'>Questions</Text><br />
                                    <Text variant="bodyLg" as="span" tone="subdued" fontWeight='regular'>{plan?.has_many_forms_count ? plan?.has_many_forms_count + " questions" : '-'} </Text>
                                </div>
                            </div>
                            {/* Rules */}
                            <div className='plans_tier_options_col'>
                                <div className='name_wrap'>
                                    <Text variant="bodyLg" as="span" fontWeight='medium'>Content Restrictions</Text><br />
                                    <Text variant="bodyLg" as="span" tone="subdued" fontWeight='regular'>{plan?.has_many_rules_count ? plan?.has_many_rules_count : "-"}</Text>
                                </div>
                            </div>
                            {/* Trial/Setup */}
                            {/* {plans?.shop?.currency} */}
                            {/* <div className='plans_tier_options_col'>
                                <div className='name_wrap'>
                                    <Text variant="headingMd" as="h6" fontWeight='semiBold'>Trial/Setup</Text>
                                    <Text variant="bodyLg" as="h6" color="subdued" fontWeight='regular'>
                                        {
                                            plan?.has_many_plan[0]?.is_advance_option ?
                                                plan?.has_many_plan[0]?.trial_type === 'orders' ?
                                                    `${plan?.has_many_plan[0]?.pricing2_after_cycle} time fee: ${plan?.has_many_plan[0]?.pricing2_adjustment_value}`
                                                    :
                                                    `${plan?.has_many_plan[0]?.trial_days} days fee: ${plan?.has_many_plan[0]?.pricing2_adjustment_value}`
                                                :
                                                '-'
                                        }
                                    </Text>
                                </div>
                            </div> */}
                            {/* customer tag */}
                            <div className='plans_tier_options_col'>
                                <div className='name_wrap'>
                                    <Text variant="bodyLg" as="span" fontWeight='medium'>Customer Tag</Text><br />
                                    <Text variant="bodyLg" as="span" tone="subdued" fontWeight='regular' >{plan?.tag_customer || '-'}</Text>
                                </div>
                            </div>
                            {/* order tag */}
                            <div className='plans_tier_options_col'>
                                <div className='name_wrap'>
                                    <Text variant="bodyLg" as="span" fontWeight='medium'>Order Tag</Text><br />
                                    <Text variant="bodyLg" as="span" tone="subdued" fontWeight='regular'>{plan?.tag_order || '-'}</Text>
                                </div>
                            </div>
                        </div>
                        <div className='plans_lengths_list_wrap' >
                            <LegacyCard >
                                <IndexTable
                                    resourceName={resourceName}
                                    itemCount={plan.has_many_plan?.length > 0 ? plan.has_many_plan?.length : 0}
                                    selectable={false}
                                    headings={[
                                        { title: "" },
                                        { title: "Membership Length" },
                                        { title: "Price", alignment: "end" },
                                        { title: "Members", alignment: "center" },
                                        { title: "Additional Settings" },

                                    ]}
                                >
                                    {rowMarkup(plan.has_many_plan)}
                                    {/* {rowMarkup2(plan.has_manual_membership_count)} */}
                                    {plan.has_manual_membership_count > 0 && rowMarkup2(plan.has_manual_membership_count)}
                                </IndexTable>
                            </LegacyCard>
                        </div>


                    </div>
                );
            })
        }

        {/* Update Mass Price Modal */}
        <UpdateMassPrice massPriceData={massPriceData} setMassPriceData={setMassPriceData} />
    </>;
}
