import React, { useEffect } from "react";

import { useTranslation } from "react-i18next";
import {
    useDispatch,
    useSelector,
} from "react-redux";
import { useNavigate } from "react-router-dom";

import {
    Link,
    Page,
    SkeletonBodyText,
    Text,
} from "@shopify/polaris";

import { getData } from "../../../data/features/dashboard/dashboardAction";
import ErrorBanner from "./partials/ErrorBanner";
import { FreeMembershipBanner } from "./partials/FreeMembershipBanner";
import FreeMembershipBannerSkeleton
    from "./partials/FreeMembershipBannerSkeleton";
import GettingStarted from "./partials/GettingStarted";
import MembersActivity from "./partials/MembersActivity";
import NewMembers from "./partials/NewMembers";
import RecentCancellations from "./partials/RecentCancellations";
import UpcomingRenewals from "./partials/UpcomingRenewals";
import WarningBanner from "./partials/WarningBanner";

function Dashboard() {
    const dispatch = useDispatch();
    const dashboard = useSelector((state) => state?.dashboard?.data);
    const isLoading = useSelector((state) => state?.dashboard?.isLoading);
    const upcoming_renewals = useSelector((state) => state?.dashboard?.data?.upcoming_renewals);
    const new_members = useSelector((state) => state?.dashboard?.data?.new_members);
    const recent_cancelation = useSelector((state) => state?.dashboard?.data?.recent_cancelation);

    const navigate = useNavigate();

    useEffect(() => {
        // if (Object.keys(dashboard).length <= 0) {
            dispatch(getData());
        // }
    }, []);

    console.log("FreePla ::");
    console.log(dashboard?.freePlans);

    const { t } = useTranslation();
    return (
        <>

            <Page fullWidth>
                <div className="simplee_membership_main_wrap">
                    <div className="simplee_membership_container">
                        <div className="mainWrap">
                            {isLoading == false ?
                                (!dashboard?.eligibleForSubscriptions ||
                                   ( !dashboard?.is_old_installation && !dashboard?.is_app_embed) || dashboard?.freePlans) && (
                                    <div className="">
                                        {!dashboard?.eligibleForSubscriptions && (
                                            <ErrorBanner />
                                        )}
                                        {(!dashboard?.is_old_installation && !dashboard?.is_app_embed) && (
                                            <WarningBanner />
                                        )}
                                        {dashboard?.freePlans && (
                                            <FreeMembershipBanner />
                                        )}
                                    </div>
                                )
                                : (
                                    <FreeMembershipBannerSkeleton />
                                )
                            }

                            {isLoading == false ? (
                                <MembersActivity />
                            ) : (
                                <div style={{ marginTop: "20px" }}>

                                    <SkeletonBodyText lines={5} />
                                </div>
                            )}

                            <div className="tableStartWrap">
                                <div className="tableWrap">
                                    <div className="bannerWrap tableWrap">
                                        <div className='view_all'>
                                            <Text variant="headingLg" as="h3" fontWeight='regular' tone='base'>
                                                Upcoming Renewals
                                            </Text>
                                            {
                                                upcoming_renewals?.length > 0 ?
                                                    <Text alignment="end" as='legend' variant='bodyLg' textDecorationLine='liline-through'><Link onClick={() => navigate('/reports?=upcoming_reports')}>View All</Link></Text> : ""
                                            }
                                        </div>
                                        {/* <SkeletonBodyText lines= {5} /> */}
                                        {isLoading == false ? (
                                            <UpcomingRenewals />
                                        ) : (
                                            <SkeletonBodyText lines={5} />
                                        )}
                                    </div>
                                    <div className="bannerWrap tableWrap">
                                        {/* <SkeletonBodyText lines= {5} /> */}
                                        <div className='view_all'>
                                            <Text variant="headingLg" as="h3" fontWeight='regular' tone='base'>
                                                New Members
                                            </Text>
                                            {
                                                new_members?.length > 0 ?
                                                    <Text alignment="end" as='legend' variant='bodyLg' textDecorationLine='liline-through'><Link onClick={() => navigate('/reports?=newest_member_report')}>View All</Link></Text> : ""
                                            }
                                        </div>
                                        {isLoading == false ? (
                                            <NewMembers />
                                        ) : (
                                            <SkeletonBodyText lines={5} />
                                        )}
                                    </div>
                                    <div className="bannerWrap tableWrap">
                                        {/* <SkeletonBodyText lines= {5} /> */}
                                        <div className='view_all'>
                                            <Text variant="headingLg" as="h3" fontWeight='regular' tone='base'>
                                                Recent Cancellations
                                            </Text>
                                            {
                                                recent_cancelation?.length > 0 ?
                                                    <Text alignment="end" as='legend' variant='bodyLg' textDecorationLine='liline-through'><Link onClick={() => navigate('/reports?=recent_cancellation_report')}>View All</Link></Text> : ""
                                            }
                                        </div>
                                        {isLoading == false ? (
                                            <RecentCancellations />
                                        ) : (
                                            <SkeletonBodyText lines={5} />
                                        )}
                                    </div>
                                </div>
                                <div className="bannerWrap">


                                    {/* <SkeletonBodyText lines={8} />
                                    <SkeletonBodyText lines={8} />
                                    <SkeletonBodyText lines={8} /> */}

                                    <GettingStarted />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Page>
        </>
    );
}

export default Dashboard;
