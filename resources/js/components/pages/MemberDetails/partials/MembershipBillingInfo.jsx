import { ActionList, Button, LegacyCard, Popover, Text } from '@shopify/polaris'
import React, { useCallback, useState, useEffect } from 'react'
import card from '../../../../../images/CreditCards.svg'
import visa from '../../../../../images/static/cards/visa.svg'
import americanexpress from '../../../../../images/static/cards/americanexpress.svg'
import dinersclub from '../../../../../images/static/cards/dinersclub.svg'
import discover from '../../../../../images/static/cards/discover.svg'
import interac from '../../../../../images/static/cards/interac.svg'
import mastercard from '../../../../../images/static/cards/mastercard.svg'
import shoppay from '../../../../../images/static/cards/shoppay.svg'
import paypal from '../../../../../images/static/cards/paypal.svg'
import gpay from '../../../../../images/static/cards/gpay.svg'
import applepay from '../../../../../images/static/cards/applepay.svg'
import jcb from '../../../../../images/static/cards/jcb.svg'





import { useSelector, useDispatch } from 'react-redux';
import EditBillingInfo from './EditBillingInfo';
import { subscribeUpdate } from '../../../../data/features/memberDetails/membersDetailsAction';
import { useParams } from "react-router-dom";

export default function MembershipBillingInfo() {

    const contract = useSelector((state) => state.memberDetails?.data?.contract);
    const memberDetailsData$ = useSelector((state) => state.memberDetails?.data);
    const dispatch = useDispatch();
    const { id } = useParams();

    // ----- membership billing model ------
    const [modalOpen, setModalOpen] = useState(false);

    // Billing Information edit popover
    const [popoverActive, setPopoverActive] = useState(false);

    const togglePopoverActive = useCallback(
        () => setPopoverActive((popoverActive) => !popoverActive),
        []);


    const [Img, setImg] = useState("");
    const [text, setText] = useState("");


    const getPaymentInfo = ()=>{
        switch (contract?.payment_method) {
            case "google_pay":
                setImg(gpay);
                setText(contract?.cc_brand.charAt(0).toUpperCase() + contract?.cc_brand.slice(1).replace(/_/g, ' ') + " ending in " + contract?.cc_lastDigits.toString().padStart(4, "0"));
                break;
            case "apple_pay":
                setImg(applepay);
                setText(contract?.cc_brand.charAt(0).toUpperCase() + contract?.cc_brand.slice(1).replace(/_/g, ' ') + " ending in " + contract?.cc_lastDigits.toString().padStart(4, "0"))
                break;
            case "shop_pay":
                setImg(shoppay);
                setText("Shop Pay")
                break;
            case "paypal":
                setImg(paypal);
                setText("Account : " + contract?.paypal_account)
                break;
            case "credit_card":
                switch (contract?.cc_brand) {
                    case "visa":
                        setImg(visa);
                        break;
                    case "mastercard":
                        setImg(mastercard);
                        break;
                    case "american_express":
                        setImg(americanexpress);
                        break;
                    case "discover":
                        setImg(discover);
                        break;
                    case "diners_club":
                        setImg(dinersclub);
                        break;
                    case "jcb":
                        setImg(jcb);
                        break;
                    default:
                        setImg(card)
                        break;
                }
                setText(contract?.cc_brand.charAt(0).toUpperCase() + contract?.cc_brand.slice(1).replace(/_/g, ' ') + " ending in " + contract?.cc_lastDigits.toString().padStart(4, "0"))
                break;
            default:
                setImg(card)
                setText(contract?.cc_brand.charAt(0).toUpperCase() + contract?.cc_brand.slice(1).replace(/_/g, ' ') + " ending in " + contract?.cc_lastDigits.toString().padStart(4, "0"))
                break;
        }

    }



    useEffect(() => {
        getPaymentInfo();
    }, [])
    // Send billing information reset email
    const sendBillinInfo = useCallback(() => {
        const subscribeData = {
            data: {
                contract_id: id,
                ...memberDetailsData$,
                type: "updatePaymentDetailEmail"
            }
        }
        dispatch(subscribeUpdate({ id, subscribeData }))
    }, [])

    // {content: 'Edit details', onAction: () => setModalOpen(true)},
    return (
        <div className='ms_billing_info_block ms-margin-top main_box_wrap'>
            <LegacyCard>

                {/* Heading & Edit */}
                <div className='edit_header_block'>
                    <Text variant="headingMd" as="h6" fontWeight='medium'>Billing Information</Text>
                    <Popover
                        active={popoverActive}
                        activator={<Button  onClick={togglePopoverActive} variant="plain">Edit</Button>}
                        autofocusTarget="first-node"
                        onClose={togglePopoverActive}
                    >
                        <ActionList
                            actionRole="menuitem"
                            items={[
                                { content: 'Send billing information reset email', onAction: () => sendBillinInfo() }
                            ]}
                        />
                    </Popover>
                </div>

                {/* billing details */}
                <div className='billing_details_wrap'>

                    {/* name */}
                    <div className='billing_details_col ms-margin-top'>
                        <Text variant="bodyLg" as="h6">Name</Text>
                        <div className='ms-margin-top-ten'>
                            <Text variant="bodyLg" as="h6" fontWeight='regular'>{contract?.cc_name}</Text>
                        </div>
                    </div>

                    {/* Payment method */}
                    <div className='billing_details_col ms-margin-top'>
                        <Text variant="bodyLg" as="h6">Payment method</Text>

                        {contract?.payment_method == "paypal" ?
                            <>
                                <div className='ms-margin-top-ten payment_methos_block' >


                                    <div className='paypal-img'>
                                        <img src={paypal} alt='card' />
                                    </div>

                                </div>
                                <Text variant="bodyLg" as="h6" fontWeight='regular'>{text}</Text>
                            </>
                            :
                            <div className='ms-margin-top-ten payment_methos_block'>
                                <img src={Img} alt='card' />
                                <Text variant="bodyLg" as="h6" fontWeight='regular'>{text}</Text>
                            </div>

                        }
                    </div>

                    {/* Expiry date */}
                    {
                        contract?.cc_expiryMonth ?
                            <div className='billing_details_col ms-margin-top'>
                                <Text variant="bodyLg" as="h6">Expiry date</Text>
                                <div className='ms-margin-top-ten'>
                                    <Text variant="bodyLg" as="h6" fontWeight='regular'>{contract?.cc_expiryMonth}/{String(contract?.cc_expiryYear).slice(-2)}</Text>
                                </div>
                            </div>
                        : ''
                    }
                </div>
            </LegacyCard>

            {/* edit modal */}
            <EditBillingInfo  />
        </div>
    );
}
