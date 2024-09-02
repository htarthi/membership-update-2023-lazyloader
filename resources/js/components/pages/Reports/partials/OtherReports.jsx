import { Card, SkeletonThumbnail, Text } from '@shopify/polaris'
import { BarChart, LineChart, SimpleBarChart } from '@shopify/polaris-viz'
import React, { useEffect } from 'react'
import { useDispatch } from 'react-redux'
import { useSelector } from 'react-redux'
import { activePlans, getData } from '../../../../data/features/reports/reportAction'

const OtherReports = () => {
    const reportsData = useSelector((state) => state?.reports?.data?.other_reports);




    const dispatch = useDispatch()
    useEffect(() => {
        // dispatch(getData("null"));
        dispatch(activePlans());

        console.log("==+++++++++++++++++++++++++++")
    }, [])

    // format date
    const lineChartFormatDate = (dateString) => {
        const date = new Date(dateString);
        const options = { month: 'short', day: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    }
    const barChartformatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    };

    // chartData from api
    const activeMembershipsChart = reportsData?.active_memberships?.key?.map((key, index) => ({
        key: reportsData?.active_memberships.key?.[index],
        value: reportsData?.active_memberships.values?.[index]
    }))
    const newMembershipsChart = reportsData?.new_memberships?.key?.map((key, index) => ({
        key: reportsData?.new_memberships.key?.[index],
        value: reportsData?.new_memberships.values?.[index]
    }))
    const cancelledSubscriptionsChart = reportsData?.cancelled_subscriptions?.key?.map((key, index) => ({
        key: reportsData?.cancelled_subscriptions.key?.[index],
        value: reportsData?.cancelled_subscriptions.values?.[index]
    }))

    const lineChartData = [
        {
            name: 'Active Memberships',
            color: [
                { offset: 0, color: '#9565FF' },
                { offset: 100, color: '#007CC2' },
            ],
            data: activeMembershipsChart
        }
    ];

    const NewMembershipsData = [
        {
            name: 'New Memberships',
            color: [
                { offset: 0, color: '#9565FF' },
                { offset: 100, color: '#007CC2' },
            ],
            data: newMembershipsChart

        }
    ];

    const CancellationsData = [
        {
            name: 'Cancellations',
            color: [
                { offset: 0, color: '#9565FF' },
                { offset: 100, color: '#007CC2' },
            ],
            data: cancelledSubscriptionsChart

        }
    ];

    const CancellationReasonsData = [
        {
            name: 'Cancellation Reasons',
            color: [
                { offset: 0, color: '#9565FF' },
                { offset: 100, color: '#007CC2' },
            ],
            data: [
                {
                    key: 'Too expensive',
                    value: 44,
                },
                {
                    key: 'Not enough options for customization',
                    value: 275,
                },
                {
                    key: 'I found another app that I like better',
                    value: 174,
                },
                {
                    key: 'Other',
                    value: 423,
                },
            ]

        }
    ];

    const chartData = [
        {
            head: "Active Memberships",
            // subHead: "Data Visualization",
            type: "line",
            data: { lineChartData }
        },
        {
            head: "New Memberships",
            // subHead: "Data Visualization",
            type: "bar",
            flag: 'active',
            data: { NewMembershipsData }
        },
        {
            head: "Cancellations",
            // subHead: "Data Visualization",
            type: "bar",
            flag: 'cancel',
            data: { CancellationsData }
        },
        // {
        //     head: "Cancellation Reasons",
        //     subHead: "Data Visualization",
        //     type: "simpleBar",
        //     data: { CancellationReasonsData }
        // },
    ]
    return (
        <>
            <div className='chartContainer'>
                {
                    chartData?.map((data, index) => {
                        return (
                            Object.keys(reportsData).length == 0 ?
                                <SkeletonThumbnail size="large" />
                                : (
                                    <div className='testClass' style={{ width : '100%' }}>
                                        <Card key={index} padding='500' roundedAbove='md'>
                                            <Text variant="headingLg" as="h5" fontWeight='medium'>
                                                {data?.head}
                                            </Text>
                                            <Text variant="headingMd" as="h6" fontWeight='medium'> 
                                                {data?.subHead}
                                            </Text>
                                            {
                                                data?.flag === "active" && data?.type === "bar" && (
                                                    <BarChart
                                                        theme='Light'
                                                        showLegend={false}
                                                        data={NewMembershipsData}
                                                    />
                                                )
                                            }
                                            {
                                                data?.flag === "cancel" && data?.type === "bar" && (
                                                    <BarChart
                                                        theme='Light'
                                                        showLegend={false}
                                                        data={CancellationsData}
                                                    />
                                                )
                                            }
                                            {
                                                data?.type === "simpleBar" && (
                                                    <SimpleBarChart
                                                        theme='Light'
                                                        showLegend={false}
                                                        data={CancellationReasonsData}
                                                    // type='stacked'
                                                    />
                                                )
                                            }
                                            {
                                                data?.type === "line" && (
                                                    <LineChart
                                                        theme='Light'
                                                        showLegend={false}
                                                        data={lineChartData}
                                                    />
                                                )
                                            }

                                        </Card>
                                    </div>
                                )
                        )
                    })
                }
            </div>
        </>

    )
}

export default OtherReports
