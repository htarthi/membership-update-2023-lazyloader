import { Badge, Button, Icon, Text, Tooltip } from "@shopify/polaris";
import React, { useState } from "react";
import rightIcon from "../../../../../images/right-icon.svg";
import { QuestionCircleIcon } from "@shopify/polaris-icons";
import { useSelector, useDispatch } from "react-redux";
import { changePlans } from "../../../../data/features/plans/plansSlice";
import { changeBillingPlan } from "../../../../data/features/settings/settingAction";

export default function AccountBillings() {
    const plans$ = useSelector((state) => state?.settings?.data?.data?.plans);
    const plan$ = useSelector((state) => state?.settings?.data?.data?.plan);

    const user$ = useSelector((state) => state?.settings?.data?.data?.user);

    const dispatch = useDispatch();

    const changePlan = function (val) {
        const data = {
            plan_id: val,
            user_name: user$.name,
        };
        dispatch(changeBillingPlan(data));
    };

    return (
        <div className="account_billings_block">
            {/* <Text variant="bodyLg" as="h6" fontWeight="regular">
                Your current plan and per-member fee are listed below. The
                member fee is applied to each billing cycle, and applies to any
                active memberships.
            </Text> */}
            {plans$?.length > 0 && (
                <div className="plans_row_block ">
                    {/* Starter Plan */}
                    <div className="plans_col">
                        {/* plan heading */}
                        <div className="plan_heading_block">
                            <Text variant="headingLg" as="h5" fontWeight="bold">
                                Starter Plan
                            </Text>
                            <Badge>${plans$[0]?.price}/month</Badge>
                        </div>

                        {/* plan details */}
                        <div className="plan_details_block">
                            <div className="per_member">
                                <Text
                                    variant="bodyLg"
                                    as="h6"
                                    fontWeight="regular"
                                >
                                    + $
                                    {(plans$[0]?.transaction_fee * 100).toFixed(
                                        2,
                                    )}{" "}
                                    per member
                                </Text>
                            </div>

                            <div className="start_plan_list ms-margin-top">
                                <ul>
                                    <li>
                                        <div className="right_icon">
                                            <img src={rightIcon} alt="icon" />
                                        </div>
                                        Works with Shopify payments,
                                        Authorize.net, PayPal Express
                                    </li>
                                    <li>
                                        <div className="right_icon">
                                            <img src={rightIcon} alt="icon" />
                                        </div>
                                        Sell Memberships
                                    </li>
                                    <li>
                                        <div className="right_icon">
                                            <img src={rightIcon} alt="icon" />
                                        </div>
                                        Add Customer + Order Tags
                                    </li>
                                    <li>
                                        <div className="right_icon">
                                            <img src={rightIcon} alt="icon" />
                                        </div>
                                        Show / Hide Storefront Content
                                    </li>
                                    <li>
                                        <div className="right_icon">
                                            <img src={rightIcon} alt="icon" />
                                        </div>
                                        Dunning Management
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {/* footer */}
                        <div className="plans_footer_block">
                            <Button
                                disabled={
                                    plan$?.freePlans
                                        ? false
                                        : plan$.active_plan_id === 1
                                          ? true
                                          : false
                                }
                                onClick={() => changePlan(1)}
                            >
                                {" "}
                                {plan$?.freePlans
                                    ? "Upgrade"
                                    : plan$.active_plan_id === 1
                                      ? "Current Plan"
                                      : plan$.active_plan_id > 1 && "Downgrade"}
                            </Button>
                        </div>
                    </div>
                    {/* Growth Plan */}
                    <div className="plans_col">
                        {/* plan heading */}
                        <div className="plan_heading_block">
                            <Text variant="headingLg" as="h5" fontWeight="bold">
                                Growth Plan
                            </Text>
                            <Badge tone="success">
                                ${plans$[1]?.price}/month
                            </Badge>
                        </div>

                        {/* plan details */}
                        <div className="plan_details_block">
                            <div className="per_member">
                                <Text
                                    variant="bodyLg"
                                    as="h6"
                                    fontWeight="regular"
                                >
                                    + $
                                    {(plans$[1]?.transaction_fee * 100).toFixed(
                                        2,
                                    )}{" "}
                                    per member
                                </Text>
                            </div>

                            <div className="start_plan_list ms-margin-top">
                                <ul>
                                    <li>
                                        <div className="right_icon">
                                            <img src={rightIcon} alt="icon" />
                                        </div>
                                        Works with Shopify payments,
                                        Authorize.net, PayPal Express
                                    </li>
                                    <li>
                                        <div className="right_icon">
                                            <img src={rightIcon} alt="icon" />
                                        </div>
                                        Sell Memberships
                                    </li>
                                    <li>
                                        <div className="right_icon">
                                            <img src={rightIcon} alt="icon" />
                                        </div>
                                        Add Customer + Order Tags
                                    </li>
                                    <li>
                                        <div className="right_icon">
                                            <img src={rightIcon} alt="icon" />
                                        </div>
                                        Show / Hide Storefront Content
                                    </li>
                                    <li>
                                        <div className="right_icon">
                                            <img src={rightIcon} alt="icon" />
                                        </div>
                                        Dunning Management
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {/* stores */}
                        <div className="stores_block">
                            <div className="best_for_stores">
                                <Text
                                    variant="bodyLg"
                                    as="h6"
                                    fontWeight="regular"
                                >
                                    Best for stores with more than 200 members.
                                </Text>
                            </div>
                        </div>

                        {/* footer */}
                        <div className="plans_footer_block">
                            <Button
                                disabled={
                                    plan$?.freePlans
                                        ? false
                                        : plan$.active_plan_id === 2
                                          ? true
                                          : false
                                }
                                onClick={() => changePlan(2)}
                            >
                                {" "}
                                {plan$?.freePlans
                                    ? "Upgrade"
                                    : plan$.active_plan_id === 2
                                      ? "Current Plan"
                                      : plan$.active_plan_id < 2
                                        ? "Upgrade"
                                        : "Downgrade"}
                            </Button>
                        </div>
                    </div>
                    {/* Enterprise Plan */}
                    <div className="plans_col">
                        {/* plan heading */}
                        <div className="plan_heading_block">
                            <Text variant="headingLg" as="h5" fontWeight="bold">
                                Enterprise Plan
                            </Text>
                            <Badge>${plans$[2]?.price}/month</Badge>
                            {/* <div className='question_block'>
                                <Tooltip content="This order has shipping labels.">
                                    <Icon source={QuestionCircleIcon} color="base" />
                                </Tooltip>
                            </div> */}
                        </div>

                        {/* plan details */}
                        <div className="plan_details_block">
                            <div className="per_member">
                                <Text
                                    variant="bodyLg"
                                    as="h6"
                                    fontWeight="regular"
                                >
                                    + $
                                    {(plans$[2]?.transaction_fee * 100).toFixed(
                                        2,
                                    )}{" "}
                                    per member
                                </Text>
                            </div>

                            <div className="start_plan_list ms-margin-top">
                                <ul>
                                    <li>
                                        <div className="right_icon">
                                            <img src={rightIcon} alt="icon" />
                                        </div>
                                        Works with Shopify payments,
                                        Authorize.net, PayPal Express
                                    </li>
                                    <li>
                                        <div className="right_icon">
                                            <img src={rightIcon} alt="icon" />
                                        </div>
                                        Sell Memberships
                                    </li>
                                    <li>
                                        <div className="right_icon">
                                            <img src={rightIcon} alt="icon" />
                                        </div>
                                        Add Customer + Order Tags
                                    </li>
                                    <li>
                                        <div className="right_icon">
                                            <img src={rightIcon} alt="icon" />
                                        </div>
                                        Show / Hide Storefront Content
                                    </li>
                                    <li>
                                        <div className="right_icon">
                                            <img src={rightIcon} alt="icon" />
                                        </div>
                                        Dunning Management
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {/* stores */}
                        <div className="stores_block">
                            <div className="best_for_stores">
                                <Text
                                    variant="bodyLg"
                                    as="h6"
                                    fontWeight="regular"
                                >
                                    Best for stores with more than 2,500
                                    members.
                                </Text>
                            </div>
                        </div>

                        {/* footer */}
                        <div className="plans_footer_block">
                            <Button
                                disabled={
                                    plan$?.freePlans
                                        ? false
                                        : plan$.active_plan_id === 3
                                          ? true
                                          : false
                                }
                                onClick={() => changePlan(3)}
                            >
                                {" "}
                                {plan$?.freePlans
                                    ? "Upgrade"
                                    : plan$.active_plan_id === 3
                                      ? "Current Plan"
                                      : plan$.active_plan_id < 3 && "Upgrade"}
                            </Button>{" "}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
