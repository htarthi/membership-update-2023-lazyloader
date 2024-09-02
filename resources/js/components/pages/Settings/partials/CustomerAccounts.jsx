import React, {useCallback} from "react";
import { Checkbox, Text } from "@shopify/polaris";
import CommanLabel from "../../../GlobalPartials/CommanInputLabel/CommanLabel";
import { useDispatch, useSelector } from "react-redux";
import { updateSetting , setChanged } from "../../../../data/features/settings/settingsDataSlice";

export default function CustomerAccounts() {

    const settings$ = useSelector((state) => state?.settings?.data?.data?.setting);
    const dispatch = useDispatch();
    // input & checkbox onchange method
    const handleNotificationInputChange = useCallback((value, name) => {
        console.log(value);
        dispatch(updateSetting({[name]: value}))
        dispatch(setChanged({['isChangeData']: true}))
    }, [settings$])

  return (
    <>
        {/* Notifications */}
        <div className="customer_accounts_blocks">
            {/* notifications checkboxes */}
            <Text variant="bodyLg" as="h6" fontWeight="regular" >If using Classic Accounts, this will automatically send an email to new customers asking them to create their account </Text>
            <div className="notifications_checkboxes ms-margin-top">
                <div className="notification_checkboxes_fields  ms-margin-top">
                    <div className="checkbox_block">
                        <Checkbox
                            label={<CommanLabel label={"Send account invites to new customers"} content={''} />}
                            checked={settings$?.send_account_invites}
                            onChange={(val) =>
                                handleNotificationInputChange(
                                    val,
                                    "send_account_invites"
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
