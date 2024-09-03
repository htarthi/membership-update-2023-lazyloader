import React, { useCallback, useEffect, useState } from "react";
import {
    IndexTable,
    LegacyCard,
    Text,
    Badge,
    Pagination,
    Link,
} from "@shopify/polaris";
import { useSelector } from "react-redux";
import { useNavigate } from "react-router-dom";
import Filters from "./Filters";
import { useDispatch } from "react-redux";
import {
    defultFilterUpdate,
    memberSortREducer,
} from "../../../../data/features/members/membersSlice";
import { getMembersList } from "../../../../data/features/members/membersAction";

export default function NewMemberList() {
    const defaultFilter$ = useSelector(
        (state) => state?.members?.defaultFilter,
    );
    const navigate = useNavigate();
    // Redux Data
    const members = useSelector(
        (state) => state.members?.data?.memberships?.data,
    );
    const memberships$ = useSelector(
        (state) => state.members?.data?.memberships,
    );
    const isSuccess$ = useSelector((state) => state.members?.isSuccess);
    const dispatch = useDispatch();

    const sorting = useCallback(
        (selectedOption) => {
            let order = selectedOption[0];
            dispatch(memberSortREducer({ order }));
        },
        [members],
    );
    // Table row
    const resourceName = {
        singular: "row",
        plural: "rows",
    };

    const rowMarkup =
        members?.length > 0 &&
        members?.map(
            (
                {
                    id,
                    first_name,
                    last_name,
                    status,
                    storeCredit,
                    date_first_order,
                    next_order_date,
                    last_payment_status,
                    next_processing_date,
                    status_billing,
                    actions,
                    is_onetime_payment,
                    member_number,
                },
                index,
            ) => (
                <tr
                    id={id}
                    key={id}
                    position={index}
                    onClick={() => navigate(`/members/${id}/edit`)}
                    className="Polaris-IndexTable__TableRow membership_table_tr"
                >
                    <IndexTable.Cell>
                        {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                        {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                        <Text
                            alignment="center"
                            variant="bodyMd"
                            fontWeight="medium"
                            as="span"
                            color={`${
                                last_payment_status === "paid" &&
                                status === "active"
                                    ? "subdued"
                                    : ""
                            }`}
                        >
                            {member_number}
                        </Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Link
                            alignment="start"
                            onClick={() => navigate(`/members/${id}/edit`)}
                        >
                            {first_name || "-"} {last_name || "-"}{" "}
                        </Link>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Text alignment="center">
                            {" "}
                            {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                            {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                            {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                            <Badge
                                tone={
                                    status === "active"
                                        ? "success"
                                        : status === "expiring"
                                          ? "attention"
                                          : status === "cancelled"
                                            ? "critical"
                                            : ""
                                }
                                progress={
                                    status === "active"
                                        ? ""
                                        : status === "expiring"
                                          ? ""
                                          : status === "cancelled"
                                            ? ""
                                            : ""
                                }
                            >
                                {/* {defaultFilter$?.f} */}
                                {status}
                            </Badge>
                        </Text>
                    </IndexTable.Cell>
                    {/* <IndexTable.Cell>
                    <Text
                        variant="bodyMd"
                        fontWeight="medium"
                        as="span"
                        alignment="center"
                        color={`${
                            last_payment_status === "paid" && status === "active"
                                ? "subdued"
                                : ""
                        }`}
                    >
                        {storeCredit || "0"}
                    </Text>
                </IndexTable.Cell> */}
                    <IndexTable.Cell>
                        {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                        {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                        <Text
                            variant="bodyMd"
                            fontWeight="medium"
                            as="span"
                            color={`${
                                last_payment_status === "paid" &&
                                status === "active"
                                    ? "subdued"
                                    : ""
                            }`}
                        >
                            {date_first_order || "-"}
                        </Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                        {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                        <Text
                            variant="bodyMd"
                            fontWeight="medium"
                            as="span"
                            color={`${
                                last_payment_status === "paid" &&
                                status === "active"
                                    ? "subdued"
                                    : ""
                            }`}
                        >
                            {is_onetime_payment === 0
                                ? defaultFilter$?.f == "failed"
                                    ? next_processing_date
                                    : next_order_date
                                : "-"}
                        </Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                        {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                        {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                        <Badge
                            tone={
                                last_payment_status === "Successful"
                                    ? "success"
                                    : last_payment_status === "pending"
                                      ? "attention"
                                      : last_payment_status === "failed"
                                        ? "critical"
                                        : ""
                            }
                            progress={
                                last_payment_status === "Successful"
                                    ? ""
                                    : last_payment_status === "pending"
                                      ? ""
                                      : last_payment_status === "failed"
                                        ? ""
                                        : ""
                            }
                        >
                            {last_payment_status}
                        </Badge>
                    </IndexTable.Cell>
                    {/* <IndexTable.Cell>
                    <Text
                        variant="bodyMd"
                        fontWeight="medium"
                        as="span"
                        color={`${
                            last_payment_status === "paid" && status === "active"
                                ? "subdued"
                                : ""
                        }`}
                    >
                        {actions || "-"}
                    </Text>
                </IndexTable.Cell> */}
                </tr>
            ),
        );

    // pagination....
    let totalPage = Math.ceil(memberships$?.total / 20);
    let index = isSuccess$ ? memberships$?.current_page : 1;

    const prevPagination = useCallback(() => {
        index = index === 0 ? 1 : index - 1;
        const defaultFilter = {
            page: index,
        };
        dispatch(defultFilterUpdate(defaultFilter));
    }, [defaultFilter$, index]);

    const nextPagination = useCallback(() => {
        index = index < totalPage ? index + 1 : totalPage;

        const defaultFilter = {
            page: index,
        };
        dispatch(defultFilterUpdate(defaultFilter));
    }, [defaultFilter$, memberships$, index]);

    useEffect(() => {
        let queryString = {
            s: defaultFilter$?.s,
            f: defaultFilter$?.f,
            p: defaultFilter$?.p,
            lp: defaultFilter$?.lp,
            sk: defaultFilter$?.sk,
            sv: defaultFilter$?.sv,
            page: defaultFilter$?.page,
        };

        dispatch(getMembersList(queryString));
    }, [defaultFilter$]);

    return (
        <div className="simplee_membership_main_wrap">
            <div className="simplee_membership_container">
                <LegacyCard>
                    {/* filter */}
                    <Filters filterSorting={sorting} />

                    {/* table */}
                    <IndexTable
                        resourceName={resourceName}
                        itemCount={members?.length > 0 ? members?.length : 0}
                        selectable={false}
                        headings={[
                            { title: "Member  #", alignment: "start" },
                            { title: "Customer", alignment: "start" },
                            { title: "Status", alignment: "center" },
                            // { title: "Store Credit", alignment: 'center' },
                            { title: "Start Date" },
                            { title: "Next Billing Date" },
                            { title: "Last Payment" },
                            // { title: "Actions" },
                        ]}
                    >
                        {rowMarkup}
                    </IndexTable>

                    {/* pagination */}
                    <div className="memberlist_pagination">
                        <Pagination
                            hasPrevious={memberships$?.total > 20 && index > 1}
                            onPrevious={() => {
                                prevPagination();
                            }}
                            hasNext={
                                memberships$?.total > 20 && index < totalPage
                            }
                            onNext={() => {
                                nextPagination();
                            }}
                        />
                    </div>
                </LegacyCard>
            </div>
        </div>
    );
}
