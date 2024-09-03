import { createSlice } from "@reduxjs/toolkit";
import { getPlanGroup, merchantMigrate, checkActivePlan, updatePriceForSC, productUpdate  } from "./planAction";
import { toast } from "react-toastify";
import { deleteTier } from "../plansDetails/planAction";

const initialState = {
    data: {
        planG: [],
        products_list: [],
        shop: {
            currency: "CA$",
            name: "",
        },
    },
    isLoading: false,
    isSuccess: false,
    errorMessage: "",
    isAddMemberSuccess: false,
    newStore: false,
    updated_tiers: [],
    single_members: {
        ss_plan_group_id: '',
        firstname: "",
        lastname: "",
        fileName: "",
        email: "",
        importType: 0,
        file: null,
        is_sendinvitation: false,
        is_sendnewmembershipmail: false,
    },
    activePlan: [],
    errors: []
};

export const plansSlice = createSlice({
    name: "plans",
    initialState,
    reducers: {
        changePlans: (state) => {
            state.data = [];
        },
        storeReducer: (state, action) => {
            state.newStore = action.payload;
        },
        updateTiers: (state, action) => {
            const newData = action.payload;
            const isDataAlreadyAdded = state.updated_tiers.some(
                (item) => item == newData
            );
            if (!isDataAlreadyAdded) {
                state.updated_tiers.push(newData);
            }
        },
        resetupdateTiers: (state, action) => {
            state.updated_tiers = [];
        },
        deleteTiers: (state, actions) => {
            const id = actions.payload;
            const index = state.data.planG.findIndex(
                (item) => item.plan_groups[0]?.id == id
            );
            if (index !== -1) {
                state.data.planG = state.data.planG.filter(
                    (item) => item?.plan_groups[0]?.id !== id
                );
            }
        },
        singleMembers: (state, actions) => {
            const data = actions.payload;
            state.single_members = {
                ...state.single_members,
                ...data
            }
        },
        resteSingleMembers: (state, actions) => {
            state.single_members = {
                ss_plan_group_id: '',
                firstname: "",
                lastname: "",
                fileName: "",
                email: "",
                importType: 0,
                file: null,
                is_sendinvitation: false,
                is_sendnewmembershipmail: false,
            };
            state.isAddMemberSuccess = false;
            state.errors = []
        },
        ismerchantMigrate(state, actions) {
            const payload = actions.payload;
            const count = payload.membership_count + 1;

            if(payload?.isfreeMem){
                if(payload?.isfreemember > payload?.isconmem) {
                    state.data.shop = {
                        ...state.data.shop,
                        is_membership_expired : false,
                    }
                }else {
                    state.data.shop = {
                        ...state.data.shop,
                        is_membership_expired : true,
                    }
                }
            }

            // state.data.planG = {
            //     ...state.data.planG,
            //     [payload.plangroupvariantindex]: {
            //         ...state.data.planG[payload.plangroupvariantindex],
            //         plan_groups: [
            //             {
            //                 ...state.data.planG[payload.plangroupvariantindex].plan_groups[payload.plangroupindex],
            //                 has_manual_membership_count: count ,
            //             },
            //             ...state.data.planG[payload.plangroupvariantindex].plan_groups.slice(1) // keep other plan_groups unchanged
            //         ]
            //     }
            // };

            state.data.planG = {
                ...state.data.planG,
                [payload.plangroupvariantindex]: {
                    ...state.data.planG[payload.plangroupvariantindex],
                    plan_groups: [
                        {
                            ...state.data.planG[payload.plangroupvariantindex].plan_groups[0],
                            has_manual_membership_count : count,
                        },
                    ]
                }
            };
            state.data.planG = Object.values(state.data.planG);

        }
    },
    extraReducers: {
        [getPlanGroup.pending]: (state) => {
            state.isLoading = true;
        },
        [getPlanGroup.fulfilled]: (state, { payload }) => {
            state.isLoading = false;
            state.isSuccess = true;
            state.data.planG = payload?.data?.planG;
            state.data.shop = payload?.data?.shop;
            state.data.products_list = payload?.data?.products_list;
        },
        [getPlanGroup.rejected]: (state, { payload }) => {
            state.isLoading = true;
        },
        [checkActivePlan.pending]: (state) => {
            state.activePlan = [];
        },
        [checkActivePlan.fulfilled]: (state, { payload }) => {

            state.activePlan = payload?.data;
        },
        [checkActivePlan.rejected]: (state, { payload }) => {

            state.activePlan = payload?.data;
        },
        [merchantMigrate.pending]: (state) => {
            // state.isLoading = true;
        },
        [merchantMigrate.fulfilled]: (state, { payload }) => {
            state.isSuccess = true;
            state.isAddMemberSuccess = true;
            toast.success(payload.data.data);
            state.data.shop = payload?.data?.shop;
            state.errors = [];
        },
        [merchantMigrate.rejected]: (state, { payload }) => {
            // state.isLoading = true;
            state.errors = [payload.response.data];
        },
        [updatePriceForSC.pending]: (state) => {
            // state.isLoading = true;
        },
        [updatePriceForSC.fulfilled]: (state, { payload }) => {
            toast.success(payload.data.message);
            state.errors = [];
        },
        [updatePriceForSC.rejected]: (state, { payload }) => {
            // state.isLoading = true;
            state.errors = [payload.response.data];
        },
        [productUpdate.pending]: (state) => {
            // state.isLoading = true;
        },
        [productUpdate.fulfilled]: (state, { payload }) => {
            state.data.shop = payload?.shop;
        },
        [productUpdate.rejected]: (state, { payload }) => {
            // state.isLoading = true;
        },
    },
});

// Action creators are generated for each case reducer function
export const { resetupdateTiers, changePlans, storeReducer, updateTiers, deleteTiers, singleMembers, resteSingleMembers, ismerchantMigrate } =
    plansSlice.actions;

export default plansSlice.reducer;
