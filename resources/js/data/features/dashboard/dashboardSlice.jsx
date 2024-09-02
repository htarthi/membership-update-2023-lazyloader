import { createSlice } from "@reduxjs/toolkit"
import Dashboard from "../../../components/pages/Dashborad/Dashboard"

import {
    getData,
    getUserDetails,
    updateAppEmbeded,
    checkMaintainceMode
} from "./dashboardAction";

// import { dashboarddata } from "./dashboardAction";

import CryptoJS from 'crypto-js';


const secretKey = 'sunny';

export const encryptData = (data) => {
    return CryptoJS.AES.encrypt(JSON.stringify(data), secretKey).toString();
};

const initialState = {
    data: {

    },
    getUser: '',
    isLoading: true,
    isSuccess: false,
    errorMessage: "",
    inMaintenance: false

}

export const dashboardSlice = createSlice({
    name: "dashboard",
    initialState,
    extraReducers: {
        [getData.pending]: (state) => {
            state.isLoading = true;
        },
        [getData.fulfilled]: (state, { payload }) => {
            state.isLoading = false;
            state.isSuccess = true;
            state.data = payload;
        },
        [getData.rejected]: (state, { payload }) => {
            state.isLoading = false;
        },
        [updateAppEmbeded.pending]: (state) => {
            state.isLoading = true;
        },
        [updateAppEmbeded.fulfilled]: (state, { payload }) => {
            state.isLoading = false;


        },
        [updateAppEmbeded.rejected]: (state, { payload }) => {
            state.isLoading = false;
        },
        [checkMaintainceMode.pending]: (state) => {
            // state.isLoading = true;
        },
        [checkMaintainceMode.fulfilled]: (state, { payload }) => {


            if (payload.is_maintence === true) {
                state.inMaintenance = 1;
             }
             else{
                state.inMaintenance  = 0;
                if(state.is_secret !== null){
                    const encryptedData = encryptData(payload.is_secret);
                    localStorage.setItem('maintain_secret', encryptedData);
                }

             }
        },
        [checkMaintainceMode.rejected]: (state, { payload }) => {
            // state.isLoading = true;
        },
        [getUserDetails.pending]: (state) => {
        },
        [getUserDetails.fulfilled]: (state, { payload }) => {
            state.getUser = payload;
        },
        [getUserDetails.rejected]: (state, { payload }) => {
        },
    }
})


export default dashboardSlice.reducer;
