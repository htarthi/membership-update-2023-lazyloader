import React, { useCallback } from "react";
import { Badge, Link, RadioButton, Text, TextField } from "@shopify/polaris";
import CommanLabel from "../../../GlobalPartials/CommanInputLabel/CommanLabel";
import { useSelector, useDispatch } from "react-redux";
import { updateSetting,setChanged } from "../../../../data/features/settings/settingsDataSlice";
import { info } from "sass";

export default function EmailInput() {
    const settings$ = useSelector(
        (state) => state?.settings?.data?.data?.setting,
    );
    const plan$ = useSelector(
        (state) => state?.settings?.data?.data?.plan?.active_plan_name,
    );
    const dispatch = useDispatch();
    const handleEmailInputChange = useCallback(
        (value, name) => {
            dispatch(updateSetting({ [name]: value }));
        },
        [settings$],
    );
    // radio field - verified, unverified email, domain name
    const radioHandleChange = useCallback(
        (_, newValue) => {
            dispatch(updateSetting({ mailgun_method: newValue }));
            dispatch(setChanged({['isChangeData']: true}));

        },
        [settings$],
    );

    return <>
        {/* From & Email address */}
        <div className="email_input_fields_block">
            <div className="input_two_fields_wrap">
                {/* from */}
                <TextField
                    label={<CommanLabel label={"From"} content={""} />}
                    value={settings$?.email_from_name}
                    onChange={(value) =>
                        handleEmailInputChange(value, "email_from_name")
                    }
                    autoComplete="off"
                />

                {/* Email address */}
                <TextField
                    label={
                        <CommanLabel label={"Email address"} content={""} />
                    }
                    value={settings$?.email_from_email}
                    onChange={(value) =>
                        handleEmailInputChange(value, "email_from_email")
                    }
                    autoComplete="off"
                />
            </div>
            {/* radio button */}
            <div className="email_radio_block ms-margin-top">
                <div className="radio_field">
                    <RadioButton
                        label="Use verified email"
                        helpText="Emails will be sent from your name, and replies will be sent to your email. This will ensure that most outgoing emails are received."
                        checked={settings$?.mailgun_method === "Safe"}
                        id="Safe"
                        name="email"
                        onChange={radioHandleChange}
                    />
                </div>
                <div className="radio_field">
                    {/* <RadioButton
                    label="Use unverified email"
                    helpText="We will send all emails from your email address. Many email clients could flag emails as suspicious, and some may not be received."
                    id="Basic"
                    name="email"
                    checked={settings$?.mailgun_method === "Basic"}
                    onChange={radioHandleChange}
                /> */}
                </div>
                <div className="radio_field">
                    {/* <Badge>Fulfilled</Badge> */}

                    <RadioButton
                        tone="magic"
                        disabled={plan$ !== "Enterprise Plan" && true}
                        label={
                            <div className="fulfilled_wrap">
                                <p>Use my domain name</p>{" "}
                                <Badge tone="info">
                                    Enterprise Feature
                                </Badge>
                            </div>
                        }
                        helpText={
                            <Text
                                tone="subdued"
                                variant="bodyLg"
                                as="h6"
                                fontWeight="regular"
                            >
                                Best option, requires that you update your
                                domain’s DNS settings. Learn more about
                                enabling this option in the{" "}
                                <Link
                                    url="https://support.simplee.best/en/articles/5458606-send-emails-from-a-custom-domain"
                                    external
                                >
                                    help centre
                                </Link>
                            </Text>
                        }
                        id="Advanced"
                        name="email"
                        checked={settings$?.mailgun_method === "Advanced"}
                        onChange={radioHandleChange}
                    />
                </div>
            </div>
        </div>
    </>;
}
