import { Badge, Icon, IndexTable, LegacyCard, Link, Pagination, Text , Tooltip } from "@shopify/polaris";
import React, { useCallback , useState} from "react";
import { useSelector } from "react-redux";
import { billingAttempts, subscriberEdit } from "../../../../data/features/memberDetails/membersDetailsAction";
import { useParams } from "react-router-dom";
import { useDispatch } from "react-redux";
import { toast } from "react-toastify";

export default function ProductOrderTable() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const contract = useSelector(
        (state) => state.memberDetails.data?.contract?.billingAttempt?.data
    );
    const billingAttempt$ = useSelector(
        (state) => state.memberDetails.data?.contract?.billingAttempt
    );
    const isSuccess$ = useSelector((state) => state.memberDetails?.isSuccess);

    const domain = useSelector((state) => state.memberDetails.data?.shop);

    const next_billing_date$ = useSelector((state)=>state.memberDetails.data?.contract?.next_processing_date);


    const rowMarkup = contract?.length > 0 && contract?.map(
        (
            { shopify_order_id, total, created_at, shopify_order_name, status, next_processing_date , errorMessage },
            index
        ) => (
            <IndexTable.Row id={shopify_order_id} key={index} position={index}>
                <IndexTable.Cell>
                    <div className="order_created">
                    {
                        status != "failed" ?  <Link target="_blank" url={"https://admin.shopify.com/store/" + domain?.name + "/orders/" + shopify_order_id} >{shopify_order_name || ""}</Link> : ''
                    }
                    </div>
                </IndexTable.Cell>
                <IndexTable.Cell>
                    <Text fontWeight="regular" variant="bodyLg" as="h6">
                    {created_at || "-"}
                        {/* {
                            status != "failed" ? created_at || "" : ''
                        } */}
                    </Text>
                </IndexTable.Cell>
                <IndexTable.Cell>
                    <div className="badge_wrap">
                        {
                            errorMessage  ? <Tooltip content={errorMessage} padding='300' width="wide">
                                                <Badge size="small"
                                                    progress={
                                                        status === "paid"
                                                            ? "complete"
                                                            : status === "pending"
                                                                ? "partiallyComplete"
                                                                : status === "failed"
                                                                    ? "incomplete"
                                                                    : ""
                                                    }
                                                    tone={
                                                        status === "paid"
                                                            ? ""
                                                            : status === "pending"
                                                                ? "attention"
                                                                : status === "failed"
                                                                    ? "critical"
                                                                    : ""
                                                    }
                                                >{status}
                                                </Badge>
                                            </Tooltip> :
                                                <Badge size="small"
                                                    progress={
                                                        status === "paid"
                                                            ? "complete"
                                                            : status === "pending"
                                                                ? "partiallyComplete"
                                                                : status === "failed"
                                                                    ? "incomplete"
                                                                    : ""
                                                    }
                                                    tone={
                                                        status === "paid"
                                                            ? ""
                                                            : status === "pending"
                                                                ? "attention"
                                                                : status === "failed"
                                                                    ? "critical"
                                                                    : ""
                                                    }
                                                >{status}
                                                </Badge>
                        }

                        {status === "failed" && (
                            <span className="next_billing_attempt">
                                Next billing attempt: {next_billing_date$}
                            </span>

                        )}
                    </div>
                </IndexTable.Cell>
                <IndexTable.Cell>
                    <Text fontWeight="regular" variant="bodyLg" as="h6">
                        {
                            status != "failed" ? domain?.currency + " " + total || "" : ''
                        }
                    </Text>
                </IndexTable.Cell>
            </IndexTable.Row>
        )
    );


    // pagination....
    let totalPage = Math.ceil(billingAttempt$?.total / 5);
    let index = isSuccess$ ? billingAttempt$?.current_page : 1;

    const prevPagination = useCallback(() => {
        index = index === 0 ? 1 : index - 1;
        dispatch(billingAttempts({ page: index, id: parseInt(id) }))
    }, [billingAttempt$, index])

    const nextPagination = useCallback(() => {
        index = index < totalPage ? index + 1 : totalPage;
        dispatch(billingAttempts({ page: index, id: parseInt(id) }))
    }, [billingAttempt$, index])

    return (
        <div className="product_order_table_wrap ms-margin-top main_box_wrap">
            <LegacyCard>
                <IndexTable
                    itemCount={contract?.length > 0 ? contract?.length : 0}
                    selectable={false}
                    headings={[
                        {
                            id: 1,
                            title: (
                                <Text
                                    fontWeight="regular"
                                    variant="bodyLg"
                                    as="h6"
                                >
                                    Order #
                                </Text>
                            ),
                        },
                        {
                            id: 2,
                            title: (
                                <Text
                                    fontWeight="medium"
                                    variant="bodyLg"
                                    as="h6"
                                >
                                    Create date
                                </Text>
                            ),
                        },
                        {
                            id: 3,
                            title: (
                                <Text
                                    fontWeight="medium"
                                    variant="bodyLg"
                                    as="h6"
                                >
                                    Payment status
                                </Text>
                            ),
                        },
                        {
                            id: 4,
                            title: (
                                <Text
                                    fontWeight="medium"
                                    variant="bodyLg"
                                    as="h6"
                                >
                                    Total
                                </Text>
                            ),
                        },
                    ]}
                >
                    {rowMarkup}
                </IndexTable>

                {/* pagination */}
                {
                    billingAttempt$?.total > 5 &&
                    <div className="memberlist_pagination">
                        <Pagination
                            hasPrevious={billingAttempt$?.total > 5 && index > 1}
                            onPrevious={() => {
                                prevPagination()
                            }}
                            hasNext={billingAttempt$?.total > 5 && index < totalPage}
                            onNext={() => {
                                nextPagination()
                            }}
                        />
                    </div>
                }
            </LegacyCard>
        </div>
    );
}
