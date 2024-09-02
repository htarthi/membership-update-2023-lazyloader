import { createAsyncThunk } from "@reduxjs/toolkit";
import instance from "../../../components/shopify/instance";

export const getMembersList = createAsyncThunk('members/getMembersList', async ({s, f, p, lp, sk, sv, page}) => {
    try {
        const queryString = `?s=${s}&f=${f}&p=${p}&lp=${lp}&sk=${sk}&sv=${sv}&page=${page}`;
        // const { data } = await instance.get(`https://reqres.in/api/users?per_page=2&page=${page}`);
        const { data } = await instance.get(`/subscriber${queryString}`);
        return data;
    } catch (error) {
        return rejectWithValue(error.message);
    }
})
