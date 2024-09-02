import React, { useState, useCallback, useEffect, IndexTable } from "react";
import { Checkbox, Text, TextField, Button, Tooltip, Icon } from "@shopify/polaris";
import CommanLabel from "../../../GlobalPartials/CommanInputLabel/CommanLabel";
import { useDispatch, useSelector } from "react-redux";
import { updateSetting , setChanged } from "../../../../data/features/settings/settingsDataSlice";
import { addNewReason } from "../../../../data/features/settings/settingAction";
import { DeleteIcon } from '@shopify/polaris-icons';

export default function CancellationReasons() {

    const settings$ = useSelector((state) => state?.settings?.data?.data?.setting);
    const dispatch = useDispatch();
    const [customReason, setCustomeReasons] = useState();
    const [getReasons, setReasons] = useState(settings$?.reasons);
    const [getDeleteReason, setDeleteReason] = useState([]);
    const [getCustom, setshowCustom] = useState(false);

    const custom_message = 'We’re sorry to see you go.  You will keep access to your membership until the remainder of your current paid period expires.  Please let us know the reason for your cancellation.';


    useEffect(() => {
        setReasons(settings$.reasons);
    }, [settings$]);

    const handleNotificationInputChange = useCallback((value, name) => {
        if (name == 'new_reason') {
            setCustomeReasons(value);
        }
        dispatch(updateSetting({ [name]: value }));
        dispatch(setChanged({['isChangeData']: true}));
    }, [settings$]);

    const handleCheckboxChange = (newChecked, is_enabled) => {
        const updatedReasons = getReasons.map((checkbox) =>
            checkbox.id === is_enabled ? { ...checkbox, is_enabled: newChecked ? 1 : 0 } : checkbox
        );
        setReasons(updatedReasons);
        dispatch(updateSetting({ ['reasons']: updatedReasons }));
        dispatch(setChanged({['isChangeData']: true}));

    };

    const deleteReason1 = (id) => {
        const updatedReasons = getReasons.filter(checkbox => checkbox.id !== id);
        setReasons(updatedReasons);
        dispatch(updateSetting({ ['reasons']: updatedReasons }));
        dispatch(setChanged({['isChangeData']: true}));

        const deletR = [...getDeleteReason, id];
        setDeleteReason(deletR);
        dispatch(updateSetting({ ['deleteReasons']: deletR }));
        dispatch(setChanged({['isChangeData']: true}));
    };

    const addReason = () => {
        if (customReason) { dispatch(addNewReason(customReason)); }
    };

    const addReasonOpen = () => {
        setshowCustom(true)
    };

    const removeReason = () => {
        setshowCustom(false)
    };


    return (
        
        <div className="customer_accounts_blocks">
            <Text variant="bodyLg" as="h6" fontWeight="regular" >Ask members to provide the reason that they are cancelling their membership</Text>
            <div className="checkbox_block ms-margin-top">

                <Checkbox
                    label={<CommanLabel label={"Enable cancellation reasons"} content={''} />}
                    checked={settings$?.cancellation_reason_enable}
                    onChange={(val) =>
                        handleNotificationInputChange(
                            val,
                            "cancellation_reason_enable"
                        )
                    }
                />
                <Text variant="bodyLg" as="h6" fontWeight="regular" >Cancellation reasons enable members to let you know why they’re cancelling their membership. The answers can be seen on the individual membership, and the Reports page.</Text>
            </div>
            {
                settings$?.cancellation_reason_enable ?
                    <>
                        <div className="ms-margin-top" >
                            <div className="sent_notification_input_field">
                                <Checkbox
                                    label={<CommanLabel label={"Customers are required to choose a reason"} content={''} />}
                                    checked={settings$?.required_reason}
                                    onChange={(val) =>
                                        handleNotificationInputChange(
                                            val,
                                            "required_reason"
                                        )
                                    }
                                />
                            </div>
                            <div className="sent_notification_input_field" style={{ marginTop : '20px'}}>
                                <TextField
                                    label={<CommanLabel label={"Custom message : "} content={''} />}
                                    value={settings$?.custom_reason_message ? settings$?.custom_reason_message : custom_message}
                                    onChange={(value) =>
                                        handleNotificationInputChange(
                                            value,
                                            "custom_reason_message"
                                        )
                                    }
                                    multiline={4}
                                    autoComplete="off"
                                />
                            </div>

                            <div className="ms-margin-top">
                                <Button  loading={false} size='medium' onClick={addReasonOpen} variant="primary" >Add a reason</Button>
                            </div>
                            {
                                getCustom ?
                                <div className="flex-container" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                    <div className="flex-item" style={{ flex: '11', marginRight: '20px' }}>
                                        <TextField
                                        label={<CommanLabel label={""} content={''} />}
                                        value={settings$?.new_reason}
                                        onChange={(value) => handleNotificationInputChange(value, "new_reason")}
                                        maxLength={255}
                                        placeholder="Enter the reason which will be displayed to members"
                                        autoComplete="off"
                                        />
                                    </div>
                                    <div className="flex-item" style={{ flex: '1', display: 'flex', justifyContent: 'flex-end', alignItems: 'center', gap: '10px' , marginTop : '20px' }}>
                                        <Button loading={false} size='medium' onClick={removeReason} >
                                            <Icon source={DeleteIcon} color="base" size="large"/>
                                        </Button>
                                        <Button  loading={false} size='medium' onClick={addReason} variant="primary">
                                            Add
                                        </Button>
                                    </div>
                                    </div>

                                    : ''
                            }
                            {getReasons?.map((checkbox, index) => (
                                <div className="flex-container" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                    <div className="flex-item" style={{ flex: '1', marginRight: '20px' }}>
                                        <Checkbox
                                            label={
                                                <CommanLabel
                                                    label={checkbox.reason}
                                                    content={checkbox.content}
                                                />
                                            }
                                            id={checkbox.id}
                                            name={checkbox.reason}
                                            checked={checkbox.is_enabled === 1}
                                            onChange={(newChecked) => handleCheckboxChange(newChecked, checkbox.id)}
                                        />

                                    </div>
                                    <div className="flex-item" style={{ marginTop: '17px' }}>
                                    <Button
                                            onClick={(e) => deleteReason1(checkbox.id)}

                                        > <Icon source={DeleteIcon} color="base" size="large"/></Button>

                                    </div>
                                </div>
                            ))}
                        </div>
                        <div className="" style={{marginTop : '30px'}}>
                            <Checkbox
                                label={<CommanLabel label={"Allow customers to enter their own custom response "} content={''} />}
                                checked={settings$?.cancellation_reason_enable_custom}
                                onChange={(val) =>
                                    handleNotificationInputChange(
                                        val,
                                        "cancellation_reason_enable_custom"
                                    )
                                }
                            />
                            {
                                settings$?.cancellation_reason_enable_custom ?
                                    // <div className="flex-container" style={{ justifyContent: 'space-between', alignItems: 'center' , marginTop : '15px' }}>
                                    //     <div className="flex-item" style={{  marginRight: '20px' }}>
                                    <div className="sent_notification_input_field" style={{ justifyContent: 'space-between', alignItems: 'center' , marginTop : '15px' }}>
                                            <TextField
                                                label={<CommanLabel label={"Name for this option :"} content={''} />}
                                                value={settings$?.custom_options}
                                                onChange={(value) =>
                                                    handleNotificationInputChange(value, "custom_options")
                                                }
                                                maxLength={255}
                                                placeholder="Name for this option"
                                                autoComplete="off"
                                            />
                                    </div>
                                : ''
                            }
                        </div>
                        <div className="" style={{ marginTop: '30px' }}>
                            <div className="flex-container" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: '15px', gap: '20px' }}>
                                <div className="flex-item" style={{ flex: '1' }}>
                                <TextField
                                    label={<CommanLabel label={"Name for Submit Button :"} content={''} />}
                                    value={settings$?.custom_submit}
                                    onChange={(value) => handleNotificationInputChange(value, "custom_submit")}
                                    maxLength={255}
                                    placeholder="Name for Submit Button"
                                    autoComplete="off"
                                />
                                </div>
                                <div className="flex-item" style={{ flex: '1' }}>
                                <TextField
                                    label={<CommanLabel label={"Name for Cancel Button  :"} content={''} />}
                                    value={settings$?.custom_cancel}
                                    onChange={(value) => handleNotificationInputChange(value, "custom_cancel")}
                                    maxLength={255}
                                    placeholder="Name for Cancel Button"
                                    autoComplete="off"
                                />
                                </div>
                            </div>
                        </div>


                    </>
                    : ''
            }
        </div>
    );
}


