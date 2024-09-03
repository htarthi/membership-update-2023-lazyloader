import React from 'react'
import Member from '../../../../../images/member.svg'
import Wallet from '../../../../../images/Wallet.svg'
import Graph from '../../../../../images/Graph.svg'
import Chart from '../../../../../images/Chart.svg'
import ArrowDown from '../../../../../images/arrow-down_minor.svg'
import ArrowUpM from '../../../../../images/arrow-up_minor.svg'
import ArrowUp from '../../../../../images/arrorUp.svg'
import { SkeletonDisplayText, Text } from '@shopify/polaris'
import { useSelector } from 'react-redux'

const MembersActivity = () => {

    const dashboard = useSelector((state) => state?.dashboard?.data);
    return (
        <div className='bannerWrap'>
            <Text variant='headingLg' as="h3" fontWeight='regular' tone='base'>
                Membership Activity
            </Text>
            <div className='cardWrap'>
                <div className='membersCard'>

                    <div className='CardHead'>
                        <div className='memberImg'>
                            <img src={Member} alt="Member" />
                        </div>

                        {
                            dashboard.active_memberships_percentage.is_increase !== null &&
                            <div className={`timePer ${dashboard.active_memberships_percentage.is_increase == 1 ? "upTimePer" : ""}`}>

                                <div className='imgWrap'>

                                    {dashboard.active_memberships_percentage.is_increase == 1 ? <img src={ArrowUpM} alt="" /> : <img src={ArrowDown} alt="" />}
                                </div>
                                <Text variant="bodyLg" as="h6" >
                                    <span> {dashboard.active_memberships_percentage.percentage}% </span>    Last week
                                </Text>
                            </div>
                        }

                    </div>
                    <div className='CardBody'>
                        <div className='activityText'>
                            <Text variant="heading2xl" as="h4" fontWeight='regular' tone='base'>
                                {dashboard.active_memberships_total}
                            </Text>
                            <Text variant="bodyLg" as="h6">
                                Active Memberships
                            </Text>
                        </div>

                        {/* <div className='arrorImg'>
                            <img src={ArrowUp} alt="ArrowUp" />

                        </div> */}
                    </div>
                </div>
                <div className='membersCard'>
                    <div className='CardHead'>
                        <div className='memberImg'>
                            <img src={Wallet} alt="Member" />
                        </div>

                        {
                            dashboard.membership_spend_percentage.is_increase !== null &&
                            <div className={`timePer ${dashboard.membership_spend_percentage.is_increase == 1 ? "upTimePer" : ""}`}>

                                <div className='imgWrap'>

                                    {dashboard.membership_spend_percentage.is_increase == 1 ? <img src={ArrowUpM} alt="" /> : <img src={ArrowDown} alt="" />}

                                </div>
                                <Text variant="bodyLg" as="h6" >
                                    <span> {dashboard.membership_spend_percentage.percentage}% </span>    Last week
                                </Text>
                            </div>
                        }
                    </div>
                    <div className='CardBody'>
                        <div className='activityText'>
                            <Text variant="heading2xl" as="h4" fontWeight='regular' tone='base'>
                                {dashboard.shop.currency_symbol} {dashboard.membership_spend}

                            </Text>
                            <Text variant="bodyLg" as="h6">
                                Membership Spend
                            </Text>
                        </div>

                        {/* <div className='arrorImg'>
                            <img src={ArrowUp} alt="ArrowUp" />
                        </div> */}
                    </div>
                </div>
                <div className='membersCard'>
                    <div className='CardHead'>
                        <div className='memberImg'>
                            <img src={Graph} alt="Member" />
                        </div>
                        {
                            dashboard.avg_lifetime_value_percentage.is_increase !== null &&
                            <div className={`timePer ${dashboard.avg_lifetime_value_percentage.is_increase !== 1 ? "upTimePer" : "downTimePer"}`}>

                                <div className='imgWrap'>

                                    {dashboard.avg_lifetime_value_percentage.is_increase == 0 ? <img src={ArrowUpM} alt="" /> : <img src={ArrowDown} alt="" />}
                                </div>
                                <Text variant="bodyLg" as="h6">
                                    <span> {dashboard.avg_lifetime_value_percentage.percentage}% </span>    Last week
                                </Text>
                            </div>
                        }
                    </div>
                    <div className='CardBody'>
                        <div className='activityText'>
                            <Text variant="heading2xl" as="h4" fontWeight='regular' tone='base'>
                            {dashboard.shop.currency_symbol} {dashboard.avg_lifetime_value}
                            </Text>
                            <Text variant="bodyLg" as="h6">
                                Average Lifetime Value
                            </Text>
                        </div>

                        {/* <div className='arrorImg'>
                            <img src={ArrowUp} alt="ArrowUp" />
                        </div> */}
                    </div>

                </div>
            </div>
        </div>
    );
}

export default MembersActivity
