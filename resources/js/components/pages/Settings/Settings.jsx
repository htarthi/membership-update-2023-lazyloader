import React, { useCallback, useState, useEffect, lazy, Suspense } from 'react';
import SubHeader from '../../GlobalPartials/SubHeader/SubHeader';
import { Button, LegacyCard, Page, Layout, Text, InlineStack } from '@shopify/polaris';
import { useSelector, useDispatch } from 'react-redux';
import { getSettingsData, setSettingsData } from '../../../data/features/settings/settingAction';
import { setChanged, resetdefaultOpen, discardChanges } from '../../../data/features/settings/settingsDataSlice';
import SettingDetalsSekeleton from './partials/SettingDetalsSekeleton';
import { ContextualSaveBar } from '@shopify/app-bridge-react';

// Lazy loaded components
const CollapsibleAccordion = lazy(() => import('../../GlobalPartials/CollapsibleAccordion/CollapsibleAccordion'));
const AccordionEmailBody = lazy(() => import('./partials/AccordionEmailBody'));
const CustomerNotifications = lazy(() => import('./partials/CustomerNotifications'));
const StorefrontWidget = lazy(() => import('./partials/StorefrontWidget'));
const MemberPortal = lazy(() => import('./partials/MemberPortal'));
const AccountBillings = lazy(() => import('./partials/AccountBillings'));
const CustomerAccounts = lazy(() => import('./partials/CustomerAccounts'));
const AutomaticallyFulFilMembership = lazy(() => import('./partials/AutomaticallyFulFilMembership'));
const CreditCardsRetries = lazy(() => import('./partials/CreditCardsRetries'));
const RestrictedContent = lazy(() => import('./partials/RestrictedContent'));
const CancellationReasons = lazy(() => import('./partials/CancellationReasons'));
const StorefrontColour = lazy(() => import('./partials/StorefrontColour'));


export default function Settings() {
    const settings$ = useSelector((state) => state?.settings?.data?.data);
    const isLoading$ = useSelector((state) => state?.settings?.isLoading);
    const isSetSettingData$ = useSelector((state) => state?.settings?.isSetSettingData);
    const isChangeData$ = useSelector((state) => state?.settings?.isChangeData);
    const setdefaultOpen$ = useSelector((state) => state?.settings?.defaultOpen);
    const initialSettingData$ = useSelector((state) => state?.settings?.initialSettingData?.setting);

    const dispatch = useDispatch();
    const [open, setOpen] = useState();
    const [defaultOpen, setDefaultopen] = useState(setdefaultOpen$ ? setdefaultOpen$ : 0);

    const handleToggle = useCallback((id) => {
        open !== id ? setOpen(id) : setOpen();
    }, [open]);

    // Save Changes
    const saveChanges = useCallback(() => {
        dispatch(setSettingsData({ data: settings$ }));
    }, [settings$]);

    // Get data of setting API
    useEffect(() => {
        dispatch(getSettingsData());
        if (location.search === '?Billing') {
            setOpen(4);
        }
        if (location.search !== 'setting') {
            dispatch(resetdefaultOpen());
        }
    }, [dispatch]);

    const handleTabs = (getParam) => {
        setDefaultopen(getParam);
    };

    const handlediscard = useCallback(() => {
        dispatch(discardChanges());
    }, [dispatch]);

    return (
        <>
            <SubHeader title={"Settings"} secondButtonState={false} needHelp={false} />
            <div className='settings_wrap'>
                <Page fullWidth>
                    <div className='simplee_membership_container'>
                        <InlineStack gap="200" wrap={false} blockAlign="center">
                            <Button onClick={() => handleTabs(0)} disabled={defaultOpen === 0}>
                                <Text variant="bodyMd" as="span">General</Text>
                            </Button>
                            <Button onClick={() => handleTabs(1)} disabled={defaultOpen === 1}>
                                <Text variant="bodyMd" as="span">Emails</Text>
                            </Button>
                            <Button onClick={() => handleTabs(2)} disabled={defaultOpen === 2}>
                                <Text variant="bodyMd" as="span">Dunning & Cancellation</Text>
                            </Button>
                            <Button onClick={() => handleTabs(3)} disabled={defaultOpen === 3}>
                                <Text variant="bodyMd" as="span">Storefront & Portal</Text>
                            </Button>
                            <Button onClick={() => handleTabs(4)} disabled={defaultOpen === 4}>
                                <Text variant="bodyMd" as="span">Billing</Text>
                            </Button>
                        </InlineStack>
                        <br />
                        {!isLoading$ ? (
                            <>
                                <Layout>
                                    {defaultOpen === 0 && (
                                        <Layout.AnnotatedSection
                                            description={
                                                <>
                                                    <div>
                                                        <Text as="h1" variant="headingMd">Account Invitations</Text>
                                                        If using Classic Accounts, this will automatically send an email to new customers asking them to create their account.
                                                    </div>
                                                    <div style={{ marginTop: "40px" }}>
                                                        <Text as="h1" variant="headingMd">Product Fulfillment</Text>
                                                        Most membership products don’t need to be shipped. This setting determines whether membership products will be fulfilled automatically.
                                                    </div>
                                                    <div style={{ marginTop: "40px" }}>
                                                        <Text as="h1" variant="headingMd">Restricted Content Message</Text>
                                                        When non-members load a page, product, or blog post they don’t have access to, what message should be displayed in its place?
                                                    </div>
                                                </>
                                            }
                                        >
                                            <LegacyCard>
                                                <Suspense fallback={<SettingDetalsSekeleton />}>
                                                    <div style={{ marginBottom: "30px" }}>
                                                        <CollapsibleAccordion title="" handleToggle={handleToggle} id={6} open={6} showIcon={false} body={<CustomerAccounts />} />
                                                    </div>
                                                    <div style={{ marginBottom: "30px" }}>
                                                        <CollapsibleAccordion title="" handleToggle={handleToggle} id={7} open={7} showIcon={false} body={<AutomaticallyFulFilMembership />} />
                                                    </div>
                                                    <div style={{ marginBottom: "30px" }}>
                                                        <CollapsibleAccordion title="" handleToggle={handleToggle} id={8} open={8} showIcon={false} body={<RestrictedContent />} />
                                                    </div>
                                                </Suspense>
                                            </LegacyCard>
                                        </Layout.AnnotatedSection>
                                    )}
                                    {defaultOpen === 1 && (
                                        <Layout.AnnotatedSection
                                            description={
                                                <>
                                                    <div style={{ marginTop: "30px" }}>
                                                        <Text as="h1" variant="headingMd">Member Emails</Text>
                                                        These emails are sent to new and existing members when important events occur with their membership.
                                                    </div>
                                                    <div style={{ marginTop: "570px" }}>
                                                        <Text as="h1" variant="headingMd">Merchant Emails</Text>
                                                        These are notifications sent to a specific email address with information about important member events.
                                                    </div>
                                                </>
                                            }
                                        >
                                            <LegacyCard>
                                                <Suspense fallback={<SettingDetalsSekeleton />}>
                                                    <div style={{ marginBottom: "30px" }}>
                                                        <CollapsibleAccordion title="" handleToggle={handleToggle} id={0} open={0} showIcon={false} body={<AccordionEmailBody />} />
                                                    </div>
                                                    <div style={{ marginBottom: "30px" }}>
                                                        <CollapsibleAccordion title="" handleToggle={handleToggle} id={1} open={1} showIcon={false} body={<CustomerNotifications />} />
                                                    </div>
                                                </Suspense>
                                            </LegacyCard>
                                        </Layout.AnnotatedSection>
                                    )}
                                    {defaultOpen === 2 && (
                                        <Layout.AnnotatedSection
                                            description={
                                                <>
                                                    <div>
                                                        <Text as="h1" variant="headingMd">Failed Payments</Text>
                                                        Choose how often to retry processing orders which have failed.
                                                    </div>
                                                    <div style={{ marginTop: "190px" }}>
                                                        <Text as="h1" variant="headingMd">Cancellation Reasons</Text>
                                                        Ask members to provide the reason that they are cancelling their membership.
                                                    </div>
                                                </>
                                            }
                                        >
                                            <LegacyCard>
                                                <Suspense fallback={<SettingDetalsSekeleton />}>
                                                    <div style={{ marginBottom: "30px" }}>
                                                        <CollapsibleAccordion title="" handleToggle={handleToggle} id={5} open={5} showIcon={false} body={<CreditCardsRetries />} />
                                                    </div>
                                                    <div style={{ marginBottom: "30px" }}>
                                                        <CollapsibleAccordion title="" handleToggle={handleToggle} id={4} open={4} showIcon={false} body={<CancellationReasons />} />
                                                    </div>
                                                </Suspense>
                                            </LegacyCard>
                                        </Layout.AnnotatedSection>
                                    )}
                                    {defaultOpen === 3 && (
                                        <Layout.AnnotatedSection
                                            description={
                                                <>
                                                    <div>
                                                        <Text as="h1" variant="headingMd">Storefront Widget</Text>
                                                        Determine where the membership widget should be displayed.
                                                    </div>
                                                    <div style={{ marginTop: "80px" }}>
                                                        <Text as="h1" variant="headingMd">Storefront Colour</Text>
                                                        The colour scheme for the public pages of your store.
                                                    </div>
                                                    <div style={{ marginTop: "100px" }}>
                                                        <Text as="h1" variant="headingMd">Member Portal</Text>
                                                        Here you can customize the styling and behaviour of the Member Portal.
                                                    </div>

                                                </>
                                            }
                                        >
                                            <LegacyCard>
                                                <Suspense fallback={<SettingDetalsSekeleton />}>
                                                    <div style={{ marginBottom: "30px" }}>
                                                        <CollapsibleAccordion title="" handleToggle={handleToggle} id={3} open={3} showIcon={false} body={<StorefrontWidget />} />
                                                    </div>

                                                    <div style={{ marginBottom: "30px" }}>
                                                        <CollapsibleAccordion title="" handleToggle={handleToggle} id={9} open={9} showIcon={false} body={<StorefrontColour />} />
                                                    </div>
                                                    <div style={{ marginBottom: "30px" }}>
                                                        <CollapsibleAccordion title="" handleToggle={handleToggle} id={2} open={2} showIcon={false} body={<MemberPortal />} />
                                                    </div>
                                                </Suspense>
                                            </LegacyCard>
                                        </Layout.AnnotatedSection>
                                    )}
                                    {defaultOpen === 4 && (
                                        <Layout.AnnotatedSection
                                            description={
                                                <div>
                                                    <Text as="h1" variant="headingMd">Account Billings</Text>
                                                    These options allow you to customize how and when your customers are billed.
                                                </div>
                                            }
                                        >
                                            <LegacyCard>
                                                <Suspense fallback={<SettingDetalsSekeleton />}>
                                                    <div style={{ marginBottom: "30px" }}>
                                                        <CollapsibleAccordion title="" handleToggle={handleToggle} id={10} open={10} showIcon={false} body={<AccountBillings />} />
                                                    </div>
                                                </Suspense>
                                            </LegacyCard>
                                        </Layout.AnnotatedSection>
                                    )}
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
                                                onAction: handlediscard,
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
        </>
    );
}
