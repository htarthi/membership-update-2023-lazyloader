import {
    Text,
    IndexTable,
    LegacyCard,
    EmptySearchResult,
    Pagination,
    Link,
    Badge,
} from "@shopify/polaris";
import React, { useState, useEffect, useCallback } from "react";
import { useSelector } from "react-redux";
import { useDispatch } from "react-redux";
import { recentCancellations } from "../../../../data/features/reports/reportAction";
import { defultFilterUpdate } from "../../../../data/features/reports/reportSlice";
import { Reports_Filters } from "./Reports_Filters";
import { useNavigate } from "react-router-dom";

export default function RecentCancellation() {
    const dispatch = useDispatch();
    const recent_cancellation = useSelector(
        (state) => state?.reports?.data?.recent_cancellation,
    );
    const navigate = useNavigate();
    const defaultFilter$ = useSelector(
        (state) => state?.reports?.defaultFilter,
    );
    const shop$ = useSelector((state) => state.plans?.data?.shop);
    const month = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "May",
        "Jun",
        "Jul",
        "Aug",
        "Sep",
        "Oct",
        "Nov",
        "Dec",
    ];

    const resourceName = {
        singular: "row",
        plural: "rows",
    };

    useEffect(() => {
        let queryString = {
            s: defaultFilter$?.s,
            p: defaultFilter$?.p,
            lp: defaultFilter$?.lp,
            em: defaultFilter$?.em,
            sk: defaultFilter$?.sk,
            sv: defaultFilter$?.sv,
            page: defaultFilter$?.page,
        };
        dispatch(recentCancellations(queryString));
    }, [defaultFilter$]);

    const rowmarkup =
        recent_cancellation?.data?.length > 0 &&
        recent_cancellation.data.map(
            (
                {
                    id,
                    cancelled_by,
                    first_name,
                    last_name,
                    order_count,
                    name,
                    created_at,
                    ss_contract_id,
                },
                index,
            ) => (
                <tr
                    id={id}
                    key={id}
                    position={index}
                    className="Polaris-IndexTable__TableRow membership_table_tr"
                >
                    <IndexTable.Cell>
                        <Text variant="bodyMd" fontWeight="medium" as="span">
                            {month[new Date(created_at).getMonth()] +
                                " " +
                                new Date(created_at).getDate() +
                                ", " +
                                new Date(created_at).getFullYear()}
                        </Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Link
                            onClick={() =>
                                navigate(
                                    `/members/${ss_contract_id}/edit?=reports`,
                                )
                            }
                        >
                            {first_name + " " + last_name}
                        </Link>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Text variant="bodyMd" fontWeight="medium" as="span">
                            {cancelled_by === "owner" ? (
                                <Badge tone="success">Merchant</Badge>
                            ) : (
                                <Badge tone="info">Member</Badge>
                            )}
                        </Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Text variant="bodyMd" fontWeight="medium" as="span">
                            {name}
                        </Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>{order_count}</IndexTable.Cell>
                </tr>
            ),
        );

    const sortOptions = [
        {
            label: "Customer Number",
            value: "Customer_Name asc",
            directionLabel: "Ascending",
        },
        {
            label: "Customer Number",
            value: "Customer_Name desc",
            directionLabel: "Descending",
        },
        { label: "Date", value: "created_at asc", directionLabel: "Ascending" },
        {
            label: "Date",
            value: "created_at desc",
            directionLabel: "Descending",
        },
    ];

    const [sortSelected, setSortSelected] = useState(["created_at desc"]);
    const [queryValue, setQueryValue] = useState("");

    const appliedFilters = [];

    const handleSortChange = useCallback(
        (selectedOption) => {
            setSortSelected(selectedOption);
            const sliceOption = selectedOption[0].split(" ");
            const defaultFilter = {
                sk: sliceOption[0],
                sv: sliceOption[1],
            };
            dispatch(defultFilterUpdate(defaultFilter));
        },
        [sortSelected, defaultFilter$],
    );

    const filters = [];

    const handleQueryValueRemove = useCallback(() => {
        setQueryValue("");
    });
    const handleFiltersQueryChange = useCallback(
        (value) => {
            const defaultFilter = {
                s: value,
            };
            setQueryValue(value);
            dispatch(defultFilterUpdate(defaultFilter));
        },
        [queryValue, defaultFilter$],
    );

    const handleStatusRemove = useCallback((val, key) => {
        const defaultFilter = {
            lp: "",
            em: "",
        };
        dispatch(defultFilterUpdate(defaultFilter));
    }, []);
    // clear all status filter
    const handleFiltersClearAll = useCallback(() => {
        handleStatusRemove();
        handleQueryValueRemove();
    }, [handleStatusRemove]);

    const isEmpty = (value) => {
        if (Array.isArray(value)) {
            return value.length === 0;
        } else {
            return value === "" || value == null;
        }
    };

    let totalPage = Math.ceil(recent_cancellation?.total / 20);
    let index = recent_cancellation?.current_page;

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
    }, [defaultFilter$, recent_cancellation, index]);

    const emptyStateMarkup = (
        <EmptySearchResult
            title={<Text>No Recent Cancellations Found</Text>}
            description={"Try changing the filters or search term"}
            withIllustration
        />
    );

    return (
        <>
            <div className="simplee_membership_main_wrap">
                <div className="simplee_membership_container">
                    <Reports_Filters
                        sortOptions={sortOptions}
                        sortSelected={sortSelected}
                        handleSortChange={handleSortChange}
                        filters={filters}
                        handleFiltersClearAll={handleFiltersClearAll}
                        queryValue={queryValue}
                        setQueryValue={setQueryValue}
                        handleFiltersQueryChange={handleFiltersQueryChange}
                        appliedFilters={appliedFilters}
                    />
                    <LegacyCard>
                        <IndexTable
                            resourceName={resourceName}
                            itemCount={
                                recent_cancellation?.data?.length > 0
                                    ? recent_cancellation?.data?.length
                                    : 0
                            }
                            selectable={false}
                            emptyState={emptyStateMarkup}
                            headings={[
                                { title: "Date" },
                                { title: "Customer Name" },
                                { title: "Cancelled By" },
                                { title: "Plan/Tier" },
                                { title: "Order Count" },
                            ]}
                        >
                            {rowmarkup}
                        </IndexTable>

                        <div className="memberlist_pagination">
                            <Pagination
                                hasPrevious={
                                    recent_cancellation?.total > 20 && index > 1
                                }
                                onPrevious={() => {
                                    prevPagination();
                                }}
                                hasNext={
                                    recent_cancellation?.total > 20 &&
                                    index < totalPage
                                }
                                onNext={() => {
                                    nextPagination();
                                }}
                            />
                        </div>
                    </LegacyCard>
                </div>
            </div>
        </>
    );
};
