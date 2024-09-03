import { createSlice } from "@reduxjs/toolkit";
import { checkSellingPlanExists, deleteTier, getDefaultPlanData, getRestricatedContents, storePlanGroup } from "./planAction";
import { toast } from "react-toastify";

const initialState = {
    data: {
        plan_groups: [
            {
                rules: [],
                formFields: [],
                discounts: [],
                creditRules: [],
                membershipLength: [],
                product: {
                    id: 8555113611583,
                    name: "shoes",
                },
                id: 1,
                tier_id: 'new_1',
                name: "",
                content: "",
                options: ["Membership Length"],
                tag_customer: "",
                tag_order: "",
                discount_code: null,
                discount_code_members: null,
                is_display_on_cart_page: 0,
                is_display_on_member_login: 0,
                discount_type: "1",
                activate_product_discount: false,
                activate_shipping_discount: false,
                shipping_discount_code: null,
                active_shipping_dic: '%',
                shipping_discount_message: null,
                automatic_checkout_discount: [
                    {
                        id: 1,
                        collection_discount: '',
                        collection_discount_type: '%',
                        collection_name: '',
                        collection_id: '',
                        collection_message: '',
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
            },
        ],
        storeData: {
            page: [
                {
                    id: 122965229887,
                    title: "Contact",
                },
            ],
            blog: [
                {
                    id: 102093422911,
                    title: "News",
                },
            ],
            article: [],
        },
        shop: {
            isPosEnable: true,
            currency: "\u20b9",
            name: "dtas-demo.myshopify.com",
            storecredit: false,
        },
        feature: {
            automatic_discounts: true,
        },
        product: {
            id: null,
            name: null,
            images: null
        },
        formFields: [],
    },
    errors: [],
    selectedIndex: 0,
    help: {
        plan_name:
            "This name will be displayed on the product page - we recommend that it matches the name of your membership product",
        tag_customer:
            "Name of the tag which will be added to all customers with active memberships",
        tag_order:
            "Optional, this tag will be added to any order containing this membership product",
        membership_product:
            " The Shopify product which will represent this membership. You should create this product before creating this plan",
        plan_price: "What will be the initial price of the membership? After this period, the membership will be charged the regular price listed above",
        plan_length: "For how many periods will this price be applied",
        plan_period: "Is this price to be applied to a set number of days or orders (including the first order)?",
        display_name:
            "How should this length be displayed to customers on the storefront?",
        description:
            "Any additional information that is relevant to this membership",
        free_trial:
            "Set the initial price for the membership, and decide how many orders they get at this price before reverting to regular price",
        one_time_payment:
            "Only charge members for the first order. No recurring orders will be created",
        min_max:
            "Don’t allow members to cancel until they have completed a minimum number of orders",
        store_credit:
            "Give members store credit for successful membership orders",
        credit_type:
            "Will a credit be applied on every order, or only the first order?",
        store_amount:
            " The amount of credit to apply to the member’s account",
        expired_membership: "Automatically cancel the membership at the end of this number of orders",
        active_product_dis: "Automatically apply a fixed or percentage discount to one-time products when an active member makes future orders",
        active_shipping_dis: "Automatically apply a fixed or percentage discount on shipping for active members",
        discount_message: "The name of the discount that will appear at checkout",
    },
    isLoading: false,
    isStoreTiers: false,
    isSuccess: false,
    isDeleteSuccess: false,
    isShopifyPlanId: '',
    sellingPlanExists: [],
    isSellingPlanLoading: false,
    createTiersId: [],
    isUpdateQuestionPlan: false,

};

export const plansDetailsSlice = createSlice({
    name: "plansDetails",
    initialState,
    extraReducers: {
        [getDefaultPlanData.pending]: (state) => {
            state.isLoading = true;
        },
        [getDefaultPlanData.fulfilled]: (state, { payload }) => {
            const index = payload?.data?.formfield_index;
            state.data.storeData = payload?.data?.storeData;
            state.data.product = payload?.data?.product;
            state.data.formFields = payload?.data?.planGroups[index]['formFields'];
            const planGroupsUpdate = payload?.data?.planGroups || [];
            const automaticCheckoutDiscount = planGroupsUpdate[index]?.['automatic_checkout_discount'] || [];

            const discountToSet = automaticCheckoutDiscount.length <= 0
                ? [{
                    id: 1,
                    collection_discount: '',
                    collection_discount_type: '%',
                    collection_name: '',
                    collection_id: '',
                    collection_message: '',
                }]
                : automaticCheckoutDiscount;

            const updatedPlanGroups = planGroupsUpdate.map((group, idx) => {
                if (idx === index) {
                    return {
                        ...group,
                        automatic_checkout_discount: discountToSet
                    };
                }
                return group;
            });

            console.log("updatedPlanGroups");
            console.log(updatedPlanGroups);

            state.data.plan_groups =
                updatedPlanGroups.length > 0
                    ? updatedPlanGroups
                    : state.data.plan_groups;
            state.data.shop = payload?.data?.shop;
            state.data.feature = payload?.data?.feature;
            state.isLoading = false;
            state.isSuccess = true;
        },
        [getDefaultPlanData.rejected]: (state, { payload }) => {
            state.isLoading = true;
        },
        [getRestricatedContents.pending]: (state) => {

        },
        [getRestricatedContents.fulfilled]: (state, { payload }) => {
            state.data.storeData = payload?.storeData;
        },
        [getRestricatedContents.rejected]: (state) => {

        },
        [checkSellingPlanExists.pending]: (state) => {
            state.isSellingPlanLoading = true;
            state.isLoading = true;
        },
        [checkSellingPlanExists.fulfilled]: (state, { payload }) => {
            state.isSellingPlanLoading = false;
            state.isLoading = false;
            state.sellingPlanExists = payload?.data
        },
        [checkSellingPlanExists.rejected]: (state, { payload }) => {
            state.isSellingPlanLoading = true;
            state.isLoading = true;
        },

        [storePlanGroup.pending]: (state) => {
            state.isLoading = true;
            state.isStoreTiers = false;
            state.isShopifyPlanId = '';
        },
        [storePlanGroup.fulfilled]: (state, { payload }) => {

            if (payload?.new_1?.original?.data) {
                state.isLoading = false;
                state.isStoreTiers = false;
                toast.error(payload.new_1.original.data);
            } else {
                state.isLoading = false;
                state.isStoreTiers = true;
                state.isShopifyPlanId = payload;
                state.errors = [];

                toast.success("Changes saved successfully");
            }
        },
        [storePlanGroup.rejected]: (state, { payload }) => {


            state.isLoading = false;
            state.isStoreTiers = false;
            state.isShopifyPlanId = '';
            state.errors = [payload];
            toast.error(payload.data);
        },
        [deleteTier.pending]: (state) => {
            state.isDeleteSuccess = false;
        },
        [deleteTier.fulfilled]: (state, { payload }) => {
            state.isDeleteSuccess = payload?.isSuccess;
            toast.success(payload.data);
        },
        [deleteTier.rejected]: (state, { payload }) => {
            state.isDeleteSuccess = false;
            toast.error(payload?.data);
            state.errors = payload?.data;
        },
    },
    reducers: {
        sellingPlanExists: (state, action) => {
            state.sellingPlanExists = []
        },
        deletedUpdate: (state, action) => {
            const { key, id } = action.payload;
            state.data.plan_groups[state.selectedIndex].deleted?.[key].push(id);
        },
        resetIsDeleteSuccess: (state, action) => {
            state.isDeleteSuccess = false;
        },
        resetIsStoreTiers: (state, action) => {
            state.isStoreTiers = false;
        },
        CreateTier: (state, action) => {
            const data = action.payload;
            state.data.plan_groups.push(data);
        },
        createTiersId: (state, action) => {
            state.createTiersId.push(action.payload);
        },
        resetCreateTiersId: (state, action) => {
            state.createTiersId = [];
        },
        resetisShopifyPlanId: (state, action) => {
            state.isShopifyPlanId = '';
        },
        DeleteTier: (state, action) => {
            state.data.plan_groups = state.data.plan_groups.filter(
                (item, index) => item?.id !== action.payload
            );
        },
        DeleteCreateTier: (state, action) => {
            state.createTiersId = state.createTiersId.filter(
                (item, index) => item !== action.payload
            );
        },
        PlansDetails: (state, action) => {
            state.data = action.payload;
        },
        UpdatePlanGroup(state, action) {
            const { id, updatedFields } = action.payload;
            const index = state.data.plan_groups.findIndex(
                (item) => item.id === id
            );
            if (index !== -1) {
                state.data.plan_groups[index] = {
                    ...state.data.plan_groups[index],
                    ...updatedFields,
                };
            }
        },
        UpdatePlanGroupId(state, action) {
            const { newPlanGroupId } = action.payload;

            state.data.plan_groups.map((item, index) => {
                newPlanGroupId?.map((newId, i) => {
                    if (Object.keys(newId)?.includes(item?.tier_id)) {
                        state.data.plan_groups[index] = {
                            ...state.data.plan_groups[index],
                            id: newId[item?.tier_id],
                            tier_id: newId[item?.tier_id]
                        };
                    }
                })
            })

        },
        AddLength: (state, action) => {
            state.data.plan_groups[state.selectedIndex].membershipLength.push(action.payload);
        },
        UpdateLength(state, action) {
            const { index, updatedFields } = action.payload;
            if (index !== -1) {
                state.data.plan_groups[state.selectedIndex].membershipLength[
                    index
                ] = {
                    ...state.data.plan_groups[state.selectedIndex]
                        .membershipLength[index],
                    ...updatedFields,
                };
            }
        },
        DeleteLength: (state, action) => {
            state.data.plan_groups[state.selectedIndex].membershipLength =
                state.data.plan_groups[state.selectedIndex].membershipLength.filter(
                    (item, index) => index !== action.payload
                );
        },
        Rules: (state, action) => {
            state.data.plan_groups[state.selectedIndex].rules = action.payload;
        },
        AddRules: (state, action) => {
            state.data.plan_groups[state.selectedIndex].rules.push(action.payload);
        },
        UpdateRules(state, action) {
            const { id, updatedFields } = action.payload;

            console.log("payload us an " + action.payload)
            console.log("updatedFields us an " + updatedFields)

            const index = state.data.plan_groups[state.selectedIndex].rules.findIndex((item) => item.id === id);
            if (index !== -1) {
                state.data.plan_groups[state.selectedIndex].rules[index] = {
                    ...state.data.plan_groups[state.selectedIndex].rules[index],
                    ...updatedFields,
                };
            }
        },
        DeleteRules: (state, action) => {
            state.data.plan_groups[state.selectedIndex].rules =
                state.data.plan_groups[state.selectedIndex].rules.filter(
                    (item) => item?.id !== action.payload
                );
        },
        Discounts: (state, action) => {
            state.data.plan_groups[state.selectedIndex].discounts =
                action.payload;
        },
        AddDiscounts: (state, action) => {
            state.data.plan_groups[state.selectedIndex].discounts.push(
                action.payload
            );
        },
        UpdateDiscounts(state, action) {
            const { id, updatedFields } = action.payload;
            const index = state.data.plan_groups[state.selectedIndex].discounts.findIndex((item) => item.id === id);
            if (index !== -1) {
                state.data.plan_groups[state.selectedIndex].discounts[index] = {
                    ...state.data.plan_groups[state.selectedIndex].discounts[
                    index
                    ],
                    ...updatedFields,
                };
            }
        },
        DeleteDiscounts: (state, action) => {
            state.data.plan_groups[state.selectedIndex].discounts =
                state.data.plan_groups[state.selectedIndex].discounts.filter(
                    (item) => item?.id !== action.payload
                );
        },
        AdditionalQue: (state, action) => {
            state.data.formFields =
                action.payload;
        },
        AddAdditionalQue: (state, action) => {
            state.data.formFields.push(
                action.payload
            );
        },
        UpdateFields(state, action) {
            const { id, updatedFields } = action.payload;
            const index = state.data.formFields.findIndex((item) => item.id === id);
            if (index !== -1) {
                state.data.formFields[index] =
                {
                    ...state.data
                        .formFields[index],
                    ...updatedFields,
                };
            }
        },
        UpdateQuestionPlan(state, action) {
            state.data.plan_groups[0].formFields = action.payload;
            state.data.plan_groups.map((plangroup) => {
                plangroup.formFields = action.payload;
            })
            state.isUpdateQuestionPlan = true;
        },
        resetUpdateQuestionPlan(state, action) {
            state.isUpdateQuestionPlan = false;
        },
        DeleteFields: (state, action) => {
            state.data.formFields =
                state.data.formFields.filter(
                    (item) => item?.id !== action.payload
                );
        },
        setProduct: (state, action) => {
            state.data.product = action.payload;
        },
        resetPlanErrors: (state, action) => {
            state.errors = [];
        },
        resetPlanData: (state) => {
            state.data = {
                plan_groups: [
                    {
                        rules: [],
                        formFields: [],
                        discounts: [],
                        creditRules: [],
                        membershipLength: [],
                        product: {
                            id: null,
                            name: "",
                        },
                        id: 1,
                        tier_id: 'new_1',
                        name: null,
                        content: null,
                        options: ["Membership Length"],
                        tag_customer: null,
                        tag_order: null,
                        discount_code: null,
                        discount_code_members: null,
                        is_display_on_cart_page: 0,
                        is_display_on_member_login: 0,
                        discount_type: "1",
                        activate_product_discount: false,
                        activate_shipping_discount: false,
                        shipping_discount_code: null,
                        active_shipping_dic: '%',
                        shipping_discount_message: null,
                        automatic_checkout_discount: [
                            {
                                id: 1,
                                collection_discount: '',
                                collection_discount_type: '%',
                                collection_name: '',
                                collection_id: '',
                                collection_message: '',
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
                    },
                ],
                storeData: {
                    page: [
                        {
                            id: 113675174194,
                            title: "Contact",
                        },
                    ],
                    blog: [
                        {
                            id: 96088949042,
                            title: "News",
                        },
                    ],
                    article: [],
                },
                shop: {
                    isPosEnable: false,
                    currency: "",
                    name: null,
                    storecredit: false,
                },
                feature: {
                    automatic_discounts: true,
                },
                product: {
                    id: null,
                    name: null,
                    images: null
                },
                formFields: []
            };
        },
        changeIndex: (state, action) => {
            state.selectedIndex = action.payload;
        },
        AddProduct: (state, action) => {
            state.data.product = action.payload;
            state.data.plan_groups[0].product = action.payload;
            state.isSellingPlanLoading = false;
            state.sellingPlanExists = [];
        }
    },
});

// Action creators are generated for each case reducer function
export const {
    sellingPlanExists,
    deletedUpdate,
    resetIsStoreTiers,
    resetIsDeleteSuccess,
    CreateTier,
    createTiersId,
    resetCreateTiersId,
    resetisShopifyPlanId,
    DeleteTier,
    PlansDetails,
    AddLength,
    UpdateLength,
    DeleteLength,
    UpdatePlanGroup,
    UpdatePlanGroupId,
    Rules,
    AddRules,
    UpdateRules,
    DeleteRules,
    Discounts,
    AddDiscounts,
    UpdateDiscounts,
    DeleteDiscounts,
    AdditionalQue,
    AddAdditionalQue,
    UpdateFields,
    DeleteFields,
    setProduct,
    resetPlanErrors,
    resetPlanData,
    changeIndex,
    AddProduct,
    UpdateQuestionPlan,
    resetUpdateQuestionPlan,
    DeleteCreateTier,
} = plansDetailsSlice.actions;

export default plansDetailsSlice.reducer;
