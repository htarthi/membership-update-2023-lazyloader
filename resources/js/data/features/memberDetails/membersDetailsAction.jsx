import { createAsyncThunk } from "@reduxjs/toolkit";
import instance from "../../../components/shopify/instance";

export const subscriberEdit = createAsyncThunk('subscriberEdit', async ({ page, id }) => {
    try {
        const queryString = `?page=${page ? page : 1}`;
        const { data } = await instance.get(`/subscriber/${id}/edit${queryString}`);
        return data;
    } catch (error) {
        return rejectWithValue(error.message);
    }
})

export const billingAttempts = createAsyncThunk('billingAttempts', async ({ page, id }) => {
    try {
        const queryString = `?page=${page ? page : 1}`;
        const { data } = await instance.get(`/billng-attempts/${id}${queryString}`);
        return data;
    } catch (error) {
        return rejectWithValue(error.message);
    }
})

export const commentPost = createAsyncThunk('commentPost', async (CommentData, { rejectWithValue }) => {
    try {
        const { data } = await instance.post(`/save-comment`, CommentData);
        return data;
    } catch (error) {
        return rejectWithValue(error.response.data);
    }
})

export const subscribeUpdate = createAsyncThunk('subscribeUpdate', async ({ id, subscribeData }, { rejectWithValue }) => {
    try {
        const { data } = await instance.patch(`/subscriber/${id}`, subscribeData);
        return data;
    } catch (error) {
        return rejectWithValue(error.response.data);
    }
})

export const shippingUpdate = createAsyncThunk('shippingUpdate', async ({ id, payload }, { rejectWithValue }) => {
    try {
        const { data } = await instance.put(`/subscriber/${id}/shipping/update`, payload);
        return data;
    } catch (error) {
        return rejectWithValue(error.response.data);
    }
})

export const questionAnswerUpdate = createAsyncThunk('questionAnswerUpdate', async ({ id, payload }, { rejectWithValue }) => {

    try {
        const { data } = await instance.patch(`/subscriber/${id}`, { data: payload });
        return data;
    } catch (error) {
        return rejectWithValue(error.response.data)
    }
})
