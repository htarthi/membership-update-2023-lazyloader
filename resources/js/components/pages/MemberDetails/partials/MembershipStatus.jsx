import { Badge, LegacyCard, Select, Text } from "@shopify/polaris";
import React, { useCallback, useState, useEffect } from "react";
import EditComp from "./EditComp";
import { useDispatch, useSelector } from "react-redux";
import { contractReducer } from "../../../../data/features/memberDetails/memberDetailsSlice";

export default function MembershipStatus() {
    // const dispatch = useDispatch();
    const status$ = useSelector((state) => state.memberDetails?.data?.contract);

    // status edit
    const [editStatus, setEditStatus] = useState(false);
    // const editHandleEvent = () => {
    //     setEditStatus(true);
    // }

    // selected status
    const [selectedStatus, setSelectedStatus] = useState(status$?.status);

    useEffect(() => {
        setSelectedStatus(status$?.status);
    }, [status$]);

    // select handle change
    // const handleSelectChange = useCallback((value) => {
    //     setSelectedStatus(value);
    //     dispatch(contractReducer({status: value}))
    //     setEditStatus(false);
    // },[selectedStatus, status$]);

    // select options
    // const options = [
    //   {label: 'Active', value: 'active'},
    //   {label: 'Expiring', value: 'expiring'},
    //   {label: 'Cancelled', value: 'cancelled'},
    //   {label: 'Created Manually', value: 'Created Manually'},
    // ];

    return (
        <div className="membership_status_block main_box_wrap">
            <LegacyCard>
                {/* Heading & Edit */}
                {/* <EditComp title={"Status"} editHandleEvent={editHandleEvent} /> */}
                <div className="edit_header_block">
                    <Text variant="headingMd" as="h6" fontWeight='medium'>
                        Status
                    </Text>
                </div>

                {!editStatus && (
                    ((<div className="status_badge_wrap ms-margin-top">
                    {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                    {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                    {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                    <Badge
                        tone={
                            selectedStatus === "active"
                                ? "success"
                                : selectedStatus === "expiring"
                                  ? "attention"
                                  : selectedStatus === "cancelled"
                                    ? "critical"
                                    : selectedStatus === "Created Manually"
                                      ? "info"
                                      : ""
                        }
                        progress={
                            selectedStatus === "active"
                                ? "complete"
                                : selectedStatus === "expiring"
                                  ? "partiallyComplete"
                                  : selectedStatus === "cancelled"
                                    ? "incomplete"
                                    : ""
                        }
                    >
                        {selectedStatus}
                    </Badge>
                </div>))
                )}

                {/* {
                  editStatus &&
                  <div className='select_status_wrap ms-margin-top'>
                      <Select
                      options={options}
                      onChange={handleSelectChange}
                      value={selectedStatus}
                      onBlur={(e) => handleSelectChange(e.target.value)}
                      />
                  </div>
              } */}
            </LegacyCard>
        </div>
    );
}
