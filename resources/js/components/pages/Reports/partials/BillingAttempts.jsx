import {
    TextField,
    Text,
    IndexTable,
    LegacyCard,
    EmptySearchResult,
    ChoiceList,
    Pagination,
    Link,
    Badge,
} from "@shopify/polaris";
import React, { useState, useEffect, useCallback } from "react";
import { useSelector } from "react-redux";
import { useDispatch } from "react-redux";
import Renewal from "../../../../../images/Renewals.svg";
import { recentBillingAttempts } from "../../../../data/features/reports/reportAction";
import { defultFilterUpdate } from "../../../../data/features/reports/reportSlice";
import { Reports_Filters } from "./Reports_Filters";
import { useNavigate } from "react-router-dom";

export default function BillingAttempts () {
    const dispatch = useDispatch();
    const recent_biilling_attempts = useSelector(
        (state) => state?.reports?.data?.recent_biilling_attempts,
    );
    const navigate = useNavigate();
    const defaultFilter$ = useSelector(
        (state) => state?.reports?.defaultFilter,
    );

    const hourswithleadingzero = useCallback((datetimeString, userTimezone) => {
        const [datePart, timePart] = datetimeString.split(" ");
        const [year, month, day] = datePart.split("-").map(Number);
        const [hour, minute, second] = timePart.split(":").map(Number);

        // Create a Date object in UTC
        const utcDate = new Date(
            Date.UTC(year, month - 1, day, hour, minute, second),
        );

        // Convert the Date object to the user's timezone
        const options = { timeZone: userTimezone };
        const formattedDate = utcDate.toLocaleString("en-US", options);

        var inputDate = new Date(formattedDate);

        // Extract date components
        var monthNames = [
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

        // Extract date components
        var months = monthNames[inputDate.getMonth()];
        var days = inputDate.getDate();
        var years = inputDate.getFullYear();
        var hours = ("0" + inputDate.getHours()).slice(-2);
        var minutes = ("0" + inputDate.getMinutes()).slice(-2);

        // Construct formatted date string
        var outputDateString =
            months + " " + days + ", " + years + ", " + hours + ":" + minutes;

        return outputDateString;
    });
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
        dispatch(recentBillingAttempts(queryString));
    }, [defaultFilter$]);

    const rowmarkup =
        recent_biilling_attempts?.data?.length > 0 &&
        recent_biilling_attempts.data.map(
            (
                {
                    id,
                    completedAt,
                    status,
                    member_number,
                    errorMessage,
                    first_name,
                    last_name,
                    order_amount,
                    shopify_order_name,
                    myshopify_domain,
                    currency_symbol,
                    shopify_order_id,
                    iana_timezone,
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
                        <Text
                            variant="bodyMd"
                            fontWeight="medium"
                            as="span"
                            alignment="end"
                        >
                            {member_number}
                        </Text>
                    </IndexTable.Cell>

                    <IndexTable.Cell>
                        <Link
                            onClick={() =>
                                navigate(`/members/${id}/edit?=reports`)
                            }
                        >
                            {first_name + " " + last_name}
                        </Link>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Text
                            variant="bodyMd"
                            fontWeight="medium"
                            as="span"
                            alignment="end"
                        >
                            {hourswithleadingzero(completedAt, iana_timezone)}
                        </Text>
                    </IndexTable.Cell>

                    <IndexTable.Cell>
                        {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                        {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                        {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                        <Badge
                            tone={
                                status == "successful"
                                    ? "success"
                                    : status == "sent"
                                      ? "info"
                                      : status == "failed"
                                        ? "critical"
                                        : ""
                            }
                        >
                            {status}
                        </Badge>
                    </IndexTable.Cell>

                    <IndexTable.Cell>
                        <Text
                            variant="bodyMd"
                            fontWeight="medium"
                            as="span"
                            alignment="end"
                        >
                            {order_amount && currency_symbol} {order_amount}
                        </Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Link
                            url={
                                "https://admin.shopify.com/store/" +
                                (myshopify_domain?.replace(
                                    ".myshopify.com",
                                    "",
                                ) || "") +
                                "/orders/" +
                                shopify_order_id
                            }
                            external
                        >
                            <Text
                                variant="bodyMd"
                                fontWeight="medium"
                                as="span"
                            >
                                {shopify_order_name}
                            </Text>
                        </Link>
                    </IndexTable.Cell>

                    <IndexTable.Cell>
                        <Text variant="bodyMd" fontWeight="medium" as="span">
                            {errorMessage ? errorMessage : "-"}
                        </Text>
                    </IndexTable.Cell>
                </tr>
            ),
        );

    const sortOptions = [
        {
            label: "Member Number",
            value: "Member_Number asc",
            directionLabel: "Ascending",
        },
        {
            label: "Member Number",
            value: "Member_Number desc",
            directionLabel: "Descending",
        },
        {
            label: "Customer Name",
            value: "Customer_Name asc",
            directionLabel: "Ascending",
        },
        {
            label: "Customer Name",
            value: "Customer_Name desc",
            directionLabel: "Descending",
        },
        {
            label: "Date",
            value: "completedAt asc",
            directionLabel: "Ascending",
        },
        {
            label: "Date",
            value: "completedAt desc",
            directionLabel: "Descending",
        },
    ];

    const [sortSelected, setSortSelected] = useState(["completedAt desc"]);
    const [queryValue, setQueryValue] = useState("");
    const appliedFilters = [];
    const sorting = useCallback(
        (selectedOption) => {
            let order = selectedOption[0];
            dispatch(upcomingSortReducer({ order }));
        },
        [recent_biilling_attempts],
    );

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

    // filter items
    const filetrsObj = {
        status: undefined,
        ErrorMessage: undefined,
    };

    const [status, setStatus] = useState(filetrsObj);
    const handleStatusChange = useCallback(
        (value, key) => {
            setStatus({
                ...status,
                [key]: value,
            });
            const defaultFilter = {
                [key === "status" ? "lp" : "em"]: value,
            };
            dispatch(defultFilterUpdate(defaultFilter));
        },
        [status],
    );

    const activePlans$ = useSelector(
        (state) => state?.members?.data?.activePlans,
    );

    const activePlansData =
        activePlans$.length > 0
            ? activePlans$?.map((item) => {
                  return { label: item, value: item };
              })
            : "";
    const filters = [
        {
            key: "status",
            label: "status",
            filter: (
                <ChoiceList
                    title="Status"
                    titleHidden
                    choices={[
                        { label: "Sent", value: "sent" },
                        { label: "Succesful", value: "successful" },
                        { label: "Failed", value: "failed" },
                    ]}
                    selected={status.status || []}
                    onChange={(value) => handleStatusChange(value, "status")}
                    allowMultiple
                />
            ),
            shortcut: true,
        },
        {
            key: "ErrorMessage",
            label: "ErrorMessages",
            filter: (
                <ChoiceList
                    title="Status"
                    titleHidden
                    choices={[
                        {
                            label: "Your card's expiration year is invalid",
                            value: "Your card's expiration year is invalid",
                        },
                        {
                            label: "Payment provider is not enabled on the shop",
                            value: "Payment provider is not enabled on the shop",
                        },
                        {
                            label: "Payment method is not ready",
                            value: "Payment method is not ready",
                        },
                        {
                            label: "Amount must be no more than $999,999.99",
                            value: "Amount must be no more than $999,999.99",
                        },
                    ]}
                    selected={status.ErrorMessage || []}
                    onChange={(value) =>
                        handleStatusChange(value, "ErrorMessage")
                    }
                    allowMultiple
                />
            ),
            shortcut: true,
        },
    ];

    const handleQueryValueRemove = useCallback(() => {
        setQueryValue("");
    });
    // serach items onChange event
    const handleFiltersQueryChange = useCallback(
        (value) => {
            const defaultFilter = {
                s: value,
            };
            setQueryValue(value);
            dispatch(defultFilterUpdate(defaultFilter));
            // dispatch(getUpcomingRenewals(value));
        },
        [queryValue, defaultFilter$],
    );

    const handleStatusRemove = useCallback((val, key) => {
        setStatus(filetrsObj);
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

    const setLabel = (key, value) => {
        switch (key) {
            case "status":
                return "Status: " + value?.map((val) => `${val}`).join(", ");
            case "ErrorMessage":
                return (
                    "ErrorMessage: " + value?.map((val) => `${val}`).join(", ")
                );
            default:
                return value;
        }
    };
    if (status.status && !isEmpty(status.status)) {
        const key = "status";
        appliedFilters.push({
            key,
            label: setLabel(key, status.status),
            onRemove: handleStatusRemove,
        });
    }

    if (status.ErrorMessage && !isEmpty(status.ErrorMessage)) {
        const key = "ErrorMessage";
        appliedFilters.push({
            key,
            label: setLabel(key, status.ErrorMessage),
            onRemove: handleStatusRemove,
        });
    }

    let totalPage = Math.ceil(recent_biilling_attempts?.total / 20);
    let index = recent_biilling_attempts?.current_page;

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
    }, [defaultFilter$, recent_biilling_attempts, index]);

    const emptyStateMarkup = (
        <EmptySearchResult
            title= { <Text>No Billing Attempts Found</Text>}
            description={"Try changing the filters or search term"}
            withIllustration
        />
    );

    return (
        <>
            <div className="simplee_membership_main_wrap" >
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
                                recent_biilling_attempts?.data?.length > 0
                                    ? recent_biilling_attempts?.data?.length
                                    : 0
                            }
                            selectable={false}
                            emptyState={emptyStateMarkup}
                            headings={[
                                { title: "Member", alignment: "end" },
                                { title: "Customer Name" },
                                { title: "Date", alignment: "end" },
                                { title: "Status" },

                                // { title: "Store Credit", alignment: 'center' },

                                { title: "Amount", alignment: "end" },

                                { title: "Order" },
                                { title: "Error Message" },
                            ]}
                        >
                            {rowmarkup}
                        </IndexTable>
                        <div className="memberlist_pagination">
                            <Pagination
                                hasPrevious={
                                    recent_biilling_attempts?.total > 20 &&
                                    index > 1
                                }
                                onPrevious={() => {
                                    prevPagination();
                                }}
                                hasNext={
                                    recent_biilling_attempts?.total > 20 &&
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
