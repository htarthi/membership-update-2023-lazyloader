import React, { useCallback, useState, useEffect } from 'react'
import SubHeader from '../../GlobalPartials/SubHeader/SubHeader'
import { Button, LegacyCard, Page, Layout, FormLayout, TextField, Text, Frame, InlineStack } from '@shopify/polaris'
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
import StorefrontColour from './partials/StorefrontColour';
import { ContextualSaveBar } from '@shopify/app-bridge-react';
import { setChanged } from '../../../data/features/settings/settingsDataSlice';

export default function Settings() {

    const settings$ = useSelector((state) => state?.settings?.data?.data);
    const isLoading$ = useSelector((state) => state?.settings?.isLoading);
    const isSetSettingData$ = useSelector((state) => state?.settings?.isSetSettingData);
    const isChangeData$ = useSelector((state) => state?.settings?.isChangeData);

    const dispatch = useDispatch()
    // accordian
    const [open, setOpen] = useState();
    const [defaultOpen, setDefaultopen] = useState(0);

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
        if (location.search == '?Billing') {
            setOpen(4);
        }
    }, []);

    const handleTabs = (getParam) => {
        setDefaultopen(getParam);
    }

    return <>
        <SubHeader title={"Settings"} secondButtonState={false} needHelp={false} />
        <div className='settings_wrap'>
            <Page fullWidth>
                <div className='simplee_membership_container'>
                    <InlineStack gap="200" wrap={false} blockAlign="center">
                        
                        <Button onClick={() => handleTabs(0)} disabled={defaultOpen === 0 ? true : false}>
                            <Text variant="bodyMd" as="span">General</Text>
                        </Button>
                        <Button onClick={() => handleTabs(1)} disabled={defaultOpen === 1 ? true : false}>
                            <Text variant="bodyMd" as="span">Emails</Text>
                        </Button>
                        <Button onClick={() => handleTabs(2)} disabled={defaultOpen === 2 ? true : false}>
                            <Text variant="bodyMd" as="span">Dunning & Cancellation</Text>
                        </Button>
                        <Button onClick={() => handleTabs(3)} disabled={defaultOpen === 3 ? true : false}>
                            <Text variant="bodyMd" as="span">Storefront & Portal</Text>
                        </Button>
                        <Button onClick={() => handleTabs(4)} disabled={defaultOpen === 4 ? true : false}>
                            <Text variant="bodyMd" as="span">Billing</Text>
                        </Button>
                    </InlineStack><br></br>
                    {!isLoading$ ? (
                        <>
                            <Layout >
                                {
                                    defaultOpen === 0 ?
                                        <Layout.AnnotatedSection
                                            title="General"
                                        >
                                            <LegacyCard >
                                                {/* Customer Accounts */}
                                                <CollapsibleAccordion title="Account Invitations" handleToggle={handleToggle} id={6} open={6} showIcon={false}
                                                    body={<CustomerAccounts />}
                                                />
                                                {/* Automatically Fulfill Membership Products */}
                                                <CollapsibleAccordion title="Product Fulfillment" handleToggle={handleToggle} id={7} open={7} showIcon={false}
                                                    body={<AutomaticallyFulFilMembership />}
                                                />
                                                <CollapsibleAccordion title="Restricted Content Message" handleToggle={handleToggle} id={8} open={8} showIcon={false}
                                                    body={<RestrictedContent />}
                                                />

                                            </LegacyCard>
                                        </Layout.AnnotatedSection>
                                        : ''
                                }
                                {
                                    defaultOpen === 1 ?
                                        <Layout.AnnotatedSection
                                            title="Emails"
                                        // description="Shopify and your customers will use this information to contact you."
                                        >
                                            <LegacyCard>
                                                {/* email accordion */}
                                                <CollapsibleAccordion title="Member Emails" handleToggle={handleToggle} id={0} open={0} showIcon={false}
                                                    body={<AccordionEmailBody />}
                                                />
                                                {/* Customer Notifications accordion */}
                                                <CollapsibleAccordion title="Merchant Emails" handleToggle={handleToggle} id={1} open={1} showIcon={false}
                                                    body={<CustomerNotifications />}
                                                />
                                            </LegacyCard>
                                        </Layout.AnnotatedSection>
                                        : ''
                                }
                                {
                                    defaultOpen === 2 ?
                                        <Layout.AnnotatedSection
                                            title="Dunning & Cancellation"
                                        // description="Shopify and your customers will use this information to contact you."
                                        >
                                            <LegacyCard>
                                                {/* Failed Credit Card Retries */}
                                                <CollapsibleAccordion title="Failed Payments" handleToggle={handleToggle} id={5} open={5} showIcon={false}
                                                    body={<CreditCardsRetries />}
                                                />
                                                <CollapsibleAccordion title="Cancellation Reasons" handleToggle={handleToggle} id={9} open={9} showIcon={false}
                                                    body={<CancellationReasons />}
                                                />

                                            </LegacyCard>
                                        </Layout.AnnotatedSection>
                                        : ''
                                }
                                {
                                    defaultOpen === 3 ?
                                        <Layout.AnnotatedSection
                                            title="Storefront & Portal"
                                        // description="Shopify and your customers will use this information to contact you."
                                        >
                                            <LegacyCard >
                                                {/* Storefront Widget Accordion */}
                                                <CollapsibleAccordion title="Widget Title" handleToggle={handleToggle} id={2} open={2} showIcon={false}
                                                    body={<StorefrontWidget />}
                                                />

                                                <CollapsibleAccordion title="Widget Colors" handleToggle={handleToggle} id={10} open={10} showIcon={false}
                                                    body={<StorefrontColour />}
                                                />


                                                {/* Member Portal */}
                                                <CollapsibleAccordion title="Member Portal" handleToggle={handleToggle} id={3} open={3} showIcon={false}
                                                    body={<MemberPortal />}
                                                />
                                            </LegacyCard>
                                        </Layout.AnnotatedSection>
                                        : ''
                                }
                                {
                                    defaultOpen === 4 ?
                                        <Layout.AnnotatedSection
                                            title="Billing"
                                        // description="Shopify and your customers will use this information to contact you."
                                        >
                                            <LegacyCard>
                                                <CollapsibleAccordion title="Plan & Billing" handleToggle={handleToggle} id={4} open={4} showIcon={false}
                                                    body={<AccountBillings />}
                                                />
                                            </LegacyCard>
                                        </Layout.AnnotatedSection>
                                        : ''
                                }

                                {isChangeData$ && (
                                    <ContextualSaveBar
                                        saveAction={{
                                            onAction: saveChanges,
                                            loading: false,
                                            disabled: false,
                                        }}
                                        leaveConfirmationDisable
                                        fullWidth
                                        visible
                                        discardAction={{
                                            loading: false,
                                            disabled: true,
                                        }}
                                    />
                                )}

                            </Layout>
                        </>
                    ) : (
                        <SettingDetalsSekeleton />
                    )}
                </div>
            </Page>
        </div>
    </>;
}
