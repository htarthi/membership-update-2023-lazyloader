import React from "react";
import {
    IndexFilters,
    useSetIndexFiltersMode,
    ChoiceList,
    IndexFiltersMode,
} from "@shopify/polaris";
import { useState, useCallback, useEffect } from "react";
import { useSelector } from "react-redux";
import { useDispatch } from "react-redux";
import { defultFilterUpdate } from "../../../../data/features/members/membersSlice";

export const Reports_Filters = ({ sortOptions, sortSelected, handleSortChange, filters, filterSorting, handleFiltersClearAll, queryValue, setQueryValue, handleFiltersQueryChange, appliedFilters}) => {

    const activePlans$ = useSelector((state) => state?.members?.data?.activePlans);


    const dispatch = useDispatch();

    // all items of table
    const [itemStrings, setItemStrings] = useState([

    ]);


    // dropdown tabs of items
    const tabs = itemStrings?.map((item, index) => ({
        content: item,
        index,
        onAction: (e) => { },
        id: `${item}-${index}`,
        isLocked: index > 0,
    }));

    const [selected, setSelected] = useState(0);

    const onHandleCancel = () => { };


    const {mode, setMode} = useSetIndexFiltersMode();



    const activePlansData =
        activePlans$.length > 0 ?
            activePlans$?.map((item) => {
                return { label: item, value: item }
            })
            :
            ''

    return (
        <>
            <IndexFilters
                sortOptions={sortOptions}
                sortSelected={sortSelected}
                queryValue={queryValue}
                queryPlaceholder="Searching in Memberships"
                onQueryChange={handleFiltersQueryChange}
                onQueryClear={()=>handleFiltersQueryChange("")}
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
