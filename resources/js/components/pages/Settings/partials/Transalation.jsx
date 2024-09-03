import React, { useEffect, useCallback, useState } from 'react';
import { useSelector } from 'react-redux';
import { Button, LegacyCard, Page, Select, TextField, Icon , Text } from "@shopify/polaris";
import { NavLink } from "react-router-dom";
import { ArrowLeftIcon } from "@shopify/polaris-icons";
import { useDispatch } from 'react-redux';
import { resetisSuccess, updateTransalationsdata,setdefaultOpen } from '../../../../data/features/settings/settingsDataSlice'; //hitarthi
import { gettranslationData, updateTranslation } from '../../../../data/features/settings/settingAction';
import { useNavigate } from 'react-router-dom';

export const Transalation = () => {

    const dispatch = useDispatch();
    const navigate = useNavigate();
    const [Disabled, setDisabled] = (useState(true));
    const transalation$ = useSelector((state) => state?.settings?.data?.data?.portal?.transalation);
    const errors$ = useSelector((state) => state?.settings?.data?.data?.portal?.transalation?.errors);
    const qwe$ = useSelector((state) => state?.settings?.isTransaltionsSuccess);
    const originaltext = transalation$;
    const date_formats = [
        { label: 'YYYY-MM-DD', value: 'Y-m-d' },
        { label: 'YYYY-DD-MM', value: 'Y-d-m' },
        { label: 'MM-DD-YYYY', value: 'm-d-Y' },
        { label: 'MM-YYYY-DD', value: 'm-Y-d' },
        { label: 'DD-YYYY-MM', value: 'd-Y-m' },
        { label: 'DD-MM-YYYY', value: 'd-m-Y' },
        { label: 'YYYY/MM/DD', value: 'Y/m/d' },
        { label: 'YYYY/DD/MM', value: 'Y/d/m' },
        { label: 'MM/YYYY/DD', value: 'm/Y/d' },
        { label: 'MM/DD/YYYY', value: 'm/d/Y' },
        { label: 'DD/YYYY/MM', value: 'd/Y/m' },
        { label: 'DD/MM/YYYY', value: 'd/m/Y' },
        { label: 'MMM DD, Y', value: 'M d, Y' }
    ]

    const handleChange = useCallback((val, key) => {
        const updateField = {
            [key]: val ? val : ''
        }
        dispatch(updateTransalationsdata(updateField))
    }, [])

    useEffect(() => {
        dispatch(gettranslationData())
    }, [])

    useEffect(() => {
        if (qwe$ == true) {
            dispatch(gettranslationData());
            dispatch(resetisSuccess());
        }
    }, [qwe$])

    const handleSaveTransaltion = useCallback(() => {
        dispatch(updateTranslation(transalation$.languages))
        dispatch(gettranslationData())
    }, [transalation$])

    const handleBackEvent = useCallback(() => {
        dispatch(setdefaultOpen(3)) //hitarthi
        navigate('/settings')
    })

    return <>
        <Page fullWidth>
            <div className="plans_list_wrap setting_list_wrap">
                <div className="simplee_membership_container">
                    <div className='navlink-warp'>
                        <div className="members_navigate_wrap">
                            <a onClick={() => handleBackEvent()}>
                                <NavLink className="back_arrow_wrap"  >
                                    <Icon source={ArrowLeftIcon} tone="base" />
                                </NavLink>
                            </a>
                        </div>
                    </div>
                    <div style={{ marginTop : '15px'}}>
                        <Text variant='headingXl' fontWeight='medium' as='h2' >Member Portal Translations</Text>
                    </div>
                    <div className='general_wrap'>
                        <Text variant='headingXl' fontWeight='medium' as='h2' >General</Text>
                        <LegacyCard>
                            <div className='general_inputs inputs_wrap'>
                                <div className='inputs'>
                                    {/* <div className='textField_wrap'> */}
                                    <TextField value={transalation$?.languages?.portal_title_details} onChange={(value) => handleChange(value, 'portal_title_details')} error={errors$?.['data.portal_title_details']?.[0] || ''} label="Page Heading" ></TextField>

                                    <TextField value={transalation$?.languages?.portal_title_subscriptions} onChange={(value) => handleChange(value, 'portal_title_subscriptions')} error={errors$?.['data.portal_title_subscriptions']?.[0] || ''} label="Membership List"></TextField>
                                    {/* </div> */}

                                    {/* <div className='textField_wrap'> */}

                                    <TextField value={transalation$?.languages?.portal_general_active} onChange={(value) => handleChange(value, 'portal_general_active')} error={errors$?.['data.portal_general_active']?.[0] || ''} label="Active"></TextField>

                                    <TextField value={transalation$?.languages?.portal_general_cancelled} onChange={(value) => handleChange(value, 'portal_general_cancelled')} error={errors$?.['data.portal_general_cancelled']?.[0] || ''} label="Cancelled"></TextField>
                                    {/* </div> */}


                                    <TextField value={transalation$?.languages?.portal_products_title} onChange={(value) => handleChange(value, 'portal_products_title')} error={errors$?.['data.portal_products_title']?.[0] || ''} label="Membership"></TextField>

                                    <TextField value={transalation$?.languages?.member_number} onChange={(value) => handleChange(value, 'member_number')} error={errors$?.['data.member_number']?.[0] || ''} label="Member Number"></TextField>

                                    <TextField value={transalation$?.languages?.portal_status_display_lifetime} onChange={(value) => handleChange(value, 'portal_status_display_lifetime')} error={errors$?.['data.portal_status_display_lifetime']?.[0] || ''} label="Lifetime Access"></TextField>

                                    <TextField value={transalation$?.languages?.portal_status_display_expiring} onChange={(value) => handleChange(value, 'portal_status_display_expiring')} error={errors$?.['data.portal_status_display_expiring']?.[0] || ''} label="Active - Expiring"></TextField>

                                    <TextField value={transalation$?.languages?.portal_status_display_billing_failed} onChange={(value) => handleChange(value, 'portal_status_display_billing_failed')} error={errors$?.['data.portal_status_display_billing_failed']?.[0] || ''} label="Billing Failed"></TextField>

                                    <TextField value={transalation$?.languages?.portal_member_id} onChange={(value) => handleChange(value, 'portal_member_id')} error={errors$?.['data.portal_member_id']?.[0] || ''} label="Portal Member Id"></TextField>

                                    <TextField value={transalation$?.languages?.mem_biling_info} onChange={(value) => handleChange(value, 'mem_biling_info')} error={errors$?.['data.mem_biling_info']?.[0] || ''} label="Membership Billing Information "></TextField>

                                    <TextField value={transalation$?.languages?.mem_upcoming} onChange={(value) => handleChange(value, 'mem_upcoming')} error={errors$?.['data.mem_upcoming']?.[0] || ''} label="View Upcoming Renewals"></TextField>

                                    <TextField value={transalation$?.languages?.mem_resume} onChange={(value) => handleChange(value, 'mem_resume')} error={errors$?.['data.mem_resume']?.[0] || ''} label="Cancel or Resume Your existing Membership"></TextField>


                                </div>
                            </div>
                        </LegacyCard>
                    </div>
                    <div className='general_wrap'  >
                        <Text variant='headingXl' fontWeight='medium' as='h2' >Membership Section</Text>
                        <LegacyCard>
                            <div className='Membership Section_inputs inputs_wrap'>
                                <div className='inputs'>
                                    <TextField value={transalation$?.languages?.portal_products_title} onChange={(value) => handleChange(value, 'portal_products_title')} error={errors$?.['data.portal_products_title']?.[0] || ''} label="Membership"></TextField>

                                    <TextField value={transalation$?.languages?.portal_products_subtotal} onChange={(value) => handleChange(value, 'portal_products_subtotal')} error={errors$?.['data.portal_products_subtotal']?.[0] || ''} label="Subtotal"></TextField>

                                    <TextField value={transalation$?.languages?.protal_products_price} onChange={(value) => handleChange(value, 'protal_products_price')} error={errors$?.['data.protal_products_price']?.[0] || ''} label="Price"></TextField>

                                    <TextField value={transalation$?.languages?.portal_products_qty} onChange={(value) => handleChange(value, 'portal_products_qty')} error={errors$?.['data.portal_products_qty']?.[0] || ''} label="Quantity"></TextField>
                                </div>
                            </div>
                        </LegacyCard>
                    </div>
                    <div className='general_wrap'  >
                        <Text variant='headingXl' fontWeight='medium' as='h2' >Renewal Section</Text>
                        <LegacyCard>
                            <div className='Renewal Section_inputs inputs_wrap'>
                                <div className='inputs'>
                                    <TextField value={transalation$?.languages?.portal_order_title} onChange={(value) => handleChange(value, 'portal_order_title')} error={errors$?.['data.portal_order_title']?.[0] || ''} label="Renewal Information"></TextField>

                                    <TextField value={transalation$?.languages?.portal_order_to} onChange={(value) => handleChange(value, 'portal_order_to')} error={errors$?.['data.portal_order_to']?.[0] || ''} label="Member email"></TextField>

                                    <TextField value={transalation$?.languages?.portal_order_billing} onChange={(value) => handleChange(value, 'portal_order_billing')} error={errors$?.['data.portal_order_billing']?.[0] || ''} label="Next billing date"></TextField>

                                    <TextField value={transalation$?.languages?.portal_order_frequency} onChange={(value) => handleChange(value, 'portal_order_frequency')} error={errors$?.['data.portal_order_frequency']?.[0] || ''} label="Membership length"></TextField>

                                    <TextField value={transalation$?.languages?.portal_next_renewal} onChange={(value) => handleChange(value, 'portal_next_renewal')} error={errors$?.['data.portal_next_renewal']?.[0] || ''} label="Next renewal"></TextField>

                                    <TextField value={transalation$?.languages?.portal_order_delivery} onChange={(value) => handleChange(value, 'portal_order_delivery')} error={errors$?.['data.portal_order_delivery']?.[0] || ''} label="Next renewal date"></TextField>

                                    <TextField value={transalation$?.languages?.portal_order_method} onChange={(value) => handleChange(value, 'portal_order_method')} error={errors$?.['data.portal_order_method']?.[0] || ''} label="Shipping method"></TextField>

                                    <TextField value={transalation$?.languages?.portal_order_address} onChange={(value) => handleChange(value, 'portal_order_address')} error={errors$?.['data.portal_order_address']?.[0] || ''} label="Member information"></TextField>

                                </div>
                            </div>
                        </LegacyCard>
                    </div>
                    <div className='general_wrap'  >
                        <Text variant='headingXl' fontWeight='medium' as='h2' >Billing Section</Text>
                        <LegacyCard>
                            <div className='Billing Section_inputs inputs_wrap'>
                                <div className='inputs'>
                                    <TextField value={transalation$?.languages?.portal_billing_title} onChange={(value) => handleChange(value, 'portal_billing_title')} error={errors$?.['data.portal_billing_title']?.[0] || ''} label="Billing Information"></TextField>

                                    <TextField value={transalation$?.languages?.portal_billing_card} onChange={(value) => handleChange(value, 'portal_billing_card')} error={errors$?.['data.portal_billing_card']?.[0] || ''} label="Card on file"></TextField>

                                    <TextField value={transalation$?.languages?.portal_billing_ending} onChange={(value) => handleChange(value, 'portal_billing_ending')} error={errors$?.['data.portal_billing_ending']?.[0] || ''} label="ending in"></TextField>

                                    <TextField value={transalation$?.languages?.portal_billing_instructions} onChange={(value) => handleChange(value, 'portal_orderportal_billing_instructions_address')} error={errors$?.['data.portal_billing_instructions']?.[0] || ''} label="To update billing information..."></TextField>

                                    <TextField value={transalation$?.languages?.portal_billing_send} onChange={(value) => handleChange(value, 'portal_billing_send')} error={errors$?.['data.portal_billing_send']?.[0] || ''} label="Send instructions"></TextField>

                                    <TextField value={transalation$?.languages?.portal_billing_summary} onChange={(value) => handleChange(value, 'portal_billing_summary')} error={errors$?.['data.portal_billing_summary']?.[0] || ''} label="Summary"></TextField>

                                    <TextField value={transalation$?.languages?.portal_billing_subtotal} onChange={(value) => handleChange(value, 'portal_billing_subtotal')} error={errors$?.['data.portal_billing_subtotal']?.[0] || ''} label="Subtotal"></TextField>

                                    <TextField value={transalation$?.languages?.portal_billing_total} onChange={(value) => handleChange(value, 'portal_billing_total')} error={errors$?.['data.portal_billing_total']?.[0] || ''} label="Total"></TextField>

                                    <TextField value={transalation$?.languages?.portal_billing_cancel} onChange={(value) => handleChange(value, 'portal_billing_cancel')} error={errors$?.['data.portal_billing_cancel']?.[0] || ''} label="cancel"></TextField>

                                </div>
                            </div>
                        </LegacyCard>
                    </div>
                    <div className='general_wrap'  >
                        <Text variant='headingXl' fontWeight='medium' as='h2'>Additional Text</Text>
                        <LegacyCard>
                            <div className='Additional_Text_inputs inputs_wrap'>
                                <div className='inputs'>
                                    <TextField value={transalation$?.languages?.portal_general_status} onChange={(value) => handleChange(value, 'portal_general_status')} error={errors$?.['data.portal_general_status']?.[0] || ''} label="Status"></TextField>

                                    <TextField value={transalation$?.languages?.portal_action_cancel} onChange={(value) => handleChange(value, 'portal_action_cancel')} error={errors$?.['data.portal_action_cancel']?.[0] || ''} label="Cancel membership"></TextField>

                                    <TextField value={transalation$?.languages?.portal_billing_discount} onChange={(value) => handleChange(value, 'portal_billing_discount')} error={errors$?.['data.portal_billing_discount']?.[0] || ''} label="Discount"></TextField>

                                    <TextField value={transalation$?.languages?.portal_popup_cancel_text} onChange={(value) => handleChange(value, 'portal_popup_cancel_text')} error={errors$?.['data.portal_popup_cancel_text']?.[0] || ''} label="Are you sure you want to cancel?"></TextField>

                                    <TextField value={transalation$?.languages?.portal_popup_cancel_title} onChange={(value) => handleChange(value, 'portal_popup_cancel_title')} error={errors$?.['data.portal_popup_cancel_title']?.[0] || ''} label="Cancel your membership?"></TextField>

                                    <TextField value={transalation$?.languages?.portal_popup_cancel_no} onChange={(value) => handleChange(value, 'portal_popup_cancel_no')} error={errors$?.['data.portal_popup_cancel_no']?.[0] || ''} label="Go back"></TextField>

                                    <TextField value={transalation$?.languages?.portal_warning_title} onChange={(value) => handleChange(value, 'portal_warning_title')} error={errors$?.['data.portal_warning_title']?.[0] || ''} label="IMPORTANT"></TextField>

                                    <TextField value={transalation$?.languages?.portal_warning_text} onChange={(value) => handleChange(value, 'portal_warning_text')} error={errors$?.['data.portal_warning_text']?.[0] || ''} label="Payment method failed"></TextField>

                                    <TextField value={transalation$?.languages?.portal_no_membership} onChange={(value) => handleChange(value, 'portal_no_membership')} error={errors$?.['data.portal_no_membership']?.[0] || ''} label="You do not have any memberships"></TextField>

                                    <TextField value={transalation$?.languages?.toaster_email_sent} onChange={(value) => handleChange(value, 'toaster_email_sent')} error={errors$?.['data.toaster_email_sent']?.[0] || ''} label="Email sent"></TextField>

                                    <TextField value={transalation$?.languages?.toaster_membership_updated} onChange={(value) => handleChange(value, 'toaster_membership_updated')} error={errors$?.['data.toaster_membership_updated']?.[0] || ''} label="Membership updated"></TextField>

                                    <TextField value={transalation$?.languages?.portal_error_required} onChange={(value) => handleChange(value, 'portal_error_required')} error={errors$?.['data.portal_error_required']?.[0] || ''} label="This is a required field"></TextField>

                                    <TextField value={transalation$?.languages?.portal_is_waiting} onChange={(value) => handleChange(value, 'portal_is_waiting')} error={errors$?.['data.portal_is_waiting']?.[0] || ''} label="Loading..."></TextField>
                                    <Select
                                        label="Date Format"
                                        options={date_formats}
                                        value={transalation$?.languages?.date_format ? transalation$?.languages?.date_format : " "}
                                        onChange={(value) => handleChange(value, 'date_format')}
                                        error={errors$?.['data.date_format']?.[0] || ''}
                                    />

                                </div>
                            </div>
                        </LegacyCard>
                    </div>
                    <div className='btn-primart-transalation-save'>
                        <Button onClick={(e) => handleSaveTransaltion()} id="btn-save"  variant="primary">Save Changes </Button>
                    </div>
                </div>
            </div>
        </Page>
    </>;
}


