import { createAsyncThunk } from "@reduxjs/toolkit";
import instance from "../../../components/shopify/instance";


export const getDefaultPlanData = createAsyncThunk('plan/getDefaultPlanData', async (planGroupId, { rejectWithValue }) => {
    try {
        const { data } = await instance.get(`/plan/${planGroupId}/edit`);
        return data;
    } catch (error) {
        return rejectWithValue(error);
    }
})

export const getRestricatedContents = createAsyncThunk('restricated_contents', async () => {
    try {
        const { data } = await instance.get(`/restricated-contents`);
        return data;
    } catch (error) {
        return rejectWithValue(error);
    }
})

export const checkSellingPlanExists = createAsyncThunk('plan/checkSellingPlanExists', async (planGroupId, { rejectWithValue }) => {
    try {
        const { data } = await instance.get(`/check-is-selling-plan-exists/${planGroupId}`);
        return data;
    } catch (error) {
        return rejectWithValue(error);
    }
})

export const storePlanGroupV1 = createAsyncThunk('plan/storePlanGroupV1', async (planData, { rejectWithValue }) => {
    try {
        const { data } = await instance.post(`/plan`, planData);
        return data;
    } catch (error) {
        return rejectWithValue(error.response.data);
    }
})

export const storePlanGroup = createAsyncThunk('plan/storePlanGroup', async (planData, { rejectWithValue }) => {
    try {
        const { data } = await instance.post(`/tiers/store`, planData);
        return data;
    } catch (error) {
        return rejectWithValue(error.response.data);
    }
})


export const deleteTier = createAsyncThunk('plan/deleteTier', async (planId, { rejectWithValue }) => {
    try {
        const { data } = await instance.delete(`/plan-group/${planId}/delete`);
        return data;
    } catch (error) {
        return rejectWithValue(error.response.data);
    }
})

