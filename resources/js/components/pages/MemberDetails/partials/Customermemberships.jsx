import { LegacyCard, Select, Text } from '@shopify/polaris'
import React, { useCallback } from 'react'
import { useDispatch } from 'react-redux';
import { useSelector } from 'react-redux';
import { useNavigate, useParams } from 'react-router-dom';
import { subscriberEdit } from '../../../../data/features/memberDetails/membersDetailsAction';

export default function Customermemberships() {

    const contractsList$ = useSelector((state) => state.memberDetails?.data?.contracts_list);
    const members = useSelector((state) => state.members?.data?.memberships);
    const selectedContract$ = useSelector((state) => state.members?.selectedContract);

    const navigate = useNavigate();
    const dispatch = useDispatch();
    const { id } = useParams();

    // select handle change...
    const handleSelectChange = useCallback(
        (value) => {
            navigate(`/members/${value.split('#')[1]}/edit`);
            dispatch(subscriberEdit({ page: members?.current_page, id: parseInt(value.split('#')[1]) }))
        }, [selectedContract$]);


    // list...
    const list =
        contractsList$?.length > 0 ?
            contractsList$?.map((item, key) => {
                return { label: `#${item.member_number.toString().padStart(6, "0")} - ${item.status.charAt(0).toUpperCase() + item.status.slice(1)}${item.status == 'active' ? " - " + new Date(item.next_processing_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' }) : ""}`, value: `#${item.id}` }
            })
            :
            ''
    return (
        <div className='customer_memberships_block ms-margin-top main_box_wrap'>
            {contractsList$?.length > 1 ? (
                <LegacyCard>
                    {/* Heading & Edit */}
                    <div className='edit_header_block'>
                        <Text variant="headingMd" as="h6" fontWeight='medium'>Other Memberships</Text>
                    </div>

                    <div className='customer_info_dropdown ms-margin-top'>
                        <Select
                            options={[...list]}
                            onChange={handleSelectChange}
                            value={`#${id}`}
                        />
                        <div className='ms-margin-top-four'>
                            <Text variant="bodyLg" as="h6" fontWeight='regular' tone='subdued' >Switch between this customer’s other memberships</Text>
                        </div>
                    </div>

                </LegacyCard>
            ) : (
                ''
            )}

        </div>
    );
}
