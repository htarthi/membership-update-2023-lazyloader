import { Button, Checkbox, Select, Text, TextField } from "@shopify/polaris";
import React, { useCallback, useState, useEffect } from "react";
import CollapsibleAccordion from "../../../../GlobalPartials/CollapsibleAccordion/CollapsibleAccordion";
import CommanLabel from "../../../../GlobalPartials/CommanInputLabel/CommanLabel";
import { useSelector, useDispatch } from "react-redux";
import { AddLength, DeleteLength, UpdateLength, deletedUpdate } from "../../../../../data/features/plansDetails/plansDetailsSlice";
import PriceLengthSwitchOptions from "./PriceLengthSwitchOptions";
import DeleteModal from "../../../../GlobalPartials/DeleteModal/DeleteModal";
import { updateTiers } from "../../../../../data/features/plans/plansSlice";

export default function PriceLengths() {
    const dispatch = useDispatch();

    // redux data
    const help$ = useSelector((state) => state?.plansDetail?.help);
    const selectedIndex$ = useSelector((state) => state?.plansDetail?.selectedIndex);
    const plandetail$ = useSelector(
        (state) => state?.plansDetail?.data?.plan_groups[selectedIndex$]
    );
    const membershipLength$ = useSelector(
        (state) => state?.plansDetail?.data?.plan_groups[selectedIndex$]?.membershipLength
    );
    const errors$ = useSelector((state) => state?.plansDetail?.errors);
    const updated_tiers$ = useSelector((state) => state?.plans?.updated_tiers);
    const shop$ = useSelector((state) => state?.plans?.data?.shop);
    const errorMembershipLenght = `data.tiers.${selectedIndex$}.membershipLength`
    const [open, setOpen] = useState(membershipLength$?.length - 1);
    const [drop, setDrop] = useState(true);
    const handleToggle = useCallback(
        (id) => {
            open !== id ? setOpen(id) : setOpen();
        }, [open]);

    // lengths input handle change event
    const addId = []
    const [checkId, setCheckId] = useState([])
    const handleLengthChange = useCallback(
        (id, index, value, name) => {
            const updatedFields =
                name === "trial_available" ?
                    {
                        is_onetime_payment: false,
                        trial_available: value,
                    }
                    : name === "is_onetime_payment" ?
                        {
                            is_onetime_payment: value,
                            trial_available: false,
                        }
                        : name === 'trial_days' ?
                            {
                                trial_days: value,
                                pricing2_after_cycle: value
                            }
                            :
                            {
                                [name]: value,
                            };

            dispatch(UpdateLength({ index, updatedFields }));
            dispatch(updateTiers(plandetail$?.tier_id));
            !checkId?.includes(index) ? setCheckId([...checkId, index]) : ''
        },
        [checkId, plandetail$, updated_tiers$]
    );


    // period input options
    const interval = [
        { label: "Day(s)", value: "day" },
        { label: "Week(s)", value: "week" },
        { label: "Month(s)", value: "month" },
        { label: "Year(s)", value: "year" },
    ];

    // add length
    const [counter, setCounter] = useState(Math.floor(Math.random() * 120));
    const addlength = useCallback(() => {
        const newCounter = Math.floor(Math.random() * 120);
        setCounter(newCounter);
        let data = {
            id: newCounter,
            billing_interval: "month",
            billing_interval_count: 0,
            pricing_adjustment_type: "PRICE",
            pricing_adjustment_value: null,
            options: null,
            name: null,
            billing_min_cycles: null,
            billing_max_cycles: null,
            is_set_min: false,
            is_set_max: false,
            trial_available: false,
            trial_days: null,
            pricing2_adjustment_type: null,
            pricing2_adjustment_value: null,
            pricing2_after_cycle: null,
            description: null,
            is_advance_option: false,
            is_onetime_payment: false,
            trial_type: "days",
            isNew: true,
            store_credit_frequency: "all_orders",
            store_credit_amount: null,
        };
        setDrop(false);
        dispatch(AddLength(data));
        setOpen(membershipLength$?.length);
    }, [membershipLength$, counter]);

    useEffect(() => {
        setDrop(true);
        if (errors$?.length > 0) {
            errors$?.map((item) => {
                let vals = Object.keys(item);
                const myArray = vals.toString();
                let getNumber = myArray[11] ? myArray[11] : 0;
                if (myArray) {
                    const v12 = myArray.split(`data.tiers.${getNumber}.membershipLength.`)[1];
                    if (v12) {
                        const [nval] = v12.split('.').toString();
                        let nval1 = Number(nval);
                        if (drop) {
                            setOpen(nval1);
                        }
                    }
                }
            });
        }
    }, [counter]);

    // delete plan length
    const [deleteModal, setDeleteModal] = useState(false);
    const [deleteId, setDeleteId] = useState({
        deletedIndex: '',
        deletedLengthId: ''
    });

    const deleteModalOpen = useCallback(
        (id, lengthId) => {
            setDeleteModal(!deleteModal);
            setDeleteId({ ...deleteId, deletedIndex: id, deletedLengthId: lengthId })
        },
        [deleteModal, deleteId]
    );

    // delete plan
    const deletePlanLength = useCallback(() => {
        const findLengthId = membershipLength$.find(item => item.id === deleteId?.deletedLengthId);
        if (!findLengthId?.isNew) {
            dispatch(deletedUpdate({ key: 'membershipLength', id: deleteId?.deletedLengthId }))
        }
        dispatch(DeleteLength(deleteId?.deletedIndex));
        setDeleteModal(!deleteModal);
        dispatch(updateTiers(plandetail$?.tier_id));
    }, [deleteId, deleteModal]);

    return (
        <div className="price_lengths_accordion">
            <Text variant="bodyLg" as="h6" fontWeight="regular">
                How often will you charge customers to keep this membership
                active? You can add several lengths, each with their own price.
                For example, you could order a one-month membership for $20,
                then offer a three-month membership for $55 to give them a small
                discount.
            </Text>

            {/* add length */}
            <div className="add_length_block">
                {plandetail$?.membershipLength?.map((item, index) => {
                    return (
                        <CollapsibleAccordion
                            key={index}
                            title={`Length ${index + 1}`}
                            handleToggle={handleToggle}
                            id={index}
                            open={open}

                            body={
                                <div className="collapsible_body_block">
                                    {/* Length, Period & Price */}
                                    <div className="input_three_fields_wrap">
                                        {/* length */}
                                        <div className="input_fields_wrap">
                                            <TextField
                                                label={<CommanLabel label={"Length"} content={help$?.plan_length} />}
                                                type="number"
                                                placeholder="e.g. 1"
                                                value={item?.billing_interval_count ? item?.billing_interval_count : ''}
                                                onChange={(value) =>
                                                    handleLengthChange(
                                                        item?.id,
                                                        index,
                                                        value < 0 ? '0' : value,
                                                        "billing_interval_count"
                                                    )
                                                }
                                                autoComplete="off"
                                                error={item?.billing_interval_count >= 0 ? errors$?.length > 0 ? errors$[0][`${errorMembershipLenght}.${index}.billing_interval_count`] : '' : ''}
                                            />
                                        </div>
                                        {/* period */}
                                        <div className="input_fields_wrap">
                                            <Select
                                                label={
                                                    <CommanLabel
                                                        label={"Period"}
                                                        content={help$?.plan_length}
                                                    />
                                                }
                                                options={interval}
                                                value={item?.billing_interval ? item?.billing_interval : 'month'}
                                                onChange={(value) =>
                                                    handleLengthChange(
                                                        item?.id,
                                                        index,
                                                        value,
                                                        "billing_interval"
                                                    )
                                                }
                                            />
                                        </div>

                                        {/* plan */}
                                        <div className="input_fields_wrap">
                                            <TextField
                                                label={
                                                    <CommanLabel
                                                        label={"Price"}
                                                        content={help$?.plan_price}
                                                    />
                                                }
                                                type="number"
                                                value={item?.pricing_adjustment_value ? item?.pricing_adjustment_value : ''}
                                                onChange={(value) =>
                                                    handleLengthChange(
                                                        item?.id,
                                                        index,
                                                        value < 0 ? '0' : value,
                                                        "pricing_adjustment_value"
                                                    )
                                                }
                                                autoComplete="off"
                                                prefix={shop$.currency}
                                                error={!item?.pricing_adjustment_value ? errors$?.length > 0 ? errors$[0][`${errorMembershipLenght}.${index}.pricing_adjustment_value`] : '' : ''}
                                            />
                                        </div>
                                    </div>

                                    {/* Display Name & Description */}
                                    <div className="input_two_fields_wrap ms-margin-top">
                                        {/* Display Name */}
                                        <div className="input_fields_wrap">
                                            <TextField
                                                label={
                                                    <CommanLabel
                                                        label={"Display Name"}
                                                        content={help$?.display_name}
                                                    />
                                                }
                                                placeholder="e.g. Monthly Membership"
                                                type="text"
                                                value={item?.name ? item?.name : ''}
                                                onChange={(value) =>
                                                    handleLengthChange(
                                                        item?.id,
                                                        index,
                                                        value,
                                                        "name"
                                                    )
                                                }
                                                autoComplete="off"
                                                error={!item?.name || !checkId.includes(index) ? errors$?.length > 0 ? errors$[0][`${errorMembershipLenght}.${index}.name`] : '' : ''}
                                            />
                                        </div>

                                        {/* Description */}
                                        <div className="input_fields_wrap">
                                            <TextField
                                                label={
                                                    <CommanLabel
                                                        label={"Description"}
                                                        content={help$?.description}
                                                    />
                                                }
                                                placeholder="e.g. Renews monthly until cancelled"
                                                type="text"
                                                value={!item?.description ? '' : item?.description}
                                                onChange={(value) =>
                                                    handleLengthChange(
                                                        item?.id,
                                                        index,
                                                        value,
                                                        "description"
                                                    )
                                                }
                                                autoComplete="off"
                                                error={!item?.description ? errors$?.length > 0 ? errors$[0][`${errorMembershipLenght}.${index}.description`] : '' : ''}
                                            />
                                        </div>
                                    </div>

                                    {/* checkbox - Advanced membership options */}
                                    <div className="is_advance_option ms-margin-top">
                                        <Checkbox
                                            label={
                                                <CommanLabel
                                                    label={
                                                        "Advanced membership options"
                                                    }
                                                    content={""}
                                                />
                                            }
                                            checked={item?.is_advance_option ? item?.is_advance_option : false}
                                            onChange={(value) =>
                                                handleLengthChange(
                                                    item?.id,
                                                    index,
                                                    value,
                                                    "is_advance_option"
                                                )
                                            }
                                        />
                                    </div>

                                    {/* switch checkbox - options */}
                                    {item?.is_advance_option && (
                                        <div className="switch_checkbox_options_block ms-margin-top">
                                            <PriceLengthSwitchOptions
                                                item={item}
                                                index={index}
                                                handleLengthChange={
                                                    handleLengthChange
                                                }
                                            />
                                        </div>
                                    )}

                                    {/* delete - button */}
                                    <div className="delete_plan_length ms-margin-top">
                                        <Button
                                            onClick={() =>
                                                deleteModalOpen(index, item?.id)
                                            }
                                            variant="primary"
                                            tone="critical"
                                        >
                                           <span style={{ color : 'white'}}>Delete Plan Length</span> 
                                        </Button>
                                    </div>
                                </div>
                            }
                            badge={
                                errors$?.length > 0 ?
                                    errors$[0][`${errorMembershipLenght}.${index}.pricing_adjustment_value`] ?
                                        `Update length before saving `
                                        : errors$[0][`${errorMembershipLenght}.${index}.name`] ?
                                            `Update length before saving `
                                            : errors$[0][`${errorMembershipLenght}.${index}.description`] ?
                                                `Update length before saving `
                                                : ''
                                    : ''
                            }
                            status={
                                errors$?.length > 0 ?
                                    errors$[0][`${errorMembershipLenght}.${index}.pricing_adjustment_value`] ?
                                        'critical'
                                        : errors$[0][`${errorMembershipLenght}.${index}.name`] ?
                                            'critical'
                                            : errors$[0][`${errorMembershipLenght}.${index}.description`] ?
                                                'critical'
                                                : ''
                                    : ''
                            }

                        // badge = {
                        //      errors$?.length > 0 ?
                        //         errors$[0][`${errorMembershipLenght}.${index}.pricing_adjustment_value`] ?
                        //         'Validation for pricing adjustment value'
                        //         : errors$[0][`${errorMembershipLenght}.${index}.name`] ?
                        //             'Validation for display name'
                        //             : errors$[0][`${errorMembershipLenght}.${index}.description`] ?
                        //                 'Validation for description'
                        //                 : ''
                        //     : ''
                        // }

                        // status = {
                        //     errors$?.length > 0 ?
                        //         errors$[0][`${errorMembershipLenght}.${index}.pricing_adjustment_value`] ?
                        //         'critical'
                        //         : errors$[0][`${errorMembershipLenght}.${index}.name`] ?
                        //             'critical'
                        //             : errors$[0][`${errorMembershipLenght}.${index}.description`] ?
                        //                 'critical'
                        //                 : 'success'
                        //     : 'success'
                        // }

                        />
                    );
                })}

                {/* add length button */}
                <div
                    className={`${plandetail$?.membershipLength?.length <= 0 &&
                        "ms-margin-top "
                        } add_length_button`}
                >
                    <Button onClick={addlength} variant='primary'>
                        <span style={{ color : 'white'}}>Add Length </span> 
                    </Button>
                    
                </div>
            </div>

            {/* delete modal */}

            <DeleteModal
                active={deleteModal}
                setDeleteModal={setDeleteModal}
                deleteMethod={deletePlanLength}
            />
        </div>
        
    );
}
