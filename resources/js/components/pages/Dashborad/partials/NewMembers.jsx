import {
    Text,
    LegacyCard,
    DataTable,
    SkeletonBodyText,
    Link,
} from '@shopify/polaris';
import React ,{useCallback  } from 'react';
import Member from '../../../../../images/NoDataMembers.svg'
import { useSelector } from 'react-redux';
import { useNavigate } from "react-router-dom";

const NewMembers = () => {

    const dashboard = useSelector((state) => state?.dashboard?.data?.new_members);
    const shop = useSelector((state)=>state?.dashboard?.data?.shop);
    const domain = useSelector((state) => state?.dashboard?.data?.shop?.myshopify_domain);


    const navigate = useNavigate();


    const clickActionButton = useCallback((e, shopify_order_id) => {
        e.stopPropagation();
       const  url = "https://admin.shopify.com/store/" + (domain?.replace(".myshopify.com", "") || "") + "/orders/" + shopify_order_id
        window.open(url, "_blank", "noreferrer");

    });

    const rows = dashboard.map((item, index) => {
        const options = { year: 'numeric', month: 'short', day: '2-digit' };
        const tag = item.billing_interval_count > 1 ? 's' : ''
        return [new Date(item.created_at).toLocaleDateString('en-US', options),
        <Link onClick={() => navigate(`/members/${item.id}/edit?=dashboard`)}>{item.first_name + " " + item.last_name}</Link>,
        item.name,
        <Link onClick={(e)=>clickActionButton(e,item.shopify_order_id)}>{item.shopify_order_name}</Link>
    ]
    })


    return (
        <>

            <div >

                {
                    dashboard.length > 0 ?

                        <LegacyCard>
                            <DataTable
                                columnContentTypes={[
                                    'text',
                                    'text',
                                    'text',
                                    'numeric',
                                ]}
                                headings={[
                                    'Date',
                                    'Customer Name',
                                    'Plan/Tier',
                                    'View Order'
                                ]}
                                rows={rows}
                            />
                        </LegacyCard> :
                        <div className='NoData'>
                            <img src={Member} alt="No Data" />
                            <Text variant="headingMd" as="h6" fontWeight='regular' tone='base'>
                                Your newest members will  appear in this list
                            </Text>

                        </div>}
            </div>
        </>

    );
};

export default NewMembers;
