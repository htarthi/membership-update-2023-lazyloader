import { LegacyCard, Text } from "@shopify/polaris";
import React from "react";
import { useSelector } from "react-redux";
import { useLocation } from 'react-router-dom';

export default function MemberOrderDetail() {
    const contract = useSelector((state) => state.memberDetails?.data?.contract);
    const location = useLocation();
    return (
        <div className="member_detail_order_detail main_box_wrap">
            <LegacyCard>
                <div className="order_detail_list_row">
                    <div className="order_detail_main_col">
                        <div className="order_detail_list_col">
                            <Text
                                variant="bodyLg"
                                as="h6"
                                fontWeight="regular"
                                tone="subdued"
                            >
                                First order
                            </Text>
                            <Text
                                variant="headingMd"
                                as="h6"
                                fontWeight="regular"
                            >
                                {contract?.customer?.date_first_order}
                            </Text>
                        </div>
                        <div className="order_detail_list_col">
                            <Text
                                variant="bodyLg"
                                as="h6"
                                fontWeight="regular"
                                tone="subdued"
                            >
                                Order count
                            </Text>
                            <Text
                                variant="headingMd"
                                as="h6"
                                fontWeight="regular"
                            >
                                {contract?.order_count}
                            </Text>
                        </div>
                    </div>

                    <div className="order_detail_main_col">
                        <div className="order_detail_list_col">
                            <Text
                                variant="bodyLg"
                                as="h6"
                                fontWeight="regular"
                                tone="subdued"
                            >
                                Lifetime membership orders
                            </Text>
                            <Text
                                variant="headingMd"
                                as="h6"
                                fontWeight="regular"
                            >
                                {contract?.customer?.total_orders}
                            </Text>
                        </div>
                        <div className="order_detail_list_col">
                            <Text
                                variant="bodyLg"
                                as="h6"
                                fontWeight="regular"
                                tone="subdued"
                            >
                                Lifetime spend
                            </Text>
                            <Text
                                variant="headingMd"
                                as="h6"
                                fontWeight="regular"
                            >
                                {contract?.customer?.currency_symbol} {contract?.customer?.total_spend}
                            </Text>
                        </div>
                    </div>
                </div>
            </LegacyCard>
        </div>
    );
}
