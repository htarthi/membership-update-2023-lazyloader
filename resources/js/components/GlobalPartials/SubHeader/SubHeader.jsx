import { Button, ButtonGroup, Link, Text , Modal , TextField } from '@shopify/polaris'
import React from 'react'
import { useSelector } from "react-redux";
import instance from "../../../components/shopify/instance";
import { toast } from 'react-toastify';
import {useState, useCallback} from 'react';

export default function SubHeader({ title, secondButtonState, secButtonName, buttonHandleEvent, needHelp = true, exportButtonState = false, isPlanExport = false }) {
    const defaultFilter$ = useSelector((state) => state?.members?.defaultFilter);
    const member$ = useSelector((state) => state?.members?.data);
    const plans$ = useSelector((state) => state.plans?.data);
    const [active, setActive] = useState(false);
    const [email, setEmail] = useState("");
    const [checkerror, setCheckError] = useState(false);
    const shopID = plans$?.shop?.id ? plans$?.shop?.id : plans$?.planG[0]?.shop_id ;

    const handleChange = useCallback(
        () => setActive(!active), [active]

    );
    const handleEmailChange = useCallback((newValue, name) => {
        setEmail(newValue);
        setCheckError(false);
    }, [email]);

    const isValidEmail = (email)  => {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return pattern.test(email);
    }

    const resetData = () => {
        setCheckError(false);
        setEmail("");
        handleChange();
    }


    const handleExport = async () => {
        try {
            if(!isValidEmail(email)) {
                setCheckError(true);
            }else{
                setCheckError(false);
                const response = await instance.get(`/subscribers/export/${member$.shop}/${email}/${defaultFilter$.f}/${defaultFilter$.p ? defaultFilter$.p : 'All Plans'}/${defaultFilter$.lp}/${defaultFilter$.s}`);

                toast.success("Export will be emailed to " + email);
                setEmail("");
                setActive(false);
            }

        } catch (error) {
            console.error('Error exporting CSV:', error);
        }
    };

    const plansExport = async () => {
        try {
            const response = await instance.get(`/plans/export/${shopID}`);

            const blob = new Blob([response.data], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', shopID + '_plans_export.csv');
            document.body.appendChild(link);
            link.click();
            link.remove();
        } catch (error) {
            console.error('Error exporting CSV:', error);
        }
    };


    return (
        <div className='subheader_wrap'>
            <Text variant="headingLg" as="h5" fontWeight='medium'>{title}</Text>
            <ButtonGroup>
                {
                    exportButtonState ?
                        (member$?.memberships.count ?

                            <Button onClick={handleChange}>Export</Button>
                            : "") : ""
                }
                {
                    isPlanExport ?
                        (plans$?.planG.length > 0 ?

                            <Button onClick={plansExport}>Export</Button>
                            : "") : ""
                }

                {
                    needHelp &&
                    <Button target='_blank' url='https://support.simplee.best/en/articles/9280235-creating-and-managing-plans'>Need Help?</Button>
                }
                {
                    secondButtonState &&
                    <Button  onClick={buttonHandleEvent} variant="primary" >{secButtonName}</Button>
                }
            </ButtonGroup>
            <Modal
                open={active}
                onClose={handleChange}
                title={<Text>Export Memberships</Text>}
                primaryAction={{
                    content: 'Send Mail',
                    onAction: handleExport,
                    // tone : 'success',
                }}
                secondaryActions={[
                    {
                    content: 'Cancel',
                    onAction: resetData,
                    },
                ]}
                >
                <Modal.Section>
                        <TextField
                            type='email'
                            label={"Email"}
                            value={email}
                            name='email'
                            onChange={(val) => handleEmailChange(val)}
                            autoComplete="email"
                            error={checkerror ? "Email is Invalid" : ''}
                        />
                </Modal.Section>
            </Modal>
        </div>
    );
}


