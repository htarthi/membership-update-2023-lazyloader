import { Text } from '@shopify/polaris'
import React, { useCallback, useState, useEffect } from 'react'
import CollapsibleAccordion from '../../../GlobalPartials/CollapsibleAccordion/CollapsibleAccordion';
import PriceLengths from './AccordionBody/PriceLengths';
import RestrictedContent from './AccordionBody/RestrictedContent';
import DiscountFreeShipping from './AccordionBody/DiscountFreeShipping';
import POSDiscounts from './AccordionBody/POSDiscounts';
import AdditionalQuestions from './AccordionBody/AdditionalQuestions';
import { useSelector } from 'react-redux';

export default function Accordion({ open, handleToggle }) {

    const selectedIndex$ = useSelector((state) => state.plansDetail?.selectedIndex);
    const plandetail$ = useSelector(
        (state) => state.plansDetail?.data?.plan_groups[selectedIndex$]
    );
    const [pos, setPos] = useState(0);
    const [que, setQue] = useState(0);
    const [autodis, setAutodis] = useState(0);

    const handleToggle2 = useCallback(
        (id) => {
            autodis !== id ? setAutodis(id) : setAutodis();
        }, [autodis]);
    const handleToggle3 = useCallback(
        (id) => {
            pos !== id ? setPos(id) : setPos();
        }, [pos]);
    const handleToggle4 = useCallback(
        (id) => {
            que !== id ? setQue(id) : setQue();
        }, [que]);

    const AdditionalQuestions$ = useSelector(
        (state) => state.plansDetail?.data
    );

    

    const errors$ = useSelector((state) => state?.plansDetail?.errors);
    const [error, setError] = useState([]);
    const errors = [];
    useEffect(() => {
        if (errors$?.length > 0) {
            Object.keys(errors$[0])?.map((lable, index) => {
                let splitLabel = lable?.split('.')[3];
                if (!error?.includes(splitLabel)) {
                    errors.push(splitLabel);
                    setError(errors);
                }
            })
        }
        if (errors?.includes('formFields') && plandetail$?.formFields?.length > 0) {
            setQue(4);
        }
        if (errors?.includes('discounts') && plandetail$?.discounts?.length > 0) {
            setPos(3);
        }
        if (errors?.includes('automaticError') && errors$?.length > 0 && errors$[0][`data.tiers.${selectedIndex$}.automaticError`]) {
            setAutodis(2);
        }
    }, []);
    return (
        <>
            <CollapsibleAccordion
                title="Prices & Lengths"
                handleToggle={handleToggle}
                id={0}
                open={open}
                body={<PriceLengths />}
                badge={plandetail$?.membershipLength?.length > 0 ? (plandetail$?.membershipLength?.length > 1 ? `${plandetail$?.membershipLength?.length} lengths` : `${plandetail$?.membershipLength?.length} length`) : 'No Lengths'}
                status={plandetail$?.membershipLength?.length > 0 ? 'success' : (errors$?.length > 0 && errors$[0][`data.tiers.${selectedIndex$}.membershipLength`]) ? 'critical' : 'attention'}
            />
            <CollapsibleAccordion
                title="Restricted Content"
                handleToggle={handleToggle}
                id={1}
                open={open}
                body={<RestrictedContent />}
                badge={plandetail$?.rules?.length > 0 ? (plandetail$?.rules?.length > 1 ? `${plandetail$?.rules?.length} restrictions` : `${plandetail$?.rules?.length} restriction`) : 'No restrictions '}
                status={plandetail$?.rules?.length > 0 ? 'success' : 'attention'}
            />
            <CollapsibleAccordion
                title="Discount & Free Shipping"
                handleToggle={handleToggle2}
                id={2}
                open={autodis}
                body={<DiscountFreeShipping />}
                badge={plandetail$?.discount_type == "1" ?  'No Discounts' : plandetail$?.discount_type == "3" ? 'Discount Code Method' : 'Automatic Checkout Discount Method'}
                status={plandetail$?.discount_type == "1" ? 'attention' : plandetail$?.discount_type == "2"  ? (errors$?.length > 0 && errors$[0][`data.tiers.${selectedIndex$}.automaticError`]) ? 'critical' : 'success' : 'success'}
            />
            <CollapsibleAccordion
                title="Point of Sale Discounts"
                handleToggle={handleToggle3}
                id={3}
                open={pos}
                body={<POSDiscounts />}
                badge={plandetail$?.discounts?.length > 0 ? (plandetail$?.discounts?.length > 1 ? `${plandetail$?.discounts?.length} discounts` : `${plandetail$?.discounts?.length} discount`) : 'No discounts '}
                status={error?.includes('discounts') ? 'critical' : plandetail$?.discounts?.length > 0 ? 'success' : 'attention'}
            />
            <CollapsibleAccordion
                title="Additional Questions"
                handleToggle={handleToggle4}
                id={4}
                open={que}
                body={<AdditionalQuestions />}
                badge={AdditionalQuestions$?.formFields?.length > 0 ? (AdditionalQuestions$?.formFields?.length > 1 ? `${AdditionalQuestions$?.formFields?.length} questions` : `${AdditionalQuestions$?.formFields?.length} question`) : 'No questions '}
                status={error?.includes('formFields') ? 'critical' : AdditionalQuestions$?.formFields?.length > 0 ? 'success' : 'attention'}
            />
        </>
    )
}
