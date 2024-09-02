import React, {useState, useCallback} from "react";
import { Checkbox, Text, TextField } from "@shopify/polaris";
import CommanLabel from "../../../GlobalPartials/CommanInputLabel/CommanLabel";
import { useDispatch, useSelector } from "react-redux";
import { updateSetting , setChanged } from "../../../../data/features/settings/settingsDataSlice";

export default function CustomerNotifications() {

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
        <div className="notification_blocks">
        <Text variant="bodyLg" as="h6" fontWeight="regular" >These are notifications sent to a specific email address with information about important member events</Text><br></br>

            {/* Email address where notifications will be sent */}
            <div className="sent_notification_input_field">
                <TextField
                    label={ <CommanLabel label={"Email address where notifications will be sent"} content={''} /> }
                    value={settings$?.notify_email}
                    onChange={(value) =>
                        handleNotificationInputChange(
                            value,
                            "notify_email"
                        )
                    }
                    autoComplete="off"
                />
            </div>

            {/* notifications checkboxes */}
            <div className="notifications_checkboxes ms-margin-top">

                <div className="notification_checkboxes_fields  ms-margin-top">
                    <div className="checkbox_block">
                        <Checkbox
                            label={<CommanLabel label={"When a new membership is created"} content={''} />}
                            checked={settings$?.notify_new}
                            onChange={(val) =>
                                handleNotificationInputChange(
                                    val,
                                    "notify_new"
                                )
                            }
                        />
                    </div>
                    <div className="checkbox_block">
                        <Checkbox
                            label={<CommanLabel label={"When a member cancels their next renewal"} content={''} />}
                            checked={settings$?.notify_cancel}
                            onChange={(val) =>
                                handleNotificationInputChange(
                                    val,
                                    "notify_cancel"
                                )
                            }
                        />
                    </div>
                    <div className="checkbox_block">
                        <Checkbox
                            label={<CommanLabel label={"When a member’s access is revoked"} content={''} />}
                            checked={settings$?.notify_revoke}
                            onChange={(val) =>
                                handleNotificationInputChange(
                                    val,
                                    "notify_revoke"
                                )
                            }
                        />
                    </div>
                    <div className="checkbox_block">
                        <Checkbox
                            label={<CommanLabel label={"When a member’s renewal payment fails"} content={''} />}
                            checked={settings$?.notify_paymentfailed}
                            onChange={(val) =>
                                handleNotificationInputChange(
                                    val,
                                    "notify_paymentfailed"
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
