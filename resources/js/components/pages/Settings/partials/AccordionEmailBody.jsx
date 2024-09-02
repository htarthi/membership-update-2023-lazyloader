import React, { useCallback, useState, useEffect } from "react";
import { Button, Checkbox , Text } from "@shopify/polaris";
import EmailInput from "./EmailInput";
import CommanLabel from "../../../GlobalPartials/CommanInputLabel/CommanLabel";
import { useSelector } from "react-redux";
import { useDispatch } from "react-redux";
import { updateSetting , setChanged } from "../../../../data/features/settings/settingsDataSlice";
import { EmailModel } from "./EmailModel";

export default function AccordionEmailBody() {

    const [Active,setActive] = useState(false)
    const [Val, setVal] = useState([]);

    const settings$ = useSelector((state) => state?.settings?.data?.data?.setting);
    const dispatch = useDispatch();

    // checkbox - email
    const initialCheckboxes = [
        {
            id: 1,
            key: 'new_subscription_email_enabled',
            checked: settings$?.new_subscription_email_enabled === 1 ? true : false,
            label: "Email customers when they purchase a new membership",
            content: "This order has shipping labels.",
            data :  "newSubNoti",
            category:"new_membership_to_customer",
            title:"New Membership"

        },
        {
            id: 2,
            key: 'dunning_email_enabled',
            checked: settings$?.dunning_email_enabled === 1 ? true : false,
            label: "Email customers when their credit card fails and prompt them to update their information.",
            content: "This order has shipping labels.",
            data : "failedPaymentNoti",
            category:"failed_payment_to_customer",
            title:"Failed Payment Notifications"
        },
        {
            id: 3,
            key: 'membership_cancel_email_enabled',
            checked: settings$?.membership_cancel_email_enabled === 1 ? true : false,
            label: "Email customers when they cancel their membership",
            content: "This order has shipping labels.",
            data:"cancelMembershipNoti",
            category:"cancelled_membership",
            title:"Cancel Membership Notifications"

        },
        {
            id: 4,
            key: 'recurring_notify_email_enabled',
            checked: settings$?.recurring_notify_email_enabled === 1 ? true : false,
            label: "Email members before their next renewal",
            content: "This order has shipping labels.",
            data : 'recurringNotifyEmailNoti',
            category:"recurring_notify",
            title:"Recurring Membership Notifications"


        },
    ];

    const handleemail = useCallback((checkbox) => {
        setVal(checkbox)
        setActive(!Active)
    }, [Active])


    const handleCheckboxChange = useCallback((newChecked, key) => {
        dispatch(updateSetting({[key]: newChecked ? 1 : 0}))
        dispatch(setChanged({['isChangeData']: true}));
    }, [settings$]);

    return <>
        {/* edit email templates checkboxes */}
        <Text variant="bodyLg" as="h6" fontWeight="regular" >These emails are sent to new and existing members when important events occur with their membership</Text>

        <div className="emails_collpsible ms-margin-top">
            {initialCheckboxes?.map((checkbox, index) => (
                <div
                    className="emails_templates_checkbox"
                    key={index}
                >
                    <div className="checkbox_block">
                        <Checkbox
                            label={
                                <CommanLabel
                                    label={checkbox?.label}
                                    // content={checkbox?.content}
                                />
                            }
                            checked={settings$?.[checkbox?.key]}
                            onChange={(newChecked) =>
                                handleCheckboxChange(newChecked, checkbox?.key)
                            }
                        />
                    </div>

                    <Button  onClick={(e)=>handleemail(checkbox)} variant="plain">Edit email template</Button>
                </div>
            ))}
        </div>

        {/* from, Email address, Title Background, Title Border & Title Font Color */}

        <EmailModel active={Active}  setActive={setActive}  value={Val} />
        <EmailInput />
    </>;
}
