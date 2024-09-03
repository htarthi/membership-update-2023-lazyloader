import { Button, Icon, LegacyCard, Modal, Tabs, TextField ,Text} from '@shopify/polaris'
import React, { useCallback, useState, useEffect } from 'react'
import Accordion from './Accordion';
import CommanLabel from '../../../GlobalPartials/CommanInputLabel/CommanLabel';
import { useSelector, useDispatch } from 'react-redux';
import { CreateTier, DeleteCreateTier, DeleteTier, UpdatePlanGroup, UpdatePlanGroupId, UpdateQuestionPlan, changeIndex, createTiersId, resetCreateTiersId, resetIsDeleteSuccess, resetIsStoreTiers, resetPlanErrors, resetUpdateQuestionPlan } from '../../../../data/features/plansDetails/plansDetailsSlice';
import { deleteTier, getDefaultPlanData, storePlanGroup } from '../../../../data/features/plansDetails/planAction';
import { resetupdateTiers, storeReducer, updateTiers } from '../../../../data/features/plans/plansSlice';
import DeleteModal from '../../../GlobalPartials/DeleteModal/DeleteModal';
import { AlertDiamondIcon } from '@shopify/polaris-icons';
import { useNavigate, useParams } from 'react-router-dom';
import { each } from 'lodash';

export default function CreateNewTier() {

    let { id } = useParams();
    const navigate = useNavigate();
    const dispatch = useDispatch();

    // selector - redux data
    const newStore$ = useSelector((state) => state.plans?.newStore);
    const selectedIndex$ = useSelector((state) => state?.plansDetail?.selectedIndex);
    const plan_groups$ = useSelector((state) => state?.plansDetail?.data?.plan_groups);
    const product$ = useSelector((state) => state?.plansDetail?.data?.product);
    const createTiersId$ = useSelector((state) => state?.plansDetail?.createTiersId);
    const isDeleteSuccess$ = useSelector((state) => state?.plansDetail?.isDeleteSuccess);
    const isStoreTiers$ = useSelector((state) => state?.plansDetail?.isStoreTiers);
    const isShopifyPlanId$ = useSelector((state) => state?.plansDetail?.isShopifyPlanId);
    const errors$ = useSelector((state) => state?.plansDetail?.errors);
    const help$ = useSelector((state) => state?.plansDetail?.help);
    const updated_tiers$ = useSelector((state) => state?.plans?.updated_tiers);
    const isUpdateQuestionPlan$ = useSelector((state) => state?.plansDetail?.isUpdateQuestionPlan);

    // tabs - create new tier
    const [selected, setSelected] = useState(selectedIndex$);
    const [isFocused, setisFocused] = useState(false);

    const dummyData = plan_groups$?.map(item => item.name ? true : false);
    const [focusData, setFocusData] = useState(dummyData);

    useEffect(() => {
        setFocusData(plan_groups$?.map(item => item.name ? true : false))
    }, [plan_groups$[selectedIndex$]?.name]);

    // handle functions
    useEffect(() => {
        setSelected(0);
        dispatch(changeIndex(0));
    }, []);

    // accordian tabs
    const [open, setOpen] = useState(0);

    const handleToggle = useCallback((id) => {
        open !== id ? setOpen(id) : setOpen('');
    }, [open]);

    // tabs handle change event
    const handleTabChange = useCallback(
        (selectedTabIndex) => {
            setSelected(selectedTabIndex);
            dispatch(changeIndex(selectedTabIndex));
        }, [selected, selectedIndex$]);

    // Tier Name, Customer Tag & Order Tag - input handleChange event
    const handleTierChange = useCallback((newValue, name) => {
        const updatedFields = { [name]: newValue, tier_id: plan_groups$[selectedIndex$]?.tier_id };
        dispatch(UpdatePlanGroup({ id: plan_groups$[selectedIndex$]?.id, updatedFields }));
        dispatch(updateTiers(plan_groups$[selectedIndex$]?.tier_id));
    }, [plan_groups$, selectedIndex$, updated_tiers$]);

    // Tier Name input blur event
    const handleBlurEvent = useCallback((e) => {
        const updatedFields = { content: e.target.value, tier_id: plan_groups$[selectedIndex$]?.tier_id };
        dispatch(UpdatePlanGroup({ id: plan_groups$[selectedIndex$]?.id, updatedFields }));
    }, [plan_groups$, selectedIndex$])

    // create new tier modal...
    const [newTieractive, setNewTierActive] = useState(false);

    // create new tier modal - cancle handle event
    const handleChange = useCallback(() => {
        setNewTierActive(!newTieractive);
    }, [newTieractive]);

    // create new tier modal - tier name
    const [tierName, setTierName] = useState('');
    const handleTierNameChange = useCallback((newValue) => setTierName(newValue), [tierName]);
    const AdditionalQuestions$ = useSelector(
        (state) => state.plansDetail?.data
    );

    // create handle event
    const [counter, setCounter] = useState(Math.floor(Math.random() * 120));

    const handleCreateChange = useCallback(() => {
        const newCounter = Math.floor(Math.random() * 120);
        setCounter(newCounter);
        if (tierName !== '') {
            const data = {
                rules: [],
                formFields: AdditionalQuestions$.formFields,
                discounts: [],
                creditRules: [],
                membershipLength: [],
                product: {
                    id: null,
                    name: "",
                },
                id: newCounter,
                tier_id: `new_${newCounter}`,
                name: tierName,
                content: tierName,
                options: ["Membership Length"],
                tag_customer: null,
                tag_order: null,
                discount_code: null,
                discount_code_members: null,
                is_display_on_cart_page: 0,
                is_display_on_member_login: 0,
                discount_type : "1",
                activate_product_discount : false ,
                activate_shipping_discount : false ,
                shipping_discount_code : null,
                active_shipping_dic : null,
                automatic_checkout_discount: [
                    {
                        id: 1,
                        collection_discount: '',
                        collection_discount_type: '%',
                        collection_name: '',
                        collection_id: '',
                        collection_message:'',
                    }
                ],
                deleted: {
                    membershipLength: [],
                    rules: [],
                    formFields: [],
                    discounts: [],
                    creditRules: [],
                },
                contract_count: 0,
                isNew: true,
            }
            dispatch(CreateTier(data));
            dispatch(createTiersId(newCounter))
            dispatch(updateTiers(`new_${newCounter}`))
            setNewTierActive(false)
            setSelected(plan_groups$.length)
            dispatch(changeIndex(plan_groups$.length));
            setTierName('');
        }
    }, [tierName, plan_groups$, counter, createTiersId$])


    const handleSave = useCallback(async () => {
        dispatch(UpdateQuestionPlan(AdditionalQuestions$?.formFields));
        // try {
        //     await dispatch(UpdateQuestionPlan(AdditionalQuestions$?.formFields));
        //     // let payloadData = { tiers: plan_groups$, ...{ product: product$ }, newStore: newStore$, updated_tiers };

        //     let payloadData = { tiers: plan_groups$, ...{ product: product$ }, newStore: newStore$, updated_tiers };

        //     dispatch(storePlanGroup({
        //         data: payloadData
        //     }));
        //     dispatch(resetCreateTiersId())
        // } catch (error) {
        //     // Handle error if needed
        //     console.error('Error:', error);
        // }
    }, [plan_groups$, AdditionalQuestions$]);

    useEffect(() => {
        if (isUpdateQuestionPlan$) {
            let updated_tiers = !newStore$ ? updated_tiers$ : [];
            let payloadData = { tiers: plan_groups$, ...{ product: product$ }, newStore: newStore$, updated_tiers };
            dispatch(storePlanGroup({
                data: payloadData
            }));
            dispatch(resetCreateTiersId())

            dispatch(resetUpdateQuestionPlan())
        }
    }, [isUpdateQuestionPlan$])

    // save changes
    // const handleSave = useCallback(() => {
    //     let updated_tiers = !newStore$ ? updated_tiers$ : [];
    //     console.log("dave ---------------------->   ",AdditionalQuestions$);


    //     dispatch(UpdateQuestionPlan(AdditionalQuestions$?.formFields));
    //     console.log("plan group is an ", plan_groups$);
    //     // let payloadData = { tiers: plan_groups$, ...{ product: product$ }, newStore: newStore$, updated_tiers };

    //     dispatch(storePlanGroup({
    //         data: payloadData
    //     }));
    //     dispatch(resetCreateTiersId())

    // }, [plan_groups$,AdditionalQuestions$])

    // store plan is successfully updated then call edit api
    useEffect(() => {
        if (isStoreTiers$) {
            console.log('dsadasdasda');
            dispatch(UpdatePlanGroupId({ newPlanGroupId: [isShopifyPlanId$] }));
            dispatch(resetupdateTiers())
            // product$?.id
            navigate('/plans');
            // newStore$ ? navigate('/plans') : navigate('/plans/9587896090943/edit')
            dispatch(storeReducer(false));
            dispatch(resetIsStoreTiers(false))
        }
    }, [isStoreTiers$])

    // error handling - change color of tab
    useEffect(() => {
        if (errors$?.length > 0) {
            Object.keys(errors$[0]).map((item, i) => {
                if (item?.split('.')?.includes('tiers')) {
                    setTimeout(() => {
                        let getTab = document.getElementById(`${plan_groups$[parseInt(item?.split('.')[2])]?.id}`);
                        getTab !== null ? getTab.style.backgroundColor = '#fde2dd' : '';
                        getTab !== null ? getTab.style.color = '#c5280c' : '';
                    }, 1000);
                }
            })
        }
    }, [errors$])

    const [deleteModal, setDeleteModal] = useState(false);
    // open delete modal
    const handleDeletePlan = useCallback(() => {
        setDeleteModal(!deleteModal);
    }, [deleteModal])

    // delete tier
    const [planGroupId, setPlanGroupId] = useState();
    const deteleTier = useCallback(() => {


        if (plan_groups$[selectedIndex$]?.isNew) {
            dispatch(DeleteTier(plan_groups$[selectedIndex$]?.id));
            dispatch(resetPlanErrors());
        } else {
            dispatch(deleteTier(plan_groups$[selectedIndex$]?.id));
            setPlanGroupId(plan_groups$[selectedIndex$]?.id);
        }
        setDeleteModal(!deleteModal);
        dispatch(changeIndex(plan_groups$?.length - 2));
        dispatch(DeleteCreateTier(plan_groups$[selectedIndex$]?.id));
        setSelected(plan_groups$?.length - 2);
        dispatch(resetupdateTiers())


    }, [deleteModal, plan_groups$, selectedIndex$, planGroupId])

    const handle = useCallback((e) => {
    })

    useEffect(() => {
        if (isDeleteSuccess$ && !deleteModal) {
            dispatch(DeleteTier(planGroupId))
            dispatch(resetIsDeleteSuccess());
            if (plan_groups$?.length <= 1) {
                navigate('/plans');
            }
        }
    }, [isDeleteSuccess$, planGroupId]);

    return (
        <div className="create_new_tier_wrap ms-margin-top">
            <LegacyCard>

                {/* tab & create new tier */}
                <div className='create_new_tier_head flex-space-between'>
                    {/* tabs */}
                    {/* { plan_groups$[selectedIndex$]?.name && */}
                    <div id='id_new_tiew_button' className='new_tier_tabs'>
                        <Tabs tabs={plan_groups$} selected={selected} onSelect={handleTabChange}/>
                    </div>
                    {/* } */}
                    {/* Create New Tier Button */}
                    <div className='create_new_tier_button'>
                        <Button  onClick={handleChange} variant="primary">Create New Tier</Button>
                    </div>
                </div>

                {/* tier - plan informations */}
                <div className='plan_info_main_block'>
                    {/* tier information */}
                    <div className='tier_info_wrap'>
                        {
                            plan_groups$[selectedIndex$]?.membershipLength.length <= 0 ?
                                (errors$?.length > 0 && errors$[0][`data.tiers.${selectedIndex$}.membershipLength`]) &&
                                <span className='error_wrap'>
                                    <Icon source={AlertDiamondIcon} color="critical" />
                                    {errors$[0][`data.tiers.${selectedIndex$}.membershipLength`]}
                                </span>
                                :
                                ''
                        }

                        {/* input - Tier Name*/}
                        <div className='input_field_wrap input_field_row'>
                            <TextField
                                label={<CommanLabel label={"Tier Name"} content={help$?.plan_name} />}
                                value={plan_groups$[selectedIndex$]?.name ? plan_groups$[selectedIndex$]?.name : ""}
                                placeholder='e.g. Gold Membership'
                                onChange={(val) => handleTierChange(val, "name")}
                                onBlur={(e) => handleBlurEvent(e)}
                                autoComplete="off"
                                error={!plan_groups$[selectedIndex$]?.name ? errors$?.length > 0 ? errors$[0][`data.tiers.${selectedIndex$}.name`] : '' : ''}
                                autoFocus={true}
                                name="tier_name"
                            />
                        </div>

                        {/* Customer Tag & Order Tag */}
                        <div className='input_two_fields_wrap input_field_row'>
                            <div className='input_field_wrap'>
                                <TextField
                                    label={<CommanLabel label={"Customer Tag"} content={help$?.tag_customer} />}
                                    placeholder='e.g. Gold'
                                    value={plan_groups$[selectedIndex$]?.tag_customer ? plan_groups$[selectedIndex$]?.tag_customer : ''}
                                    onChange={(val) => handleTierChange(val, "tag_customer")}
                                    autoComplete="off"
                                    error={!plan_groups$[selectedIndex$]?.tag_customer ? errors$?.length > 0 ? errors$[0][`data.tiers.${selectedIndex$}.tag_customer`] : '' : ''}
                                    disabled={plan_groups$[selectedIndex$]?.active_members > 0 && true}
                                />
                            </div>

                            <div className='input_field_wrap'>
                                <TextField
                                    placeholder='e.g. Membership'
                                    label={<CommanLabel label={"Order Tag"} content={help$?.tag_order} />}
                                    value={plan_groups$[selectedIndex$]?.tag_order ? plan_groups$[selectedIndex$]?.tag_order : ''}
                                    onChange={(val) => handleTierChange(val, "tag_order")}
                                    autoComplete="off"
                                    error={!plan_groups$[selectedIndex$]?.tag_order ? errors$?.length > 0 ? errors$[0][`data.tiers.${selectedIndex$}.tag_order`] : '' : ''}
                                />
                            </div>
                        </div>
                    </div>

                    {/* accordian */}
                    <Accordion open={open} handleToggle={handleToggle} />

                    {/* save changes */}
                    <div className='save_changes_wrap' style={{ color: '#D82C0D' }}>
                        {(plan_groups$?.length >= 1 && !newStore$) ? <Button variant='primary' tone='critical' onClick={handleDeletePlan}>Delete Tier</Button> : <Button id='btn-delete-tier'   onClick={handleDeletePlan}>Delete Tier</Button>}

                        <Button  onClick={handleSave} variant='primary'>Save Changes</Button>
                    </div>
                </div>
            </LegacyCard>
            {/* create new tier modal */}
            <Modal
                open={newTieractive}
                onClose={handleChange}
                title={<Text>Create New Tier</Text>}
                primaryAction={{ content: 'Create', onAction: handleCreateChange}}
                secondaryActions={[{ content: 'Cancel', onAction: handleChange }]}
            >
                <Modal.Section>
                    <TextField
                        label="Tier Name"
                        value={tierName}
                        onChange={handleTierNameChange}
                        autoComplete="off"
                    />
                </Modal.Section>
            </Modal>
            {/* delete tier*/}
            <DeleteModal active={deleteModal} setDeleteModal={setDeleteModal} deleteMethod={deteleTier} />
        </div>
    );
}
