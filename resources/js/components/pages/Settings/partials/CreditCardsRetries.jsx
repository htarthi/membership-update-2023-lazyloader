import React, { useState, useCallback, useEffect } from "react";
import { Checkbox, Text, TextField, Select } from "@shopify/polaris";
import CommanLabel from "../../../GlobalPartials/CommanInputLabel/CommanLabel";
import { useDispatch, useSelector } from "react-redux";
import { updateSetting , setChanged } from "../../../../data/features/settings/settingsDataSlice";

export default function CreditCardsRetries() {

    const settings$ = useSelector((state) => state?.settings?.data?.data?.setting);
    const dispatch = useDispatch();
    const credit_card_retries = [
        { label: "0 retries", value: 0 },
        { label: "1 retries", value: 1 },
        { label: "2 retries", value: 2 },
        { label: "3 retries", value: 3 },
        { label: "4 retries", value: 4 },
        { label: "5 retries", value: 5 },
        { label: "6 retries", value: 6 },
        { label: "7 retries", value: 7 },
        { label: "8 retries", value: 8 },
        { label: "9 retries", value: 9 },
        { label: "10 retries", value: 10 },
        { label: "11 retries", value: 11 },
        { label: "12 retries", value: 12 },
        { label: "13 retries", value: 13 },
        { label: "14 retries", value: 14 },
        { label: "15 retries", value: 15 },
    ];

    const credit_card_retries_time = [
        { label: "Every day", value: 0 },
        { label: "Every 2 days", value: 2 },
        { label: "Every 3 days", value: 3 },
        { label: "Every 4 days", value: 4 },
        { label: "Every 5 days", value: 5 },
        { label: "Every 6 days", value: 6 },
        { label: "Every 7 days", value: 7 },
    ]

    const options2 = [
        { label: 'Cancel membership', value: 'cancel' },
    ];

    const handlechange = useCallback((name, value) => {
        var x = Number(value)
        dispatch(updateSetting({ [name]: x }));
        dispatch(setChanged({['isChangeData']: true}));
    }, [settings$])

    return (
        <>
            {/* Notifications */}
            <Text variant="bodyLg" as="h6" fontWeight="regular" >Choose how often to retry processing orders which have failed</Text>

            <div className="customer_accounts_blocks">
                {/* notifications checkboxes */}
                <div className="notifications_checkboxes ms-margin-top">
                    <div className="notification_checkboxes_fields  ms-margin-top">
                        <div className="checkbox_block">
                            <Select
                                label={
                                    <CommanLabel
                                        label={"How many times will we retry a credit card"}
                                        content={''}
                                    />
                                }
                                options={credit_card_retries}
                                value={settings$.dunning_retries}
                                onChange={(e) => handlechange("dunning_retries", e)}
                            />
                        </div>
                        <div className="checkbox_block">
                            <Select
                                label={<CommanLabel label={"How often will retry a credit card ?"} content={''} />}
                                value={settings$.dunning_daysbetween}
                                options={credit_card_retries_time}
                                onChange={(e) => handlechange("dunning_daysbetween", e)}

                            />
                        </div>
                        {/* <div className="checkbox_block">
                            <Select
                                label={<CommanLabel label={"What should happen when we reach the maximum retries ?"} content={''} />}
                                onChange={(e) => handlechange("dunning_failedaction", e)}
                                options={options2}
                                value={settings$.dunning_failedaction}
                            />
                        </div> */}
                    </div>
                </div>
            </div>
        </>
    )
}
