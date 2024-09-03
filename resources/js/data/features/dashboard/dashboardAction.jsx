import { createAsyncThunk } from "@reduxjs/toolkit";

import instance from "../../../components/shopify/instance";

export const getData = createAsyncThunk('dashboard', async () => {

    try {
        const { data } = await instance.get(`/dashboard`);
        return data;
    } catch (error) {
        return rejectWithValue(error.message);
    }
})

export const updateAppEmbeded = createAsyncThunk('updateOrderAmountPrice',async()=>{
    try {
        const { data } = await instance.post(`/updateOrderAmountPrice`);
        return data;
    } catch (error) {
        return rejectWithValue(error.message);
    }
})

export const checkMaintainceMode = createAsyncThunk('checkMaintainceMode', async (paramValue, { rejectWithValue }) => {
    try {
        const { data } = await instance.get(`/in-maintanace?secret=${paramValue}`);
        return data
    } catch (error) {
        return rejectWithValue(error);
    }
})
export const getUserDetails = createAsyncThunk('getUserDetails',async()=>{
    try {
        const { data } = await instance.get(`/getUserDetails`);
        return data;
    } catch (error) {
        return rejectWithValue(error.message);
    }
})


