import { Button, LegacyCard, Link, Text } from '@shopify/polaris'
import React, { useState, useCallback } from 'react'
import EditMembersCustomer from './EditMembersCustomer'
import { useSelector } from 'react-redux';
import { toast } from 'react-toastify';

export default function MembershipCustomer() {

    const customer$ = useSelector((state) => state.memberDetails?.data?.contract?.customer);
    const contract$ = useSelector((state) => state.memberDetails?.data?.contract);

    const domain  = useSelector((state)=>state.memberDetails.data?.shop);

    // ----- membership edit model ------
    const [modalOpen, setModalOpen] = useState(false);

    // open modal
    const editHandleEvent = useCallback(() => {
        setModalOpen(true);
    }, [modalOpen])

    const copytoClipboard = useCallback((email) => {
        navigator.clipboard.writeText(email)
        toast.success("Email copied succesfully");
    }, [customer$])

    return (
        <div className='membership_customers_detail_block ms-margin-top main_box_wrap'>
            <LegacyCard>

                {/* Heading & Edit */}
                <div className='edit_header_block'>
                    <Text variant='headingMd' as="h6" fontWeight='medium'>Customer</Text>
                    <Button  onClick={editHandleEvent} variant="plain">Edit</Button>
                </div>

                {/* customers name */}
                <div className='customers_name_wrap ms-margin-top-bottom'>

                <Link variant="bodyLg" as="h6" target="_blank" url={"https://admin.shopify.com/store/"+ domain?.name + "/customers/"+ contract$?.shopify_customer_id} >{customer$?.first_name} {customer$?.last_name}</Link>
                </div>

                <div className='contact_info_block'>
                    <Text variant="bodyLg" as="h6">Contact information</Text>

                    {/* email & number */}
                    <div className='email_number_wrap ms-margin-top-ten'>
                        <Link variant="bodyLg" onClick={() => copytoClipboard(customer$?.email)} as="h6" > {customer$?.email}</Link>
                        {/* <a className='link'>{customer$?.email}</a> */}
                        <div className='ms-margin-top-four'>
                            <Text variant="bodyLg" as="h6" fontWeight='regular' >{contract$?.ship_phone}</Text>
                        </div>
                    </div>

                    {
                        contract$?.ship_address1 || contract$?.ship_city || contract$?.ship_country ?
                            <div className='default_address ms-margin-top'>
                                <Text variant="bodyLg" as="h6">Shipping address</Text>

                                <div className='ms-margin-top-ten'>
                                    <div className='ms-margin-top-four'><Text variant="bodyLg" as="h6" fontWeight='regular' ></Text></div>
                                    <div className='ms-margin-top-four'><Text variant="bodyLg" as="h6" fontWeight='regular' >{customer$?.first_name} {customer$?.last_name}</Text> </div>
                                    <div className='ms-margin-top-four'><Text variant="bodyLg" as="h6" fontWeight='regular' >{contract$?.ship_address1}</Text> </div>
                                    <div className='ms-margin-top-four'><Text variant="bodyLg" as="h6" fontWeight='regular' >{contract$?.ship_city}</Text> </div>
                                    <div className='ms-margin-top-four'><Text variant="bodyLg" as="h6" fontWeight='regular' >{contract$?.ship_province}</Text> </div>
                                    <div className='ms-margin-top-four'><Text variant="bodyLg" as="h6" fontWeight='regular' >{contract$?.ship_country}</Text> </div>
                                    <div className='ms-margin-top-four'><Text variant="bodyLg" as="h6" fontWeight='regular' >{contract$?.ship_zip}</Text> </div>
                                </div>
                            </div>
                            :
                            ''
                    }

                </div>
            </LegacyCard>

            {/* edit modal */}
            <EditMembersCustomer modalOpen={modalOpen} setModalOpen={setModalOpen} />
        </div>
    );
}
