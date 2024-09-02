import { Button, Checkbox, Link, Text, TextField, RadioButton, Select, Badge } from "@shopify/polaris";
import React, { useCallback, useState, useEffect } from "react";
import CommanLabel from "../../../../GlobalPartials/CommanInputLabel/CommanLabel";
import { useSelector, useDispatch } from "react-redux";
import { UpdatePlanGroup } from "../../../../../data/features/plansDetails/plansDetailsSlice";
import { updateTiers } from "../../../../../data/features/plans/plansSlice";
import ChangeProducts from "../../../../GlobalPartials/ChangeProducts/ChangeProducts";

export default function DiscountFreeShipping() {

    const dispatch = useDispatch();
    const selectedIndex = useSelector(state => state?.plansDetail?.selectedIndex);
    const discountShipping = useSelector(state => state?.plansDetail?.data.plan_groups[selectedIndex]);
    const shop = useSelector(state => state?.plans?.data?.shop);
    const help$ = useSelector((state) => state?.plansDetail?.help);
    const [resourcePickerOpen, setResourcePickerOpen] = useState(false);
    const [productId, setProductId] = useState(null);
    const [discountCodes, setDiscountCodes] = useState();
    const errors$ = useSelector((state) => state?.plansDetail?.errors);
    const errorMembershipLenght = `data.tiers.${selectedIndex}.automatic_checkout_discount`;
    const [delcount, setdelcount] = useState(0);

    const domain = shop?.domain;
    const myshopify_domain = shop?.myshopify_domain;
    let url = "";
    if (myshopify_domain?.includes(".myshopify.com")) {
        url = "https://admin.shopify.com/store/" + (myshopify_domain?.replace(".myshopify.com", "") || "") + "/discounts";
    } else if (myshopify_domain?.includes(".com")) {
        url = "https://admin.shopify.com/store/" + (myshopify_domain?.replace(".com", "") || "") + "/discounts";
    }

    const auto_discount_options = [
        { label: '$', value: '$' },
        { label: '%', value: '%' },
    ];


    const isStringRepresentingNumber = (str) => {
        const num = Number(str);
        return !isNaN(num);
      };

    const handleDiscountChange = useCallback((value, name, index) => {
        if (index !== undefined) {
            if(name == 'collection_discount'){
                if (isStringRepresentingNumber(value)) {
                } else {
                return false;
                }
            }
            if (discountShipping?.automatic_checkout_discount) {
                const updatedDiscountCodes = discountCodes.map((item, idx) =>
                    idx === index ? { ...item, [name]: value } : item
                );
                setDiscountCodes(updatedDiscountCodes);
                if (discountShipping?.id) {
                    dispatch(UpdatePlanGroup({ id: discountShipping?.id, updatedFields: { automatic_checkout_discount: updatedDiscountCodes } }));
                }
            }
        } else {
            if(name == 'shipping_discount_code'){
                if (isStringRepresentingNumber(value)) {
                } else {
                return false;
                }
            }
            if (discountShipping?.id) {
                const updatedFields = { [name]: value };
                dispatch(UpdatePlanGroup({ id: discountShipping?.id, updatedFields }));
                dispatch(updateTiers(discountShipping?.tier_id));
            }
        }
    }, [dispatch, discountCodes, discountShipping?.id, discountShipping?.tier_id]);

    const handleResourceSelection = useCallback((resources, idToUpdate, getIndex) => {
        let collection_id = [];
        let collection_name = [];

        resources?.selection?.forEach((item) => {
            const numbersOnly = item?.id.match(/\d+/g)?.join("") || "";
            collection_id.push(numbersOnly);
            collection_name.push(item?.title || '');
        });

        const newIds = collection_id.join(',');
        const newNames = collection_name.join(', ');

        const updatedDiscountCodes = discountCodes.map(item =>
            item.id === idToUpdate
                ? { ...item, collection_name: newNames, collection_id: newIds }
                : item
        );
        setDiscountCodes(updatedDiscountCodes);
        if (discountShipping?.id) {
            dispatch(UpdatePlanGroup({
                id: discountShipping?.id,
                updatedFields: { automatic_checkout_discount: updatedDiscountCodes }
            }));
        }
        setResourcePickerOpen(false);
    }, [discountCodes, dispatch, discountShipping?.id]);


    useEffect(() => {
        if (discountShipping?.automatic_checkout_discount) {
            const updatedDiscountCodes = discountShipping.automatic_checkout_discount.map(discount => {
                const productIdsArray = discount?.collection_id ? discount?.collection_id.split(',') : [];
                const counts = new Set(productIdsArray).size;
                return {
                    ...discount,
                    counts
                };
            });
            setDiscountCodes(updatedDiscountCodes);
        }
    }, [discountShipping]);


    const addDiscountCode = () => {
        setDiscountCodes(prevState => [
            ...prevState,
            { id: prevState.length + 1, collection_discount: '', collection_discount_type: '%', collection_name: '', collection_id: '', collection_message: '' }
        ]);
    };


    const deleteDiscountCode = useCallback((idToDelete, index) => {
        if(discountShipping?.automatic_checkout_discount.length > 1 || discountCodes.length > 1){
            setDiscountCodes(prevState => {
                const updatedState = prevState.filter((_, idx) => idx !== index);
                if (discountShipping?.id) {
                    dispatch(UpdatePlanGroup({
                        id: discountShipping.id,
                        updatedFields: { automatic_checkout_discount: updatedState }
                    }));
                }
                return updatedState;
            });
        }
    }, [dispatch, discountShipping , discountCodes]);

    const handleResourcePickerToggle = useCallback((id) => {
        setProductId(id);
        setResourcePickerOpen(prevState => !prevState);
    }, []);

    return (
        <div className="discount_free_shipping_accordion">
            <div className="radio_field">
                <RadioButton
                    label="No Discounts"
                    checked={discountShipping?.discount_type == "1"}
                    name="discount_options"
                    id="Radio_1"
                    onChange={() => handleDiscountChange("1", "discount_type")}
                />
            </div>
            <div className="radio_field ms-margin-top" style={{ display: 'flex', alignItems: 'center' }}>
                <RadioButton
                    label={
                        <span className="title_badge_block">
                            Automatic Checkout Discount Method{' '}
                            {
                                shop?.planType ? <Badge tone="success" progress='complete'>Recommended</Badge>:<Badge tone="info" progress='complete'>Paid Plan Required</Badge>
                            }
                        </span>
                    }
                    disabled={shop?.planType ? false : true }
                    helpText="Automatically apply a product and/or shipping discount to member checkouts."
                    checked={discountShipping?.discount_type == "2"}
                    name="discount_options"
                    id="Radio_2"
                    onChange={() => handleDiscountChange("2", "discount_type")}
                />
            </div>
            {
                discountShipping?.discount_type == '2' ? <>
                    <div className="" style={{ marginLeft: '30px' }}>
                        <div className="display_message_checkbox ms-margin-top">
                            <Checkbox
                                label={<CommanLabel label={"Activate Product Discounts"} content={help$?.active_product_dis} />}
                                checked={discountShipping?.activate_product_discount || false}
                                onChange={(value) => handleDiscountChange(value, "activate_product_discount")}
                            />
                        </div>
                        {
                            discountShipping?.activate_product_discount ? <>
                                {discountCodes?.map((item, index) => (
                                    <div key={item.id}>
                                        <div className="flex-containero ms-margin-top" style={{ display: 'flex', justifyContent: 'space-between', marginLeft: '30px', gap: '1px' }} >
                                            <div className="flex-item" style={{ flex: '1' }}>
                                                <TextField
                                                    type="text"
                                                    label={<CommanLabel label={"Discount Amount"} />}
                                                    id={`collection_discount_${item.id}`}
                                                    value={item.collection_discount || ''}
                                                    onChange={(value) => handleDiscountChange(value, 'collection_discount', index)}
                                                    autoComplete="off"
                                                    max={item?.collection_discount_type == '$' ? 100000 : 100}
                                                    error={item?.collection_discount >= 0 ? errors$?.length > 0 ? errors$[0][`${errorMembershipLenght}.${index}.collection_discount`] : '' : ''}
                                                />
                                            </div>
                                            <div className="flex-item" style={{ flex: '0', marginTop: '22px', marginLeft: '10px' }}>
                                                <Select
                                                    options={auto_discount_options}
                                                    id={`collection_discount_type_${item.id}`}
                                                    value={item?.collection_discount_type || '%'}
                                                    onChange={(value) => handleDiscountChange(value, 'collection_discount_type', index)}
                                                />
                                            </div>
                                        </div>
                                        <div className="display_message_checkbox ms-margin-top" style={{ marginLeft: '30px' }}>
                                            <TextField
                                                type="text"
                                                label={<CommanLabel label={"Discount Name"} content={help$?.discount_message} />}
                                                id={`collection_message_${item.id}`}
                                                value={item.collection_message}
                                                placeholder="e.g. MEMBER DISCOUNT"
                                                onChange={(value) => handleDiscountChange(value, 'collection_message', index)}
                                                autoComplete="off"
                                                maxLength={50}
                                                error={item?.collection_message != '' ? errors$?.length > 0 ? errors$[0][`${errorMembershipLenght}.${index}.collection_message`] : '' : ''}
                                            />
                                        </div>
                                        <div className="display_message_checkbox ms-margin-top" style={{ marginLeft: '30px' }}>
                                            <ChangeProducts
                                                resource="Collection"
                                                is_alreadyselected={true}
                                                value={item?.collection_name ? `Collections (${item?.counts} Selected) ` + item?.collection_name : `Collections (0 Selected)`}
                                                data={item?.collection_id || ''}
                                                label={"Collections"}
                                                handleResourcePickerToggle={() => handleResourcePickerToggle(item.id)}
                                                resourcePickerOpen={resourcePickerOpen && productId === item.id}
                                                onSelection={(resources) => handleResourceSelection(resources, item.id, index)}
                                                onCancel={() => setResourcePickerOpen(false)}
                                                selectMultiple={true}
                                                placeHolder={"Collection"}
                                            />
                                            <Text style={{ marginTop: '20px' }}>Which collections of products should the automatic discount apply to?</Text>
                                        </div>
                                        <div className="open_shopify_button ms-margin-top" style={{ textAlign: 'end' }}>
                                            {
                                                discountCodes.length > 1 ? <Button  variant="primary" tone="critical"onClick={() => deleteDiscountCode(item.id, index)}>Delete Discount</Button> : <Button variant="primary" disabled tone="critical"onClick={() => deleteDiscountCode(item.id, index)}>Delete Discount</Button>
                                            }
                                            
                                        </div>
                                    </div>
                                ))}
                                <div className="open_shopify_button ms-margin-top" style={{ marginLeft: '30px' }}>
                                    <Button variant='primary' onClick={addDiscountCode}>Add Another Discount</Button>
                                </div>
                            </> : ''
                        }
                    </div>
                    <div className="" style={{ marginLeft: '30px' }}>
                        <div className="display_message_checkbox ms-margin-top">
                            <Checkbox
                                label={<CommanLabel label={"Activate Shipping Discounts"} content={help$?.active_shipping_dis} />}
                                checked={discountShipping?.activate_shipping_discount || false}
                                onChange={(value) => handleDiscountChange(value, "activate_shipping_discount")}
                            />
                        </div>
                        {
                            discountShipping?.activate_shipping_discount ?
                                <>
                                    <div className="flex-containero ms-margin-top" style={{ display: 'flex', justifyContent: 'space-between', marginLeft: '30px', gap: '1px' }} >
                                        <div className="flex-item" style={{ flex: '1' }}>
                                            <TextField
                                                type="text"
                                                label={<CommanLabel label={"Discount Amount"} />}
                                                value={discountShipping?.shipping_discount_code || ''}
                                                onChange={(value) => {
                                                    handleDiscountChange(value, 'shipping_discount_code');
                                                }}
                                                max={discountShipping?.active_shipping_dic == '$' ? 100000 : 100}
                                                error={discountShipping?.shipping_discount_code >= 0 ? errors$?.length > 0 ? errors$[0][`data.tiers.${selectedIndex}.shipping_discount_code`] : '' : ''}
                                                autoComplete="off"
                                            />
                                        </div>
                                        <div className="flex-item" style={{ flex: '0', marginTop: '22px', marginLeft: '10px' }}>
                                            <Select
                                                options={auto_discount_options}
                                                value={discountShipping?.active_shipping_dic || '%'}
                                                onChange={(value) => handleDiscountChange(value, "active_shipping_dic")}
                                            />
                                        </div>
                                    </div>
                                    <div className="display_message_checkbox ms-margin-top" style={{ marginLeft: '30px' }}>
                                        <TextField
                                            type="text"
                                            label={<CommanLabel label={"Discount Name"} content={help$?.discount_message} />}
                                            value={discountShipping?.shipping_discount_message}
                                            placeholder="e.g. MEMBER DISCOUNT"
                                            onChange={(value) => {
                                                handleDiscountChange(value, 'shipping_discount_message');
                                            }}
                                            maxLength={50}
                                            error={discountShipping?.shipping_discount_message != '' ? errors$?.length > 0 ? errors$[0][`data.tiers.${selectedIndex}.shipping_discount_message`] : '' : ''}
                                            autoComplete="off"
                                        />
                                    </div>


                                </>
                                : ''
                        }
                    </div>
                </> : ''
            }
            <div className="radio_field ms-margin-top">
                <RadioButton
                    label="Discount Code Method"
                    helpText="Have a members-only discount code automatically applied at checkout for logged in members."
                    checked={discountShipping?.discount_type == "3"}
                    id="Radio_3"
                    onChange={() => handleDiscountChange("3", "discount_type")}
                />
            </div>
            {
                discountShipping?.discount_type == '3' ?
                    <>
                        <div className="" style={{ marginLeft: '30px' }}>
                            <div className="important_note">
                                <Text variant="headingSm" as="h6" fontWeight="medium">
                                    <b>IMPORTANT</b>: Create discount codes that are exclusive to active members. <Link target="_blank" url="https://support.simplee.best/en/articles/5655877-create-members-only-discount-codes">Learn how here.</Link>
                                </Text>
                            </div>

                            <div className="discount_code_block ms-margin-top">
                                <TextField
                                    label={<CommanLabel label={"Discount Code"} content={""} />}
                                    value={discountShipping?.discount_code || ''}
                                    onChange={(value) => handleDiscountChange(value, "discount_code")}
                                    autoComplete="off"
                                    placeholder="e.g. MEMBERS10OFF"
                                />
                            </div>
                            <div className="discount_code_block ms-margin-top">
                                <TextField
                                    label={<CommanLabel label={"Text to display on cart page to active members"} content={""} />}
                                    value={discountShipping?.discount_code_members || ''}
                                    onChange={(value) => handleDiscountChange(value, "discount_code_members")}
                                    autoComplete="off"
                                    placeholder="e.g. Your 20% members discount will be applied at checkout - thanks for being a member 🎉"
                                />
                            </div>
                            <div className="display_message_checkbox ms-margin-top">
                                <Checkbox
                                    label={<CommanLabel label={"Display message on cart page"} content={""} />}
                                    checked={discountShipping?.is_display_on_cart_page || false}
                                    onChange={(value) => handleDiscountChange(value, "is_display_on_cart_page")}
                                />
                            </div>
                            <div className="display_message_checkbox ms-margin-top">
                                <Checkbox
                                    label={<CommanLabel label={"Display message when members log in"} content={""} />}
                                    checked={discountShipping?.is_display_on_member_login || false}
                                    onChange={(value) => handleDiscountChange(value, "is_display_on_member_login")}
                                />
                            </div>
                            {/* Open Shopify Discount Codes - Button */}
                            <div className="open_shopify_button ms-margin-top">
                                <a className="open_shopify_button_discount" href={url} target="___blank" > <Button variant='primary'>Open Shopify Discount Codes</Button></a>
                            </div>
                        </div>
                    </>

                    : ''
            }
        </div>
    );
}
