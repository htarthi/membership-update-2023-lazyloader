import { createSlice } from "@reduxjs/toolkit";
import { getThemes, enableHiddenContent } from "./installationAction";
import { toast } from "react-toastify";

const initialState = {
    data: {
        eligibleForSubscriptions: false,
        themes: [],
        options: []
    },
    isLoading: false,
    isSuccess: false,
    isEnableContent: false,
};

export const installationSlice = createSlice({
    name: "installation",
    initialState,
    extraReducers: {
        [getThemes.pending]: (state) => {
            state.isLoading = true;
        },
        [getThemes.fulfilled]: (state, { payload }) => {
            state.isLoading = false;
            state.isSuccess = true;
            state.data = payload?.data;
        },
        [getThemes.rejected]: (state, { payload }) => {
            state.isLoading = true;
            state.errorMessage = payload;
        },
        [enableHiddenContent.pending]: (state) => {
            state.isEnableContent = true;
        },
        [enableHiddenContent.fulfilled]: (state, { payload }) => {
            state.isEnableContent = false;
            toast.success(payload.data);
        },
        [enableHiddenContent.rejected]: (state, { payload }) => {
            state.isEnableContent = false;
            state.errorMessage = payload;
            toast.error(payload?.msg);
        }
    },
    reducers: {
        installtionDetailsReducer: (state) => {
            state.data = [];
        }
    },
});

export const {
    installtionDetailsReducer
} = installationSlice.actions;

export default installationSlice.reducer;
