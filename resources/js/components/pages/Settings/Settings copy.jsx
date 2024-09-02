import React, { useCallback, useState, useEffect } from 'react'
import SubHeader from '../../GlobalPartials/SubHeader/SubHeader'
import { Button, LegacyCard, Page } from '@shopify/polaris'
import CollapsibleAccordion from '../../GlobalPartials/CollapsibleAccordion/CollapsibleAccordion';
import AccordionEmailBody from './partials/AccordionEmailBody';
import CustomerNotifications from './partials/CustomerNotifications';
import StorefrontWidget from './partials/StorefrontWidget';
import MemberPortal from './partials/MemberPortal';
import AccountBillings from './partials/AccountBillings';
import { useSelector } from 'react-redux';
import { getSettingsData, setSettingsData } from '../../../data/features/settings/settingAction';
import { useDispatch } from 'react-redux';
import CustomerAccounts from './partials/CustomerAccounts';
import AutomaticallyFulFilMembership from './partials/AutomaticallyFulFilMembership';
import CreditCardsRetries from './partials/CreditCardsRetries';
import PlanDetailsSkeleton from '../PlansDetail/partials/PlanDetailsSkeleton';
import SettingDetalsSekeleton from './partials/SettingDetalsSekeleton';
import RestrictedContent from './partials/RestrictedContent';
import CancellationReasons from './partials/CancellationReasons';

export default function Settings() {

    const settings$ = useSelector((state) => state?.settings?.data?.data);

    const isLoading$ = useSelector((state) => state?.settings?.isLoading);




    const isSetSettingData$ = useSelector((state) => state?.settings?.isSetSettingData);
    const dispatch = useDispatch()
    // accordian
    const [open, setOpen] = useState();

    const handleToggle = useCallback((id) => {
        open !== id ? setOpen(id) : setOpen();
    }, [open]);

    // Save Changes
    const saveChanges = useCallback(() => {
        dispatch(setSettingsData(
            {
                data: settings$
            }
        ));
    }, [settings$])

    // get data of setting api
    useEffect(() => {

        dispatch(getSettingsData());
        console.log(location.search);
        if (location.search == '?Billing') {
            console.log("dffd");
            setOpen(4);
        }
    }, [])

    return <>

        <SubHeader title={"Settings"} secondButtonState={false} needHelp={false} />
        <div className='settings_wrap'>
            <Page fullWidth>
                <div className='simplee_membership_container'>

                    {!isLoading$ ? (

                        <LegacyCard>

                            {/* email accordion */}
                            <CollapsibleAccordion title="Emails" handleToggle={handleToggle} id={0} open={open}
                                body={<AccordionEmailBody />}
                            />

                            {/* Customer Notifications accordion */}
                            <CollapsibleAccordion title="Merchant Notifications" handleToggle={handleToggle} id={1} open={open}
                                body={<CustomerNotifications />}
                            />

                            {/* Storefront Widget Accordion */}
                            <CollapsibleAccordion title="Storefront Widget" handleToggle={handleToggle} id={2} open={open}
                                body={<StorefrontWidget />}
                            />

                            {/* Member Portal */}
                            <CollapsibleAccordion title="Member Portal" handleToggle={handleToggle} id={3} open={open}
                                body={<MemberPortal />}
                            />

                            {/* Account & Billings */}


                            {/* Failed Credit Card Retries */}
                            <CollapsibleAccordion title="Failed Credit Card Retries" handleToggle={handleToggle} id={5} open={open}
                                body={<CreditCardsRetries />}
                            />

                            {/* Customer Accounts */}
                            <CollapsibleAccordion title="Account Invitations" handleToggle={handleToggle} id={6} open={open}
                                body={<CustomerAccounts />}
                            />

                            {/* Automatically Fulfill Membership Products */}
                            <CollapsibleAccordion title="Automatically Fulfill Membership Products" handleToggle={handleToggle} id={7} open={open}
                                body={<AutomaticallyFulFilMembership />}
                            />

                            <CollapsibleAccordion title="Restricted Content" handleToggle={handleToggle} id={8} open={open}
                                body={<RestrictedContent />}
                            />

                            <CollapsibleAccordion title="Cancellation Reasons" handleToggle={handleToggle} id={9} open={open}
                                body={<CancellationReasons />}
                            />



                            <CollapsibleAccordion title="Plan & Billing" handleToggle={handleToggle} id={4} open={open}
                                body={<AccountBillings />}
                            />



                            {/* save changes */}
                            <div className='savechanges_block'>
                                <Button

                                    loading={isSetSettingData$ ? true : false}
                                    onClick={saveChanges}
                                    variant="primary" >Save Changes</Button>
                            </div>

                        </LegacyCard>


                    ) : (
                        <SettingDetalsSekeleton />
                    )}
                </div>
            </Page>


        </div>
    </>;
}
