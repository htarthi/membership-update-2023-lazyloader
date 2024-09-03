import React from "react";
import {
    IndexFilters,
    useSetIndexFiltersMode,
    ChoiceList,
} from "@shopify/polaris";
import { useState, useCallback, useEffect } from "react";
import { useSelector } from "react-redux";
import { useDispatch } from "react-redux";
import { defultFilterUpdate } from "../../../../data/features/members/membersSlice";

export default function Filters({filterSorting}) {

    const defaultFilter$ = useSelector((state) => state?.members?.defaultFilter);
    const activePlans$ = useSelector((state) => state?.members?.data?.activePlans);
    const dispatch = useDispatch();
    const appliedFilters = [];

    // all items of table
    const [itemStrings, setItemStrings] = useState([
        "All",
        "Active",
        "Expiring",
        "Cancelled",
        "Failed",
    ]);


    // dropdown tabs of items
    const tabs = itemStrings?.map((item, index) => ({
        content: item,
        index,
        onAction: (e) => {},
        id: `${item}-${index}`,
        isLocked: index > 0,
    }));

    const [selected, setSelected] = useState(0);

    // sort options
    const sortOptions = [
        { label: "Customer", value: "customer asc", directionLabel: "Ascending" },
        { label: "Customer", value: "customer desc", directionLabel: "Descending" },
        { label: "Next Billing Date", value: "next_billing_date asc", directionLabel: "Ascending", },
        { label: "Next Billing Date", value: "next_billing_date desc", directionLabel: "Descending", },
        { label: "Member Number", value: "member_number asc", directionLabel: "Ascending", },
        { label: "Member Number", value: "member_number desc", directionLabel: "Descending", },
    ];

    const [sortSelected, setSortSelected] = useState(["member_number desc"]);

    const handleSortChange = useCallback((selectedOption) => {

        const sliceOption = selectedOption[0].split(' ');

        const defaultFilter = {
            sk: sliceOption[0],
            sv: sliceOption[1],
        }
        dispatch(defultFilterUpdate(defaultFilter));

        setSortSelected(selectedOption);
        filterSorting(selectedOption);
    }, [sortSelected, defaultFilter$]);

    const { mode, setMode } = useSetIndexFiltersMode();
    const onHandleCancel = () => {
        const defaultFilter = {
            s: '',
        }
        setQueryValue('');
        dispatch(defultFilterUpdate(defaultFilter));
    };


    // filter items
    const filetrsObj = {
        plans: undefined,
        last_payment: undefined,
    };

    const [status, setStatus] = useState(filetrsObj);

    // onChange event of status filter
    const handleStatusChange = useCallback(
        (value, key) => {
            setStatus({
                ...status,
                [key]: value
            });
        },
        [status]
    );

    useEffect(() => {
        const defaultFilter = {
            f: itemStrings[selected] === 'All' ? 'all' : itemStrings[selected] === 'Active' ? 'active' : itemStrings[selected] === 'Expiring' ? 'expired' : itemStrings[selected] === 'Cancelled' ? 'cancelled' : 'failed',
            p: status?.plans !== undefined ? status?.plans?.toString() : '',
            lp: status?.last_payment !== undefined ? status?.last_payment?.toString() : '',
        }
        dispatch(defultFilterUpdate(defaultFilter));
    }, [status, selected])


    // onRemove event of status filter
    const handleStatusRemove = useCallback((val, key) => {
        setStatus(filetrsObj);
    }, []);

    const [queryValue, setQueryValue] = useState("");

    // serach items onChange event
    const handleFiltersQueryChange = useCallback(
        (value) => {
            const defaultFilter = {
                s: value,
            }
            setQueryValue(value);
            dispatch(defultFilterUpdate(defaultFilter))
    }, [queryValue, defaultFilter$]);

    // serach items onRemove event
    const handleQueryValueRemove = useCallback(() => setQueryValue(""), []);

    // clear all status filter
    const handleFiltersClearAll = useCallback(() => {
        handleStatusRemove();
        handleQueryValueRemove();
    }, [handleStatusRemove, handleQueryValueRemove]);


    const activePlansData =
    activePlans$.length > 0 ?
    activePlans$?.map((item) => {
        return  {label: item, value: item}
    })
    :
    ''

    // status filter array
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
        },
        {
            key: "last_payment",
            label: "Last Payment",
            filter: (
                <ChoiceList
                    title="Last Payment"
                    titleHidden
                    choices={[
                        { label: "Succesful", value: "successful" },
                        // { label: "Pending", value: "sent" },
                        { label: "Failed", value: "failed" },
                    ]}
                    selected={status.last_payment || []}
                    onChange={(value) =>
                        handleStatusChange(value, "last_payment")
                    }
                    allowMultiple
                />
            ),
            shortcut: true,
        },
    ];

    // set filters label
    const setLabel = (key, value) => {
        switch (key) {
            case "plans":
                return (
                    "Payment Status: " +
                    value?.map((val) => `${val}`).join(", ")
                );
            case "last_payment":
                return (
                    "Last Payment: " + value?.map((val) => `${val}`).join(", ")
                );
            default:
                return value;
        }
    };

    const isEmpty = (value) => {
        if (Array.isArray(value)) {
            return value.length === 0;
        } else {
            return value === "" || value == null;
        }
    };

    //add applied filetrs data
    if (status.plans && !isEmpty(status.plans)) {
        const key = "plans";
        appliedFilters.push({
            key,
            label: setLabel(key, status.plans),
            onRemove: handleStatusRemove,
        });
    }
    if (status.last_payment && !isEmpty(status.last_payment)) {
        const key = "last_payment";
        appliedFilters.push({
            key,
            label: setLabel(key, status.last_payment),
            onRemove: handleStatusRemove,
        });
    }

    return (
        <>
            <IndexFilters
                sortOptions={sortOptions}
                sortSelected={sortSelected}
                queryValue={queryValue}
                queryPlaceholder="Search members by name, number, email, or contract ID"
                onQueryChange={handleFiltersQueryChange}
                onQueryClear={() => handleFiltersQueryChange("")}
                onSort={handleSortChange}
                cancelAction={{
                    onAction: onHandleCancel,
                    disabled: false,
                    loading: false,
                }}
                tabs={tabs}
                selected={selected}
                onSelect={setSelected}
                // canCreateNewView
                // onCreateNewView={onCreateNewView}
                filters={filters}
                appliedFilters={appliedFilters}
                onClearAll={handleFiltersClearAll}
                mode={mode}
                setMode={setMode}
            />
        </>
    );
}
