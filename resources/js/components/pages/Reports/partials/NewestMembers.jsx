import { Text, IndexTable, LegacyCard, EmptySearchResult, Pagination, Link } from '@shopify/polaris'
import React, { useState, useEffect, useCallback } from 'react'
import { useSelector } from 'react-redux';
import { useDispatch } from 'react-redux';
import { newestMembers } from '../../../../data/features/reports/reportAction';
import { defultFilterUpdate } from '../../../../data/features/reports/reportSlice';
import { Reports_Filters } from './Reports_Filters';
import { useNavigate } from "react-router-dom";

export const NewestMembers = () => {

    const dispatch = useDispatch();
    const newest_members = useSelector(
        (state) => state?.reports?.data?.newest_members
    );

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
    const navigate = useNavigate();
    const defaultFilter$ = useSelector((state) => state?.reports?.defaultFilter);
    const shop$ = useSelector((state) => state.plans?.data?.shop);

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
        dispatch(newestMembers(queryString))
    }, [defaultFilter$]);

    const rowmarkup =
        newest_members?.data?.length > 0 &&
        newest_members.data.map(
            (
                {
                    id,
                    member_number,
                    first_name,
                    last_name,
                    order_amount,
                    plan_name,
                    created_at,
                    iana_timezone,
                    next_order_date
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
                        <Text variant="bodyMd" fontWeight="medium" as="span" alignment='end'>
                            {member_number}
                        </Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Link onClick={() => navigate(`/members/${id}/edit?=reports`)}>{first_name + " " + last_name}</Link>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Text variant="bodyMd" fontWeight="medium" as="span" >
                            {plan_name}
                        </Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Text variant="bodyMd" fontWeight="medium" as="span" alignment='end'>
                            {shop$.currency} {order_amount}
                        </Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Text variant="bodyMd" fontWeight="medium" as="span">
                            {
                                month[new Date(created_at).getMonth()] +
                                " " +
                                new Date(created_at).getDate() +
                                ", " +
                                new Date(created_at).getFullYear()
                            }
                        </Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Text variant="bodyMd" fontWeight="medium" as="span">
                        {
                                hourswithleadingzero(next_order_date, iana_timezone)
                            }
                        </Text>
                    </IndexTable.Cell>
                </tr>
            )
        );

    const sortOptions = [
        { label: "Member Number", value: "Member_Number asc", directionLabel: "Ascending" },
        { label: "Member Number", value: "Member_Number desc", directionLabel: "Descending" },
        { label: "Customer Name", value: "Customer_Name asc", directionLabel: "Ascending" },
        { label: "Customer Name", value: "Customer_Name desc", directionLabel: "Descending" },
        { label: "Start Date", value: "created_at asc", directionLabel: "Ascending" },
        { label: "Start Date", value: "created_at desc", directionLabel: "Descending" },
        { label: "Next Billing Date", value: "next_order_date asc", directionLabel: "Ascending" },
        { label: "Next Billing Date", value: "next_order_date desc", directionLabel: "Descending" },

    ];

    const [sortSelected, setSortSelected] = useState(["Member_Number desc"]);
    const [queryValue, setQueryValue] = useState("");

    const appliedFilters = [];

    const handleSortChange = useCallback((selectedOption) => {
        setSortSelected(selectedOption);
        const sliceOption = selectedOption[0].split(' ');
        const defaultFilter = {
            sk: sliceOption[0],
            sv: sliceOption[1],
        }
        dispatch(defultFilterUpdate(defaultFilter));
    }, [sortSelected, defaultFilter$]);

    const filters = [

    ];

    const handleQueryValueRemove = useCallback(() => { setQueryValue("") });
    const handleFiltersQueryChange = useCallback(
    (value) => {
        const defaultFilter = {
            s: value,
        }
        setQueryValue(value);
        dispatch(defultFilterUpdate(defaultFilter))
    }, [queryValue, defaultFilter$]);

    const handleStatusRemove = useCallback((val, key) => {
        const defaultFilter = {
            lp: "",
            em: ""
        };
        dispatch(defultFilterUpdate(defaultFilter))

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

    let totalPage = Math.ceil(newest_members?.total / 20);
    let index = newest_members?.current_page;

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
    }, [defaultFilter$, newest_members, index])

    const emptyStateMarkup = (
        <EmptySearchResult
            title={<Text>No Newest Members Found</Text>}
            description={'Try changing the filters or search term'}
            withIllustration
        />
    );

    return (
        <>
            <div className="simplee_membership_main_wrap">
                <div className="simplee_membership_container" >
                    <Reports_Filters sortOptions={sortOptions} sortSelected={sortSelected} handleSortChange={handleSortChange} filters={filters} handleFiltersClearAll={handleFiltersClearAll} queryValue={queryValue} setQueryValue={setQueryValue} handleFiltersQueryChange={handleFiltersQueryChange} appliedFilters={appliedFilters} />
                    <LegacyCard>
                        <IndexTable
                            resourceName={resourceName}
                            itemCount={newest_members?.data?.length > 0 ? newest_members?.data?.length : 0}
                            selectable={false}
                            emptyState={emptyStateMarkup}
                            headings={[
                                { title: "Member", alignment: 'end' },
                                { title: "Customer Name" },
                                { title: "Plan" },
                                { title: "Amount", alignment: 'end' },
                                { title: "Start Date" },
                                { title: "Next Billing Attempt" },
                            ]}
                        >
                            {rowmarkup}
                        </IndexTable>

                        <div className="memberlist_pagination">
                            <Pagination
                                hasPrevious={newest_members?.total > 20 && index > 1}
                                onPrevious={() => {
                                    prevPagination()
                                }}
                                hasNext={newest_members?.total > 20 && index < totalPage}
                                onNext={() => {
                                    nextPagination()
                                }}
                            />
                        </div>
                    </LegacyCard>

                </div>
            </div>
        </>
    )
}
