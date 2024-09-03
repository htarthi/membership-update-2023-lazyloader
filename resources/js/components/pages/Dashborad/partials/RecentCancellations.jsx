import {
    Text,
    LegacyCard,
    DataTable,
    SkeletonBodyText,
    Badge,
    Link,
} from "@shopify/polaris";
import React from "react";
import Cancellation from "../../../../../images/Cancellations.svg";
import { useSelector } from "react-redux";
import { useNavigate } from "react-router-dom";

const RecentCancellations = () => {
    const dashboard = useSelector(
        (state) => state?.dashboard?.data?.recent_cancelation,
    );

    const shop$ = useSelector((state) => state?.dashboard?.data?.shop);
    const domain = shop$?.domain;
    const navigate = useNavigate();

    const rows = dashboard.map((item, index) => {
        const options = { year: "numeric", month: "short", day: "2-digit" };
        return [
            new Date(item.created_at).toLocaleDateString("en-US", options),
            <Link
                onClick={() =>
                    navigate(`/members/${item.ss_contract_id}/edit?=dashboard`)
                }
            >
                {item.first_name + " " + item.last_name}
            </Link>,
            item.cancelled_by === "owner" ? (
                <Badge tone="success">Merchant</Badge>
            ) : (
                <Badge tone="info">Member</Badge>
            ),
            item.name,
            <Text alignment="center">{item.order_count}</Text>,
        ];
    });

    return (
        <>
            <div>
                {dashboard.length > 0 ? (
                    <LegacyCard>
                        <DataTable
                            columnContentTypes={[
                                "text",
                                "text",
                                "text",
                                "text",
                                "numeric",
                            ]}
                            headings={[
                                "Date",
                                "Customer Name",
                                "Cancelled By",
                                "Plan/Tier",
                                "Order Count",
                            ]}
                            rows={rows}
                        />
                    </LegacyCard>
                ) : (
                    <div className="NoData">
                        <img src={Cancellation} alt="No Data" />
                        <Text variant="headingMd" as="h6" fontWeight='regular' tone='base'>
                            Recent membership cancellations will appear in this
                            list
                        </Text>
                    </div>
                )}
            </div>
        </>
    );
};

export default RecentCancellations;
