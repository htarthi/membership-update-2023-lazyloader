import {
    Text,
    LegacyCard,
    DataTable,
    SkeletonBodyText,
    Link,
} from '@shopify/polaris';
import React ,{useCallback}from 'react';
import { useNavigate } from "react-router-dom";
import Renewal from '../../../../../images/Renewals.svg'
import { useSelector } from 'react-redux';


const UpcomingRenewals = () => {
    const dashboard = useSelector((state) => state?.dashboard?.data?.upcoming_renewals);
    const shop = useSelector((state)=>state?.dashboard?.data?.shop);
    const domain = shop?.domain;
    const navigate = useNavigate();


    const hourswithleadingzero = useCallback((datetimeString, userTimezone) => {

        const [datePart, timePart] = datetimeString.split(' ');
        const [year, month, day] = datePart.split('-').map(Number);
        const [hour, minute, second] = timePart.split(':').map(Number);

        // Create a Date object in UTC
        const utcDate = new Date(Date.UTC(year, month - 1, day, hour, minute, second));

        // Convert the Date object to the user's timezone
        const options = { timeZone: userTimezone };
        const formattedDate = utcDate.toLocaleString('en-US', options);

        var inputDate = new Date(formattedDate);

        // Extract date components
        var years = inputDate.getFullYear();
        var months = ("0" + (inputDate.getMonth() + 1)).slice(-2);
        var days = ("0" + inputDate.getDate()).slice(-2);
        var hours = ("0" + inputDate.getHours()).slice(-2);
        var minutes = ("0" + inputDate.getMinutes()).slice(-2);
        var seconds = ("0" + inputDate.getSeconds()).slice(-2);

        // Construct the desired format string
        var outputDateString = `${years}-${months}-${days} ${hours}:${minutes}:${seconds}`;


        return outputDateString;


    })

    const rows = dashboard.map((item, index) => {
        const options = { year: 'numeric', month: 'short', day: '2-digit',hour: '2-digit', minute: '2-digit', second: '2-digit'
       };
    //    'ss_contract_line_items.currency',
        let currency_display = item.currency_code ;
        if(!currency_display){
            currency_display = item.currency ;
        }
        return [
            hourswithleadingzero(item.next_processing_date, item.iana_timezone)
        ,
        <Link onClick={() => navigate(`/members/${item.id}/edit?=dashboard`)}>{item.first_name + " " + item.last_name}</Link>,
         item.name ,
        <Text alignment='end'>{currency_display + " " +  item.discount_amount}</Text>
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
                                    'date',
                                    'text',
                                    'text',
                                    'numeric',
                                ]}
                                headings={[
                                    'Date',
                                    'Customer Name',
                                    'Plan/Tier',
                                    'Estimated Amount',
                                ]}
                                rows={rows}
                            />
                        </LegacyCard> :
                        <div className='NoData'>
                            <img src={Renewal} alt="No Data" />
                            <Text variant="headingMd" as="h6" fontWeight='regular' tone='base'>
                                Any memberships due to renew   soon will appear in this list.
                            </Text>

                        </div>
                }
            </div>
        </>

    );
};

export default UpcomingRenewals;
