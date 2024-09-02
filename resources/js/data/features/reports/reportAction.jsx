import { createAsyncThunk, isRejectedWithValue } from "@reduxjs/toolkit";
import instance from "../../../components/shopify/instance";
import { search } from "@shopify/app-bridge/actions/Picker";

export const getData = createAsyncThunk("reports", async (period) => {
    try {
        const { data } = await instance.get(`/other-reports?period=${period}`)
        return data;
    } catch (error) {
        return isRejectedWithValue(error.message)
    }
})

export const getUpcomingRenewals = createAsyncThunk("upcoming_renewals", async ({ s, p, lp, sk, sv, page, em }) => {
    try {
        const queryString = `?s=${s}&p=${p}&lp=${lp}&em=${em}&sk=${sk}&sv=${sv}&page=${page}`;
        const { data } = await instance.get(`/upcoming-renewals${queryString}`)
        return data
    } catch (error) {
        return isRejectedWithValue(error.message);
    }
})

export const recentBillingAttempts = createAsyncThunk("recent_billing_attempts", async ({ s, p, lp, em, sk, sv, page }) => {
    try {
        const queryString = `?s=${s}&p=${p}&lp=${lp}&em=${em}&sk=${sk}&sv=${sv}&page=${page}`;
        const { data } = await instance.get(`/recent_billing_attempts${queryString}`)
        return data;
    } catch (error) {
        return isRejectedWithValue(error.message);
    }
})

export const activePlans = createAsyncThunk("active_plans", async () => {
    try {
        const { data } = await instance.get('/ActivePlans');
        return data;
    } catch (error) {
        return isRejectedWithValue(error.message);
    }
})

export const newestMembers = createAsyncThunk("newest_members", async ({ s, p, lp, em, sk, sv, page }) => {
    try {
        const queryString = `?s=${s}&p=${p}&lp=${lp}&em=${em}&sk=${sk}&sv=${sv}&page=${page}`;
        const { data } = await instance.get(`/newest_members${queryString}`)
        return data;
    } catch (error) {
        return isRejectedWithValue(error.message);
    }
})

export const recentCancellations = createAsyncThunk("recent_cancellation", async ({ s, p, lp, em, sk, sv, page }) => {
    try {
        const queryString = `?s=${s}&p=${p}&lp=${lp}&em=${em}&sk=${sk}&sv=${sv}&page=${page}`;
        const { data } = await instance.get(`/recent_cancellation${queryString}`)
        return data;
    } catch (error) {
        return isRejectedWithValue(error.message);
    }
})
