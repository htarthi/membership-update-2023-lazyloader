import { createSlice } from "@reduxjs/toolkit";
import { useSelector } from "react-redux";
import { useNavigate } from 'react-router-dom';
import { editEmailBody, getSettingsData, sendEmailTest, setSettingsData, customerCancelMembership, changeBillingPlan, gettranslationData, updateTranslation, getThemeFiles, updateThemeFiles ,addNewReason } from "./settingAction";
import { toast } from "react-toastify";

const initialState = {
    data: {
        data: {
            setting: {},
            failedPaymentNoti: {},
            newSubNoti: {},
            cancelMembershipNoti: {},
            recurringNotifyEmailNoti: {},
            timezone: "Asia/Calcutta",
            portal: {
                transalation: {},
                editcode: {},
            },
            plan: {},
            plans: [],
            user: {},
        },
        themes: [],
    },
    isLoading: false,
    isSuccess: false,
    isSetSettingData: false,
    sentTestSuccess: false,
    isTransaltionsSuccess: false,
    isChangeData : false,
    errors: []
};

export const settingsDataSlice = createSlice({
    name: "settingDetails",
    initialState,
    extraReducers: {
        [getSettingsData.pending]: (state) => {
            state.isChangeData = false;
            state.isLoading = true;
        },
        [getSettingsData.fulfilled]: (state, { payload }) => {
            state.isChangeData = false;
            state.isLoading = false;
            state.isSuccess = true;
            state.data.data = payload?.data?.data
            state.data.themes = payload?.data?.themes
        },
        [getSettingsData.rejected]: (state, { payload }) => {
            state.isChangeData = false;
            state.isLoading = true;
            state.isSuccess = false;
        },

        [setSettingsData.pending]: (state) => {
            state.isSetSettingData = true;
        },
        [setSettingsData.fulfilled]: (state, { payload }) => {
            state.isSetSettingData = false;
            state.isChangeData = false;
            toast.success("Updated successfully");
        },
        [setSettingsData.rejected]: (state, { payload }) => {
            state.isSetSettingData = false;
            state.isChangeData = false;
            toast.error("Something went wrong!");
        },

        [sendEmailTest.pending]: (state) => {
            state.sentTestSuccess = false;
        },
        [sendEmailTest.fulfilled]: (state, { payload }) => {
            state.sentTestSuccess = true;
            state.isChangeData = false;
            toast.success("Updated successfully", {
                position: "bottom-center",
            });
            state.errors = [];
        },
        [sendEmailTest.rejected]: (state, { payload }) => {
            state.sentTestSuccess = false;
            state.isChangeData = false;
            state.errors = [payload];
        },
        [editEmailBody.pending]: (state) => {
            state.isSuccess = false;
        },
        [editEmailBody.fulfilled]: (state, { payload }) => {
            state.isSuccess = true;
            state.isChangeData = false;
            toast.success("Updated successfully", {
                position: "bottom-center",
            });
            state.errors = [];
        },
        [editEmailBody.rejected]: (state, { payload }) => {
            state.isSuccess = false;
            state.errors = [payload.response.data];
        },

        [customerCancelMembership.pending]: (state) => {
            state.isSuccess = false; 0
        },
        [customerCancelMembership.fulfilled]: (state, { payload }) => {
            state.isSuccess = true;
            state.isChangeData = false;
            toast.success("Updated successfully", {
                position: "bottom-center",
            });
            state.errors = [];
        },
        [customerCancelMembership.rejected]: (state, { payload }) => {
            state.sentTestSuccess = false;
            state.errors = [payload];
        },
        [changeBillingPlan.pending]: (state) => {
            state.isSuccess = false; 0
        },
        [changeBillingPlan.fulfilled]: (state, { payload }) => {
            state.isSuccess = true;
            state.errors = [];
            window.top.location.href = payload.data.confirmation_url;
        },
        [changeBillingPlan.rejected]: (state, { payload }) => {
            state.sentTestSuccess = false;
            state.errors = [payload];
        },
        [gettranslationData.pending]: (state) => {
            state.isLoading = true;
        },
        [gettranslationData.fulfilled]: (state, { payload }) => {
            state.isLoading = false;
            state.isSuccess = true;

            state.data.data.portal.transalation = payload?.data
        },
        [gettranslationData.rejected]: (state, { payload }) => {
        },
        [updateTranslation.pending]: (state) => {
            state.isloading = true;
        },
        [updateTranslation.fulfilled]: (state, { payload }) => {
            state.isLoading = false;
            state.isTransaltionsSuccess = true;
            state.errors = [];
            state.data.data.portal.transalation = payload?.data;
            toast.success("Data  Updated Succesfully")
        },
        [updateTranslation.rejected]: (state, { payload }) => {
            state.isTransaltionsSuccess = false;

            state.data.data.portal.transalation.errors = payload;
        },
        [getThemeFiles.pending]: (state) => {
            state.isLoading = true;
        },
        [getThemeFiles.fulfilled]: (state, { payload }) => {
            state.isLoading = false;
            state.data.data.portal.editcode = payload.data
        },
        [getThemeFiles.rejected]: (state) => {
            state.isLoading = fal
            state.errors = []
        },
        [updateThemeFiles.pending]: (state) => {
            state.isLoading = true
        },
        [updateThemeFiles.fulfilled]: (state, payload) => {
            state.isLoading = false;
            state.data.data.portal.languages = payload.data;
            toast.success("Data  Updated Succesfully")
        },
        [updateThemeFiles.rejected]: (state, action) => {
            state.isLoading = flase;
            state.errors = payload.data;
        },
        [addNewReason.pending]: (state) => {
        },
        [addNewReason.fulfilled]: (state, payload) => {
            state.data.data.setting.reasons = payload.payload.data.data.setting.reasons;
        },
        [addNewReason.rejected]: (state, action) => {
        }
    },
    reducers: {
        settingReducer: (state) => {
            state.data = [];
        },
        updateSetting: (state, action) => {
            state.data.data.setting = {
                ...state.data.data.setting,
                ...action.payload,
            };
        },
        updatePortal: (state, action) => {
            state.data.data.portal = {
                ...state.data.data.portal,
                ...action.payload,
            };
        },
        updateEmailTemplate: (state, action) => {
            const { template, updateField } = action.payload;

            state.data.data[template] = {
                ...state.data.data[template],
                ...updateField
            }
        },
        updateTransalationsdata: (state, action) => {
            state.data.data.portal.transalation.languages = {
                ...state.data.data.portal.transalation.languages,
                ...action.payload
            }
        },
        updateEditCodeData: (state, action) => {
            state.data.data.portal.editcode.portal = {
                ...state.data.data.portal.editcode.portal,
                ...action.payload
            }
        },
        resetisSuccess: (state, action) => {
            state.isTransaltionsSuccess = false;
        },
        setChanged : (state, action) => {
            state.isChangeData = true;
        }
    },
});

// Action creators are generated for each case reducer function
export const { settingReducer, updateSetting, updatePortal, updateEmailTemplate, updateTransalationsdata, updateEditCodeData, resetisSuccess , setChanged } = settingsDataSlice.actions;

export default settingsDataSlice.reducer;
