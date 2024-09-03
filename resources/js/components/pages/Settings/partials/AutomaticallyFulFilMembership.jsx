import React, {useState, useCallback} from "react";
import { Checkbox, Text, TextField } from "@shopify/polaris";
import CommanLabel from "../../../GlobalPartials/CommanInputLabel/CommanLabel";
import { useDispatch, useSelector } from "react-redux";
import { setChanged, updateSetting } from "../../../../data/features/settings/settingsDataSlice";

export default function AutomaticallyFulFilMembership() {

    const settings$ = useSelector((state) => state?.settings?.data?.data?.setting);
    const dispatch = useDispatch();

    // input & checkbox onchange method
    const handleNotificationInputChange = useCallback((value, name) => {
        dispatch(updateSetting({[name]: value}));
        dispatch(setChanged({['isChangeData']: true}));
    }, [settings$])

  return (
    <>
        {/* Notifications */}
        <div className="customer_accounts_blocks">
        {/* <Text variant="bodyLg" as="h6" fontWeight="regular" >Most membership products don’t need to be shipped. This setting determines whether membership products will be fulfilled automatically</Text> */}
            {/* notifications checkboxes */}
            <div className="notifications_checkboxes">
                <div className="notification_checkboxes_fields ">
                    <div className="checkbox_block">
                        <Checkbox
                            label={<CommanLabel label={"Automatically fulfill membership products"} content={''} />}
                            checked={settings$?.auto_fulfill}
                            onChange={(val) =>
                                handleNotificationInputChange(
                                    val,
                                    "auto_fulfill"
                                )
                            }
                        />
                    </div>
                </div>
            </div>

        </div>
    </>
  )
}
