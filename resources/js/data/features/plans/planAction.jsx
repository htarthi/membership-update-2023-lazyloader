import { createAsyncThunk } from "@reduxjs/toolkit";
import instance from "../../../components/shopify/instance";

export const getPlanGroup = createAsyncThunk('plan-group', async () => {
    try {
        const { data } = await instance.get(`/plan-group`);
        return data;
    } catch (error) {
        return rejectWithValue(error);
    }
})

export const merchantMigrate = createAsyncThunk('merchantMigrate', async (merchantMigrateData, { rejectWithValue }) => {
    try {
        const { data } = await instance.post(`/merchantmigrate`, merchantMigrateData);
        return data;
    } catch (error) {
        return rejectWithValue(error);
    }
})

export const updatePriceForSC = createAsyncThunk('updatePriceForSC', async (updateSCPriceData, { rejectWithValue }) => {
    try {
        const { data } = await instance.post(`/updatePriceForSC`, updateSCPriceData);
        return data;
    } catch (error) {
        return rejectWithValue(error);
    }
})

export const checkActivePlan = createAsyncThunk('check-activePlan', async () => {
    try {
        const { data } = await instance.get(`/check-activePlan`);
        return data;
    } catch (error) {
        return rejectWithValue(error);
    }
})

export const productUpdate = createAsyncThunk('productupdate', async () => {
    try {
        const { data } = await instance.get('/update-products');
        return data
    } catch (error) {
        return rejectWithValue(error);
    }
})


