import React, { useCallback, useEffect } from "react";
import { useTranslation } from "react-i18next";
import { useSelector } from "react-redux";
import MemberOrderDetail from "./partials/MemberOrderDetail";
import MemberShipDetailComp from "./partials/MemberShipDetailComp";
import ProductOrderTable from "./partials/ProductOrderTable";
import QuestionAnswers from "./partials/QuestionAnswers";
import PostComments from "./partials/PostComments";
import MembershipStatus from "./partials/MembershipStatus";
import MembershipStoreCredit from "./partials/MembershipStoreCredit";
import MembershipCustomer from "./partials/MembershipCustomer";
import MembershipBillingInfo from "./partials/MembershipBillingInfo";
import MembershipNotes from "./partials/MembershipNotes";
import GlobalSkeleton from "../../GlobalPartials/GlobalSkeleton";
import Customermemberships from "./partials/Customermemberships";
import Breadcrums from "../../GlobalPartials/Breadcrums/Breadcrums";
import { subscriberEdit } from "../../../data/features/memberDetails/membersDetailsAction";
import { useDispatch } from "react-redux";
import { useNavigate, useParams } from "react-router-dom";

export default function NewMemberDetails() {
    const { id } = useParams();
    const navigate = useNavigate();
    const dispatch = useDispatch();
    const members = useSelector((state) => state.members?.data?.memberships);
    const isLoading$ = useSelector((state) => state.memberDetails?.isLoading);

    const contract = useSelector(
        (state) => state.memberDetails?.data?.contract
    );
    const contractslist$ = useSelector(
        (state) => state.memberDetails?.data?.contracts_list
    );

    let getIndex = 0;
    // next and previous methods.....
    if (contractslist$) {
        contractslist$.map((val, key) => {
            if (val.id == id) {
                getIndex = key;
            }
        });
    }

    // prev.....
    let index = getIndex;
    const prevMethod = useCallback(() => {
        index = index === 0 ? 0 : index - 1;

        navigate(`/members/${contractslist$[index].id}/edit`);

        dispatch(
            subscriberEdit({
                page: members?.current_page,
                id: parseInt(contractslist$[index].id),
            })
        );
    }, [contractslist$, contract]);

    // next.....
    const nextMethod = useCallback(() => {
        index =
            index == contractslist$?.length - 1
                ? contractslist$?.length - 1
                : index + 1;
        navigate(`/members/${contractslist$[index].id}/edit`);

        dispatch(
            subscriberEdit({
                page: members?.current_page,
                id: parseInt(contractslist$[index].id),
            })
        );
    }, [contractslist$, contract]);

    // API call of subscribe edit...
    useEffect(() => {
        dispatch(
            subscriberEdit({ page: members?.current_page, id: parseInt(id) })
        );
    }, []);

    return (
        <>
            {!isLoading$ ? (
                <div className="member_details_main_wrap">
                    <div className="simplee_membership_container">
                        {/* members breadcrums & pagination */}
                        <Breadcrums
                            is_plandetail_show={true}
                            to={"/members"}
                            title={`${contract?.customer?.first_name} ${contract?.customer?.last_name}`}
                            member_number={contract?.member_number
                                ?.toString()
                                .padStart(6, "0")}
                            email={contract?.customer?.email}
                            phone_number={contract?.customer?.phone}
                            shopify_contract_id={contract?.shopify_contract_id}
                            status={contract?.status}
                            is_migrated={contract?.is_migrated}
                            is_onetime_payment={contract.is_onetime_payment}
                            showEmail={true}
                            prevMehod={prevMethod}
                            nextMehod={nextMethod}
                            back={() => ""}
                            hasNext={index < contractslist$?.length - 1}
                            hasPrev={index >= 1}
                        />

                        {/* Membership Details & All Informations */}
                        <div className="membership_detail_info_wrap comman_two_column_style ms-margin-top">
                            <div className="sub_col first_info_col">
                                {/* order details */}
                                {contract?.shopify_contract_id &&
                                contract?.is_migrated ? (
                                    ""
                                ) : contract?.shopify_contract_id == null &&
                                  contract?.is_onetime_payment == 1 ? (
                                    ""
                                ) : (
                                    <MemberOrderDetail />
                                )}

                                {/* Member Details */}
                                <MemberShipDetailComp />

                                {/* Product order table */}
                                {contract?.shopify_contract_id == null &&
                                contract?.is_onetime_payment == 1 ? (
                                    ""
                                ) : (
                                    <ProductOrderTable />
                                )}

                                {/* Questions and answers */}
                                <QuestionAnswers />

                                {/* post comments */}
                                <PostComments />
                            </div>
                            <div className="sub_col second_info_col">
                                {/* status */}
                                <MembershipStatus />

                                {/* Store Credit */}
                                {/* <MembershipStoreCredit /> */}

                                {/* Customer */}
                                <MembershipCustomer />

                                {/* Billing Information */}

                                {
                                contract?.shopify_contract_id ? (
                                    <MembershipBillingInfo />
                                ) : (
                                    ""
                                )}

                                {/* Notes */}
                                <MembershipNotes />

                                {/* Customer memberships */}
                                <Customermemberships />
                            </div>
                        </div>
                    </div>
                </div>
            ) : (
                <GlobalSkeleton />
            )}
        </>
    );
}
