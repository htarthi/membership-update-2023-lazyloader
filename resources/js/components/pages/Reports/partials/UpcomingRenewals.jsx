import { ChoiceList, EmptySearchResult, IndexTable, LegacyCard, Link, Pagination, Text, TextField } from "@shopify/polaris";
import React, { useEffect, useState, useCallback } from "react";
import { useDispatch } from "react-redux";
import { activePlans, getUpcomingRenewals } from "../../../../data/features/reports/reportAction";
import { useSelector } from "react-redux";
import Renewal from "../../../../../images/Renewals.svg";
import { Reports_Filters } from "./Reports_Filters";
import { defultFilterUpdate, upcomingSortReducer } from "../../../../data/features/reports/reportSlice";
import { slice } from "lodash";
import { useNavigate } from "react-router-dom";


export default function UpcomingRenewals  ()  {
    const dispatch = useDispatch();

    const defaultFilter$ = useSelector((state) => state?.reports?.defaultFilter);
    const activePlans$ = useSelector((state) => state?.reports?.ActivePlans);
    const navigate = useNavigate();


    const upcoming_renewals = useSelector(
        (state) => state?.reports?.data?.upcoming_renewals
    );
    const [search, setSearch] = useState("");
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
            sk: defaultFilter$?.sk,
            sv: defaultFilter$?.sv,
            page: defaultFilter$?.page,
        };
        dispatch(getUpcomingRenewals(queryString));
        // if (activePlans$.length == 0) {
        // dispatch(activePlans());
        // }
    }, [defaultFilter$]);

    const handleInputChange = useCallback((val) => {

        dispatch(getUpcomingRenewals(val));
        setSearch(val);
    }, []);

    const hourswithleadingzero = useCallback((datetimeString, userTimezone) => {

        const [datePart, timePart] = datetimeString.split(' ');
        const [year, month, day] = datePart.split('-').map(Number);
        const [hour, minute, second] = timePart.split(':').map(Number);

        // Create a Date object in UTC
        const utcDate = new Date(Date.UTC(year, month - 1, day, hour, minute, second));

        // Convert the Date object to the user's timezone
        const options = { timeZone: userTimezone };
        const formattedDate = utcDate.toLocaleString('en-US', options);

        var inputDate = new Date(formattedDate);

        // Extract date components
        var years = inputDate.getFullYear();
        var months = ("0" + (inputDate.getMonth() + 1)).slice(-2);
        var days = ("0" + inputDate.getDate()).slice(-2);
        var hours = ("0" + inputDate.getHours()).slice(-2);
        var minutes = ("0" + inputDate.getMinutes()).slice(-2);
        var seconds = ("0" + inputDate.getSeconds()).slice(-2);

        // Construct the desired format string
        var outputDateString = `${years}-${months}-${days} ${hours}:${minutes}:${seconds}`;


        return outputDateString;


    })


    const demo = useCallback((trial_days, pricing2_after_cycle, order_count, trial_available, created_at, pricing_adjustment_value,
        pricing2_adjustment_value) => {
        if (trial_available == 1) {
            if (pricing2_after_cycle !== null) {
                if (order_count < pricing2_after_cycle) {
                    return pricing2_adjustment_value.toFixed(2);;
                } else {
                    return pricing_adjustment_value.toFixed(2);
                }
            } else if (trial_days !== null) {
                const createdAtDate = new Date(created_at);
                const currentDate = new Date();
                const differenceInMilliseconds = currentDate - createdAtDate;
                const differenceInDays = differenceInMilliseconds / (1000 * 3600 * 24);
                if (differenceInDays <= trial_days) {
                    return pricing2_adjustment_value.toFixed(2);;
                } else {
                    return pricing_adjustment_value.toFixed(2);
                }
            }
        } else {
            return pricing_adjustment_value.toFixed(2);;
        }
    })

    const rowmarkup =
        upcoming_renewals?.data?.length > 0 &&

        upcoming_renewals.data.map(
            (
                {
                    id,
                    member_number,
                    next_processing_date,
                    pricing_adjustment_value,
                    pricing2_adjustment_value,
                    iana_timezone,
                    failed_payment_count,
                    first_name,
                    last_name,
                    trial_available,
                    order_count,
                    name,
                    created_at,
                    trial_days,
                    currency_code,
                    pricing2_after_cycle,
                    domain,
                    currency_symbol,
                    discount_amount,
                    shopify_customer_id
                },
                index
            ) => (

                <tr
                    id={id}
                    key={id}
                    position={index}
                    className="Polaris-IndexTable__TableRow membership_table_tr"
                >
                    <IndexTable.Cell>
                        <Text variant="bodyMd" fontWeight="medium" as="span" alignment="end">
                            {member_number}
                        </Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Link onClick={() => navigate(`/members/${id}/edit?=reports`)}>{first_name + " " + last_name}</Link>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Text variant="bodyMd" fontWeight="medium" as="span">
                            {name}
                        </Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Text variant="bodyMd" fontWeight="medium" as="span" alignment="end">
                            {currency_code}  {discount_amount}
                        </Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Text variant="bodyMd" fontWeight="medium" as="span">
                            {
                                hourswithleadingzero(next_processing_date, iana_timezone)
                            }
                        </Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Text variant="bodyMd" fontWeight="medium" as="span" alignment="end">
                            {failed_payment_count}
                        </Text>
                    </IndexTable.Cell>
                </tr>
            )
        );

    const appliedFilters = [];


    const sortOptions = [
        { label: "Member Number", value: "Member_Number asc", directionLabel: "Ascending" },
        { label: "Member Number", value: "Member_Number desc", directionLabel: "Descending" },
        { label: "Customer Name", value: "Customer_Name asc", directionLabel: "Ascending", },
        { label: "Customer Name", value: "Customer_Name desc", directionLabel: "Descending", },
        { label: "Order Date", value: "Order_Date asc", directionLabel: "Ascending" },
        { label: "Order Date", value: "Order_Date desc", directionLabel: "Descending" },
        { label: "Next Billing Attempt", value: "Next_Billing_Attempt asc", directionLabel: "Ascending" },
        { label: "Next Billing Attempt", value: "Next_Billing_Attempt desc", directionLabel: "Descending" },
        { label: "Failed Orders", value: "Failed_Orders asc", directionLabel: "Ascending" },
        { label: "Failed Orders", value: "Failed_Orders desc", directionLabel: "Descending" },
    ];

    const [sortSelected, setSortSelected] = useState(["Next_Billing_Attempt asc"]);
    const [queryValue, setQueryValue] = useState("");



    const sorting = useCallback((selectedOption) => {
        let order = selectedOption[0];
        dispatch(upcomingSortReducer({ order }));
    }, [upcoming_renewals])




    const handleSortChange = useCallback((selectedOption) => {

        setSortSelected(selectedOption);


        const sliceOption = selectedOption[0].split(' ');

        const defaultFilter = {
            sk: sliceOption[0],
            sv: sliceOption[1],
        }
        dispatch(defultFilterUpdate(defaultFilter));



    }, [sortSelected, defaultFilter$]);


    // filter items
    const filetrsObj = {
        plans: undefined,
    };

    const [status, setStatus] = useState(filetrsObj);


    const handleStatusChange = useCallback(
        (value, key) => {
            setStatus({
                ...status,
                [key]: value
            });
            const defaultFilter = {
                lp: value
            }
            dispatch(defultFilterUpdate(defaultFilter))
        },
        [status]
    );


    const activePlansData =
        activePlans$.length > 0 ?
            activePlans$?.map((item) => {
                return { label: item, value: item }
            })
            :
            ''





    const filters = [
        {
            key: "plans",
            label: "Plans",
            filter: (
                <ChoiceList
                    title="Active Plans"
                    titleHidden
                    choices={[
                        ...activePlansData
                    ]}
                    selected={status.plans || []}
                    onChange={(value) =>
                        handleStatusChange(value, "plans")
                    }
                    allowMultiple
                />
            ),
            shortcut: true,
        }
    ];


    const handleQueryValueRemove = useCallback(() => { setQueryValue("") });


    // serach items onChange event
    const handleFiltersQueryChange = useCallback(
        (value) => {


            const defaultFilter = {
                s: value,
            }
            setQueryValue(value);
            dispatch(defultFilterUpdate(defaultFilter))
            // dispatch(getUpcomingRenewals(value));
        }, [queryValue, defaultFilter$]);



    const handleStatusRemove = useCallback((val, key) => {
        setStatus(filetrsObj);

    }, []);
    // clear all status filter
    const handleFiltersClearAll = useCallback(() => {
        handleStatusRemove();
        handleQueryValueRemove();
        const defaultFilter = {
            lp: " "
        };
        dispatch(defultFilterUpdate(defaultFilter));

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
            case "plans":
                return (

                    value?.map((val) => `${val}`).join(", ")
                );

            default:
                return value;
        }
    };
    if (status.plans && !isEmpty(status.plans)) {


        const key = "plans";
        appliedFilters.push({
            key,
            label: setLabel(key, status.plans),
            onRemove: handleStatusRemove,
        });



    }

    let totalPage = Math.ceil(upcoming_renewals?.total / 20);
    let index = upcoming_renewals?.current_page;

    const prevPagination = useCallback(() => {
        index = index === 0 ? 1 : index - 1;
        const defaultFilter = {
            page: index
        }
        dispatch(defultFilterUpdate(defaultFilter))
    }, [defaultFilter$, index])

    const nextPagination = useCallback(() => {
        index = index < totalPage ? index + 1 : totalPage;

        const defaultFilter = {
            page: index
        }


        dispatch(defultFilterUpdate(defaultFilter))
    }, [defaultFilter$, upcoming_renewals, index])


    const emptyStateMarkup = (
        <EmptySearchResult
            title={<Text>No Upcoming Renewals Found</Text>}
            description={'Try changing the filters or search term'}
            withIllustration
        />
    );



    return (
        <>

            <div className="simplee_membership_main_wrap">
                <div className="simplee_membership_container">
                    {/* <TextField
                        label="Search"
                        autoComplete="off"
                        value={search}
                        onChange={(val) => handleInputChange(val)}
                    /> */}
                    <Reports_Filters sortOptions={sortOptions} sortSelected={sortSelected} handleSortChange={handleSortChange} filters={filters} handleFiltersClearAll={handleFiltersClearAll} queryValue={queryValue} setQueryValue={setQueryValue} handleFiltersQueryChange={handleFiltersQueryChange} appliedFilters={appliedFilters} />
                    {/* {upcoming_renewals?.data?.length > 0 ? ( */}
                    <LegacyCard>
                        <div>
                        <IndexTable
                            resourceName={resourceName}
                            itemCount={upcoming_renewals?.data?.length > 0 ? upcoming_renewals?.data?.length > 0 : 0}
                            selectable={false}
                            emptyState={emptyStateMarkup}
                            headings={[
                                { title: "Member", alignment: 'end' },
                                { title: "Customer Name" },
                                { title: "Plan" },
                                // { title: "Store Credit", alignment: 'center' },
                                { title: "Amount", alignment: "end" },
                                { title: "Next Billing Attempt" },
                                { title: "Failed Orders", alignment: 'end' },
                            ]}
                        >
                            {rowmarkup}
                        </IndexTable>
                        <div className="memberlist_pagination">
                            <Pagination
                                hasPrevious={upcoming_renewals?.total > 20 && index > 1}
                                onPrevious={() => {
                                    prevPagination()
                                }}
                                hasNext={upcoming_renewals?.total > 20 && index < totalPage}
                                onNext={() => {
                                    nextPagination()
                                }}
                            />
                        </div>
                        </div>
                    </LegacyCard>
                    {/* ) : ( */}
                    {/* <div className="NoData">
                            <img src={Renewal} alt="No Data" />
                            <Text variant="headingMd" as="h6">
                                Any memberships due to renew soon will appear in
                                this list.
                            </Text>
                        </div> */}
                    {/* )} */}
                </div>
            </div>
        </>
    );
};
