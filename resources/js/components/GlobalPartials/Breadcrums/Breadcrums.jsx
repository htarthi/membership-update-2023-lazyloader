import React, { useState, useEffect } from "react";
import { NavLink, useNavigate ,useLocation  } from "react-router-dom";
import { ArrowLeftIcon } from "@shopify/polaris-icons";
import { Badge, Icon, Pagination, Text } from "@shopify/polaris";
import { useSelector } from "react-redux";

export default function Breadcrums({
    is_plandetail_show,
    to,
    title,
    member_number,
    email,
    phone_number,
    shopify_contract_id,
    status,
    is_migrated,
    is_onetime_payment,
    showEmail,
    prevMehod,
    nextMehod,
    back,
    hasNext,
    hasPrev,
}) {
    const contract = useSelector(
        (state) => state.memberDetails?.data?.contract
    );
    const navigate = useNavigate();
    const [is_in_trial, Setis_in_trial] = useState(false);
    const [navigateURL,setNavigateURL]  = useState(false);
    const location = useLocation();

    useEffect(() => {
        if (contract) {
            if (contract?.trial_available == 1) {
                if (contract?.all_plans.length > 0) {
                    if(contract?.all_plans[0]["pricing2_after_cycle"] !== null){
                        if(contract?.pricing2_after_cycle){
                            if (contract?.order_count < contract.pricing2_after_cycle) {
                                Setis_in_trial(true);
                            }
                        }
                    }
                    else if (contract?.all_plans[0]["trial_days"] !== null) {
                        // Corrected syntax here
                        const createdAtDate = new Date(contract.created_at);
                        const currentDate = new Date();
                        const differenceInMilliseconds =
                            currentDate - createdAtDate;
                        const differenceInDays =
                            differenceInMilliseconds / (1000 * 3600 * 24);
                        if (differenceInDays <= contract.all_plans.trial_days) {
                            Setis_in_trial(true);
                        }
                    }
                }
            }
        }
        if(location.search == "?=dashboard" || location.search == "?=reports" ){
            setNavigateURL(true);
        }
    }, []);


    const newStore$ = useSelector((state) => state.plans?.newStore);

    return (
        <div className="members_breadcrums">
            {/* back navigate */}
            <div className="members_navigate_wrap">
                {
                    navigateURL ? <NavLink className="back_arrow_wrap" onClick={() => navigate(-1)}><Icon source={ArrowLeftIcon} tone="base"/></NavLink> : <NavLink className="back_arrow_wrap" to={to} ><Icon source={ArrowLeftIcon} tone="base"  /></NavLink>
                }


                <div className="membership_detail_heading">
                    {is_plandetail_show && (
                        <div className="title_status_wrap">
                            <Text variant="headingLg" as="h5" fontWeight="regular">
                                {title} {member_number && `(#${member_number})`}
                            </Text>
                            <div className="status_wrap">
                                {is_onetime_payment == 1 ||
                                (is_migrated == 1 &&
                                    shopify_contract_id == null) ? (
                                    <Badge
                                        tone={
                                            shopify_contract_id == null &&
                                            is_migrated == 0
                                                ? "info"
                                                : is_migrated == 1
                                                ? "warning"
                                                : is_onetime_payment == 1
                                                ? "warning"
                                                : is_in_trial == true
                                                ? "incomplete"
                                                : ""
                                        }
                                        progress={
                                            status === "Created Manually"
                                                ? ""
                                                : status === "active"
                                                ? "complete"
                                                : status === "expiring"
                                                ? "partiallyComplete"
                                                : status === "pending"
                                                ? "partiallyComplete"
                                                : status === "cancelled"
                                                ? "incomplete"
                                                : ""
                                        }
                                    >
                                        {is_onetime_payment === 1 &&
                                        shopify_contract_id !== null
                                            ? "Lifetime Access"
                                            : is_onetime_payment === 1 &&
                                              shopify_contract_id === null
                                            ? "Created Manually"
                                            : is_migrated === 1 &&
                                              shopify_contract_id === null
                                            ? "Migrated"
                                            : ""}
                                    </Badge>
                                ) : (
                                    ""
                                )}
                            </div>
                        </div>
                    )}
                    {showEmail && (
                        <Text fontWeight="regular" variant="bodyLg" as="h6">
                            {email && `${email} •`}{" "}
                            {phone_number && `${phone_number} •`} Contract ID:{" "}
                            {shopify_contract_id || "No billing contract"}
                        </Text>
                    )}
                </div>
            </div>

            {/* pagination */}
            <div className="mordeactions_pagination_wrap">
                {/* pagination */}
                {!newStore$ && (
                    <div className="pagination_wrap">
                        <Pagination
                            hasPrevious={hasPrev}
                            onPrevious={() => {
                                prevMehod();
                            }}
                            hasNext={hasNext}
                            onNext={() => {
                                nextMehod();
                            }}
                        />
                    </div>
                )}
            </div>
        </div>
    );
}
