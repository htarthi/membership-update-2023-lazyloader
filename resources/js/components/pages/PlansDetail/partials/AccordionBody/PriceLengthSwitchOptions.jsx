import { Checkbox, Select, TextField, Text } from "@shopify/polaris";
import React, { useState } from "react";
import CommanLabel from "../../../../GlobalPartials/CommanInputLabel/CommanLabel";
import { useSelector } from "react-redux";

export default function PriceLengthSwitchOptions({ item, index, handleLengthChange }) {

    const help$ = useSelector((state) => state?.plansDetail?.help);
    const errors$ = useSelector((state) => state.plansDetail?.errors);
    const selectedIndex$ = useSelector((state) => state.plansDetail?.selectedIndex);

    const shop$ = useSelector((state) => state?.plansDetail?.data?.shop);
    const plans$ = useSelector((state) => state?.plans?.data?.shop);

    const [isstorecredit , setISstorecredit] = useState(shop$.storecredit);

    if(!isstorecredit){
        if(plans$?.storecredit){
            setISstorecredit(true);
        }
    }

    // One-Time Setup Fee input options
    const oneTimeSetupPeriod = [
        { label: "Day(s)", value: "days" },
        { label: "Order(s)", value: "orders" },
    ];

    const creaditStore = [
        { label: "Fixed amount Per order", value: "all_orders" },
        { label: "Fixed amount on first order only", value: "first_order" }

    ];
    return (
        <>

            {/* One-Time Setup Fee */}
            <div className="one_time_setup_block switch_options_wrap">

                {/* checkbox */}
                <div className="switch_checkbox_field">
                    <Checkbox
                        label={<CommanLabel label={"Free/Discounted Trial or One-Time Setup Fee"} content={help$?.free_trial} />}
                        checked={item?.trial_available ? item?.trial_available : false}
                        onChange={(value) =>
                            handleLengthChange(item?.id, index, value, "trial_available")
                        }
                    />
                    {/* switch */}
                    <div className={`${item?.trial_available && "checked_checkbox"} switch`} >
                        <span className="slider round"></span>
                    </div>
                </div>

                {/* input - price, length & period */}
                {item?.trial_available && (
                    <div className="input_three_fields_wrap options_sub_block">
                        <div className="input_fields_wrap">
                            <TextField
                                label={<CommanLabel label={"Price"} content={help$?.plan_price} />}
                                type="number"
                                value={ item?.pricing2_adjustment_value == 0 ? 0 : item?.pricing2_adjustment_value   }
                                onChange={(value) =>
                                    handleLengthChange(
                                        item?.id,
                                        index,
                                        value < 0 ? '0' : parseInt(value),
                                        "pricing2_adjustment_value"
                                    )
                                }
                                autoComplete="off"
                                prefix={shop$.currency}
                                error={!item?.pricing2_adjustment_value ? errors$?.length > 0 ? errors$[0][`data.tiers.${selectedIndex$}.membershipLength.${index}.pricing2_adjustment_value`] : '' : ''}
                            />
                        </div>
                        <div className="input_fields_wrap">
                            <TextField
                                label={<CommanLabel label={"Length"} content={help$?.plan_length} />}
                                type="number"
                                value={item?.trial_type == 'orders' ? item?.pricing2_after_cycle || '' : item?.trial_days || ''}
                                onChange={(value) =>
                                    handleLengthChange(
                                        item?.id,
                                        index,
                                        value < 1 ? '' : value,
                                        'trial_days'
                                    )
                                }
                                autoComplete="off"
                                error={item?.trial_type == 'orders' ?
                                    item?.pricing2_after_cycle <= 0 ? errors$?.length > 0 ? errors$[0][`data.tiers.${selectedIndex$}.membershipLength.${index}.pricing2_after_cycle`] : '' : ''
                                    :
                                    item?.trial_days <= 0 ? errors$?.length > 0 ? errors$[0][`data.tiers.${selectedIndex$}.membershipLength.${index}.trial_days`] : '' : ''}
                            />
                        </div>
                        <div className="input_fields_wrap">
                            <Select
                                label={<CommanLabel label={"Period"} content={help$?.plan_period} />}
                                options={oneTimeSetupPeriod}
                                value={item?.trial_type == "orders" ? "orders" : "days"}
                                onChange={(value) =>
                                    handleLengthChange(
                                        item?.id,
                                        index,
                                        value,
                                        "trial_type"
                                    )
                                }
                            />
                        </div>
                    </div>
                )}

            </div>

            {/* One-Time Payments Only */}
            <div className="one_time_payment_only switch_options_wrap">

                {/* checkbox */}
                <div className="switch_checkbox_field">
                    <Checkbox
                        label={<CommanLabel label={"One-Time Payment"} content={help$?.one_time_payment} />}
                        checked={item?.is_onetime_payment ? item?.is_onetime_payment : false}
                        onChange={(value) =>
                            handleLengthChange(item?.id, index, value, "is_onetime_payment")
                        }
                    />
                    {/* switch */}
                    <div className={`${item?.is_onetime_payment && "checked_checkbox"} switch`} >
                        <span className="slider round"></span>
                    </div>
                </div>

            </div>

            {/* Require Minimum Orders */}
            <div className="require_minimum_orders switch_options_wrap">

                {/* checkbox */}
                <div className="switch_checkbox_field">
                    <Checkbox
                        label={<CommanLabel label={"Require Minimum Orders"} content={help$?.min_max} />}
                        checked={item?.is_set_min ? item?.is_set_min : false}
                        onChange={(value) =>
                            handleLengthChange(item?.id, index, value, "is_set_min")
                        }
                    />
                    {/* switch */}
                    <div className={`${item?.is_set_min && "checked_checkbox"} switch`} >
                        <span className="slider round"></span>
                    </div>
                </div>

                {/* input - Minimum Orders */}
                {item?.is_set_min && (
                    <div className="input_fields_wrap options_sub_block">
                        <TextField
                            label={<CommanLabel label={"Minimum Orders"} content={help$?.min_max} />}
                            type="number"
                            value={item?.billing_min_cycles || ''}
                            onChange={(value) =>
                                handleLengthChange(
                                    item?.id,
                                    index,
                                    value < 0 ? '0' : value,
                                    "billing_min_cycles"
                                )
                            }
                            autoComplete="off"
                            error={!item?.billing_min_cycles ? errors$?.length > 0 ? errors$[0][`data.tiers.${selectedIndex$}.membershipLength.${index}.billing_min_cycles`] : '' : ''}
                        />
                    </div>
                )}

            </div>

            {/* Expire Membership After */}
            <div className="require_minimum_orders switch_options_wrap">

                {/* checkbox */}
                <div className="switch_checkbox_field">
                    <Checkbox
                        label={<CommanLabel label={"Expire Membership After"} content={help$?.expired_membership} />}
                        checked={item?.is_set_max ? item?.is_set_max : false}
                        onChange={(value) =>
                            handleLengthChange(item?.id, index, value, "is_set_max")
                        }
                    />
                    {/* switch */}
                    <div className={`${item?.is_set_max && "checked_checkbox"} switch`} >
                        <span className="slider round"></span>
                    </div>
                </div>

                {/* input - Expire After X orders */}
                {item?.is_set_max && (
                    <div className="input_fields_wrap options_sub_block">
                        <TextField
                            label={<CommanLabel label={"Expire After X orders"} content={""} />}
                            type="number"
                            value={item?.billing_max_cycles || ''}
                            onChange={(value) =>
                                handleLengthChange(
                                    item?.id,
                                    index,
                                    value < 0 ? '0' : value,
                                    "billing_max_cycles"
                                )
                            }
                            autoComplete="off"
                            error={!item?.billing_max_cycles ? errors$?.length > 0 ? errors$[0][`data.tiers.${selectedIndex$}.membershipLength.${index}.billing_max_cycles`] : '' : ''}
                        />
                    </div>
                )}

            </div>

            {/* Give Store Credit */}
            {isstorecredit && (
                <div className="store_credit switch_options_wrap">

                    {/* checkbox */}
                    <div className="switch_checkbox_field">
                        <Checkbox
                            label={<CommanLabel label={"Give Store Credit"} content={help$?.store_credit} />}
                            checked={item?.store_credit ? item?.store_credit : false}
                            onChange={(value) =>
                                handleLengthChange(item?.id, index, value, "store_credit")
                            }
                        />
                        {/* switch */}
                        <div className={`${item?.store_credit && "checked_checkbox"} switch`} >
                            <span className="slider round"></span>
                        </div>
                    </div>

                    {/* input - price, length & period */}
                    {item?.store_credit && (
                        <div className="input_two_fields_wrap options_sub_block">
                            <div className="input_fields_wrap">
                                <Select
                                    label={<CommanLabel label={"Credit Type"} content={help$?.credit_type} />}
                                    options={creaditStore}
                                    value={item?.store_credit_frequency == "all_orders" ? "all_orders" : "first_order"}
                                    onChange={(value) =>
                                        handleLengthChange(
                                            item?.id,
                                            index,
                                            value,
                                            "store_credit_frequency"
                                        )
                                    }
                                />
                            </div>
                            <div className="input_fields_wrap">
                                <TextField
                                    label={<CommanLabel label={"Credit Amount"} content={`${help$?.store_amount}(${shop$?.currency})`} />}
                                    type="number"
                                    value={item?.store_credit_amount || ' '}
                                    onChange={(value) =>
                                        handleLengthChange(
                                            item?.id,
                                            index,
                                            value < 1 ? '' : (value),
                                            "store_credit_amount"
                                        )
                                    }
                                    autoComplete="off"
                                    prefix={shop$?.currency}
                                    error={!item?.store_credit_amount ? errors$?.length > 0 ? errors$[0][`data.tiers.${selectedIndex$}.membershipLength.${index}.store_credit_amount`] : '' : ''}
                                />
                                {
                                    item?.store_credit_amount != "" ?
                                    item?.store_credit_frequency ==  "first_order" ? <Text> Give members <Text as="span" fontWeight="semibold">
                                    {shop$?.currency} {item?.store_credit_amount}</Text>  for their first order only</Text> : <Text> Give members <Text as="span" fontWeight="semibold">{shop$?.currency} {item?.store_credit_amount}</Text>  for each successful order.</Text>
                                    : ''
                                }

                            </div>
                        </div>
                    )}

                </div>
            )}

        </>
    );
}
