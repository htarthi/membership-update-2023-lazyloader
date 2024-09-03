import { LegacyCard, Text } from '@shopify/polaris'
import React, { useState, useCallback } from 'react'
import EditComp from './EditComp'
import EditMembersDetailModal from './EditMembersDetailModal';
import product from '../../../../../images/product.png';
import { useSelector } from 'react-redux';

export default function MemberShipDetailComp() {

    const lineItems = useSelector((state) => state.memberDetails?.data?.contract?.lineItems);
    const contract = useSelector((state) => state.memberDetails?.data?.contract);

    // ----- membership details model ------
    const [modalOpen, setModalOpen] = useState(false);

    // open modal
    const editHandleEvent = useCallback(() => {
        setModalOpen(true);
    }, [modalOpen])

    return (
        <>
            <div className='membership_detail_box_wrap ms-margin-top main_box_wrap'>
                <LegacyCard>

                    {/* Heading & Edit */}
                    <EditComp title={"Membership Details"} editHandleEvent={editHandleEvent}  />

                    {/* membership detail */}
                    {
                        lineItems?.length > 0 &&
                        <div className='membership_detail_row ms-margin-top'>

                            {/* product image */}
                            <div className='membership_product_picture'>
                                <img src={lineItems[0]?.sh_product.product_image && !(lineItems[0]?.sh_product.product_image.includes("no-image-box.png")) ? lineItems[0]?.sh_product.product_image : product} alt='product image' />
                            </div>

                            {/* membership product detail */}
                            <div className='membership_product_detail_row'>
                                {/* Product */}
                                <div className='membership_product_col'>
                                    <Text variant="bodyLg" as="h6">Product</Text>
                                    <Text variant="bodyLg" as="h6" fontWeight='regular'>
                                        {lineItems[0]?.title}
                                        {/* <Text as='span' fontWeight='regular' color='subdued'>{lineItems[0]?.shopify_variant_title}</Text> */}
                                    </Text>
                                </div>
                                {/* Recurring code */}
                                {contract?.is_onetime_payment != 1 && (
                                    <div className='membership_product_col'>
                                        <Text variant="bodyLg" as="h6">Recuring Amount</Text>
                                        <Text variant="bodyLg" as="h6" fontWeight='regular'>
                                            { contract?.currency_code + " " + lineItems[0]?.discount_amount}
                                        </Text>
                                </div>
                                )}

                                {/* Discount code */}
                                {contract?.is_onetime_payment != 1 && lineItems[0]?.discount_code &&  (
                                    <div className='membership_product_col'>
                                        <Text variant="bodyLg" as="h6">Discount code</Text>
                                        <Text variant="bodyLg" as="h6" fontWeight='regular'>{lineItems[0]?.discount_code}</Text>
                                    </div>)}


                                {/* Shipping Fees */}
                                {contract?.is_onetime_payment != 1 && contract?.is_physical_product == 1 && (
                                    <div className='membership_product_col'>
                                        <Text variant="bodyLg" as="h6">Shipping fee</Text>
                                        <Text variant="bodyLg" as="h6" fontWeight='regular'>{contract?.delivery_price}</Text>
                                    </div>
                                )}

                                {/* Renewal / Billing date */}
                                {contract?.is_onetime_payment != 1 && (
                                <div className='membership_product_col'>
                                    <Text variant="bodyLg" as="h6">Renewal / Billing date</Text>
                                    <Text variant="bodyLg" as="h6" fontWeight='regular'> {contract?.next_processing_date?.replace('00:01', '')}</Text>
                                </div>
                                )}
                                {/* Billing frequency */}
                                {contract?.is_onetime_payment != 1 && (
                                    <div className='membership_product_col'>
                                        <Text variant="bodyLg" as="h6">Billing frequency</Text>
                                        <Text variant="bodyLg" as="h6" fontWeight='regular'>Every {contract?.billing_interval_count} {contract?.billing_interval}(s)</Text>
                                    </div>
                                )}
                            </div>

                        </div>
                    }

                </LegacyCard>

                {/* Edit membershi detail modal */}
                {
                    lineItems?.length > 0 &&
                    <EditMembersDetailModal modalOpen={modalOpen} setModalOpen={setModalOpen} />
                }
            </div>
        </>

    )
}
