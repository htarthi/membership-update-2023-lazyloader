import { createAsyncThunk } from "@reduxjs/toolkit";
import instance from "../../../components/shopify/instance";

export const getSettingsData = createAsyncThunk('settings', async () => {
    try {
        const { data } = await instance.get(`/setting`);
        return data;
    } catch (error) {
        return rejectWithValue(error);
    }
})

export const setSettingsData = createAsyncThunk('setSettingsData', async (settingData, { rejectWithValue }) => {
    try {
        const { data } = await instance.post(`/setting`, settingData);
        return data;
    } catch (error) {
        return rejectWithValue(error);
    }
})

export const editEmailBody = createAsyncThunk('/email-body', async (emailData, { rejectWithValue }) => {
    try {
        const { data } = await instance.post(`/email-body`, emailData);
        return data;
    } catch (error) {
        return rejectWithValue(error);
    }
})

export const sendEmailTest = createAsyncThunk('/mail', async (sendTestData, { rejectWithValue }) => {
    try {
        const { data } = await instance.post(`/mail`, sendTestData);
        return data;
    } catch (error) {
        return rejectWithValue(error.response.data);
    }
})

export const customerCancelMembership = createAsyncThunk('portal-status', async (cancelMembershipData, { rejectWithValue }) => {

    try {
        const val = cancelMembershipData.portal_can_cancel;
        const { data } = await instance.get(`portal-status?key=portal_can_cancel&v=${val}`);
        return data;
    } catch (error) {
        return rejectWithValue(error.response.data);
    }
})

export const changeBillingPlan = createAsyncThunk('mbilling/', async (changeBillingPlanData, { rejectWithValue }) => {

    try {
        const payload = changeBillingPlanData;
        const { data } = await instance.get(`pbilling/${payload.plan_id}/${payload.user_name}`);
        return data;
    } catch (error) {
        return rejectWithValue(error.response.data);
    }
})

export const gettranslationData = createAsyncThunk('/translations', async (transalationData, { rejectWithValue }) => {
    try {
        const payload = transalationData;
        const { data } = await instance.get('/translations');
        return data;
    } catch (error) {
        return rejectWithValue(error.response.data)
    }
})


export const updateTranslation = createAsyncThunk('/updatetranslations', async (updateTranslationData, { rejectWithValue }) => {
    try {
        const payload = updateTranslationData;
        const { data } = await instance.post('/translations', { "data": payload });
        return data
    } catch (error) {
        return rejectWithValue(error.response.data)

    }
})

export const getThemeFiles = createAsyncThunk('getThemeFiles', async () => {
    try {
        const { data } = await instance.get('/get-theme-files')
        return data
    } catch (error) {
        return error.response.data
    }
})

export const updateThemeFiles = createAsyncThunk('/updateThemeFile', async (updateThemeFilesData, { rejectWithValue }) => {
    try {
        const { data } = await instance.post('/store-theme-files', { data: updateThemeFilesData })
        return data;
    } catch (error) {
        return error.response.data
    }
})


export const addNewReason = createAsyncThunk('/addNewReason', async (deleteReasonData, { rejectWithValue }) => {
    try {
        const payload = deleteReasonData;
        const { data } = await instance.post('/addNewReason', { "data": payload });
        return data
    } catch (error) {
        return rejectWithValue(error.response.data)

    }
})

