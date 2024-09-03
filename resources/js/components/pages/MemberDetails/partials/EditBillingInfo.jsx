import { Card, DatePicker, Icon, Modal, Popover, TextField ,Text } from '@shopify/polaris'
import React, { useCallback, useState, useEffect } from "react";
import { useDispatch } from 'react-redux';
import { contractReducer } from '../../../../data/features/memberDetails/memberDetailsSlice';
import { useSelector } from 'react-redux';
import { CalendarIcon } from "@shopify/polaris-icons";

export default function EditBillingInfo({modalOpen, setModalOpen}) {

    const dispatch = useDispatch();
    const contract = useSelector((state) => state.memberDetails?.data?.contract);

    // edit fields object
    let editVal = {
        name: contract?.cc_name,
        payment_method: contract?.cc_brand,
        expiry_date: new Date(contract?.cc_expiryYear, contract?.cc_expiryMonth-1)
    };


    const [editFieldVal, setEditFieldVal] = useState(editVal);
    useEffect(() => {
        setEditFieldVal(editVal)
    }, [contract])

    // edit input handle change
    const editInputHandleChange = (value, name) => {
        setEditFieldVal({
            ...editFieldVal,
            [name]: value,
        });
    };

    // cancle modal
    const cancleHandleChange = useCallback(
        () => {
            setModalOpen(!modalOpen);
            setEditFieldVal(editVal);
        },
        [modalOpen]
    );

    // ------- Expiry date ------

    // popver state
    const [visible, setVisible] = useState(false);

    // set month and year
    const [{ month, year }, setDate] = useState({
        month: editFieldVal.expiry_date.getMonth(),
        year: editFieldVal.expiry_date.getFullYear(),
    });

    // month change
    function handleMonthChange(month, year) {
        setDate({ month, year });
    }

    // select date
    function handleDateSelection({ end: newSelectedDate }) {
        setEditFieldVal({
            ...editFieldVal,
            expiry_date: newSelectedDate,
        });
        setVisible(false);
    }

    useEffect(() => {
        if (editFieldVal.expiry_date) {
            setDate({
                month: editFieldVal.expiry_date.getMonth(),
                year: editFieldVal.expiry_date.getFullYear(),
            });
        }
    }, [editFieldVal]);


    // update billing
    const billingHandleChange = useCallback(() => {
        const data = {
            cc_name: editFieldVal?.name,
            cc_brand: editFieldVal?.payment_method,
            cc_expiryMonth: month+1,
            cc_expiryYear: year
        }
        dispatch(contractReducer(data));
        setModalOpen(false);
        setEditFieldVal(editVal);
    }, [contract, editFieldVal]);

  return (
    <Modal
    open={modalOpen}
    onClose={cancleHandleChange}
    title={<Text variant="headingMd" as="h6" fontWeight='medium'>Billing Information</Text>}
    primaryAction={{
        content: `Update`,
        onAction: billingHandleChange,
        // tone : 'success',
    }}
    secondaryActions={[
        {
            content: `Cancel`,
            onAction: cancleHandleChange,
        }
    ]}
    >
        <Modal.Section>
            <div className='edit_billing_info_block '>
                {/* Name */}
                <div className="input_fields_wrap">
                    <TextField
                        label="Name"
                        type="text"
                        value={editFieldVal.name}
                        onChange={(value) =>
                            editInputHandleChange(value, "name")
                        }
                        autoComplete="off"
                    />
                </div>
                {/* Payment method */}
                <div className="input_fields_wrap ms-margin-top">
                    <TextField
                        label="Payment method"
                        type="text"
                        value={editFieldVal.payment_method}
                        onChange={(value) =>
                            editInputHandleChange(value, "payment_method")
                        }
                        autoComplete="off"
                    />
                </div>
                {/* Expiry date */}
                <div className="input_fields_wrap ms-margin-top">
                    <Popover
                        active={visible}
                        preferredAlignment="left"
                        fullWidth
                        preferInputActivator={false}
                        preferredPosition="below"
                        preventCloseOnChildOverlayClick
                        onClose={() => setVisible(false)}
                        activator={
                            <div className="input_fields_wrap">
                                <TextField
                                    role="combobox"
                                    label={"Expiry date"}
                                    suffix={<Icon source={CalendarIcon} />}
                                    value={editFieldVal.expiry_date.toLocaleDateString(
                                        "en-IN"
                                    )}
                                    onFocus={() => setVisible(true)}
                                    onChange={''}
                                    autoComplete="off"
                                />
                            </div>
                        }
                    >
                        <Card>
                          <  DatePicker
                                month={month}
                                year={year}
                                selected={editFieldVal.expiry_date}
                                onMonthChange={handleMonthChange}
                                onChange={handleDateSelection}
                            />
                        </Card>
                    </Popover>
                </div>
            </div>
        </Modal.Section>
    </Modal>
  )
}
