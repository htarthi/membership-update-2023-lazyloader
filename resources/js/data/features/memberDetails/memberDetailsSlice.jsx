import { createSlice } from "@reduxjs/toolkit";
import { billingAttempts, commentPost, questionAnswerUpdate, shippingUpdate, subscribeUpdate, subscriberEdit } from "./membersDetailsAction";
import { toast } from "react-toastify";

const initialState = {
    data: {
        availableDates: [],
        contract: {},
        otherContracts: [],
        shop: {},
        contracts_list: []
    },
    isLoading: false,
    isSuccess: false,
    isMemberActivity: false
};

export const memberDetailsSlice = createSlice({
    name: "memberDetails",
    initialState,
    extraReducers: {
        [subscriberEdit.pending]: (state) => {
            state.isLoading = true;
        },
        [subscriberEdit.fulfilled]: (state, { payload }) => {
            state.isLoading = false;
            state.isSuccess = true;
            state.data = payload?.data;
        },
        [subscriberEdit.rejected]: (state, { payload }) => {
            state.isLoading = false;
            state.errorMessage = payload;
        },
        [billingAttempts.pending]: (state) => {
        },
        [billingAttempts.fulfilled]: (state, { payload }) => {
            state.data.contract.billingAttempt = payload?.data;
        },
        [billingAttempts.rejected]: (state, { payload }) => {
            state.errorMessage = payload;
        },
        [commentPost.pending]: (state) => {
            state.isSuccess = false;
        },
        [commentPost.fulfilled]: (state, { payload }) => {
            state.isSuccess = true;
            state.data.contract.activityLog.unshift(payload.data);
            toast.success(payload.msg);
        },
        [commentPost.rejected]: (state, { payload }) => {
            state.isSuccess = false;
            toast.error(payload.message);
        },
        [subscribeUpdate.pending]: (state) => {
            state.isLoading = true;
        },
        [subscribeUpdate.fulfilled]: (state, { payload }) => {
            state.isLoading = false;
            state.isMemberActivity = true;
            if(payload?.isSuccess){
                const resPonseMsgSuccess = payload?.data ?  payload?.data : "Updated successfully" ;
                toast.success(resPonseMsgSuccess);
            }else{
                const resPonseMsgError = payload?.data ?  payload?.data : "Something went to wrong ." ;
                toast.error(resPonseMsgError);
            }
        },
        [subscribeUpdate.rejected]: (state, { payload }) => {
            state.isLoading = false;
        },
        [shippingUpdate.pending]: (state) => {
        },
        [shippingUpdate.fulfilled]: (state, { payload }) => {
            toast.success(payload.msg);
        },
        [shippingUpdate.rejected]: (state, { payload }) => {
        },
        [questionAnswerUpdate.pending]: (state) => {
            state.isLoading = true;
        },
        [questionAnswerUpdate.fulfilled]: (state, { payload }) => {
            state.isLoading = false;
            toast.success(payload.data);
        },
        [questionAnswerUpdate.rejcted]: (state, { payload }) => {
            state.isLoading = false;
        }
    },
    reducers: {
        memberDetails: (state) => {
            state.data = [];
        },
        updatememberActivity: (state, action) => {
            state.isMemberActivity = false;
        },
        contractReducer: (state, action) => {
            state.data.contract = {
                ...state.data.contract,
                ...action.payload,
            };
        },
        customerReducer: (state, action) => {
            state.data.contract.customer = {
                ...state.data.contract.customer,
                ...action.payload,
            };
        },
        lineItemsReducer: (state, action) => {
            state.data.contract.lineItems[0] = {
                ...state.data.contract.lineItems[0],
                ...action.payload,
            };
        },
        QuestionAnswerReducer: (state, action) => {
            state.data.contract.customer_answer[action.payload.index] = {
                ...state.data.contract.customer_answer[action.payload.index],
                ...action.payload.data
            }
        },
    },
});

// Action creators are generated for each case reducer function
export const {
    memberDetails,
    updatememberActivity,
    contractReducer,
    customerReducer,
    lineItemsReducer,
    QuestionAnswerReducer
} = memberDetailsSlice.actions;

export default memberDetailsSlice.reducer;
