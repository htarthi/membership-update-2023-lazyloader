import { createAsyncThunk } from "@reduxjs/toolkit";
import instance from "../../../components/shopify/instance";

export const getThemes = createAsyncThunk('getThemes', async () => {
    try {
        const { data } = await instance.get(`/get-installation-config`);
        return data;
    } catch (error) {
        return rejectWithValue(error.message);
    }
})

export const enableHiddenContent = createAsyncThunk('enableHiddenConent', async (payload) => {
    try {
        const { data } = await instance.post(`/install-widget`, payload);
        return data;
    } catch (error) {
        return rejectWithValue(error.message);
    }
})

