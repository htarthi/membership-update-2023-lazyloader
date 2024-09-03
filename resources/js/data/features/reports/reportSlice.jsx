import { createSlice } from "@reduxjs/toolkit";
import { getData, getUpcomingRenewals, recentBillingAttempts,activePlans, newestMembers ,recentCancellations } from "./reportAction";

const initialState = {
    data: {
        other_reports: [

        ],
        upcoming_renewals: [],
        recent_biilling_attempts: [],
        newest_members: [],
        recent_cancellation: [],
    },
    isLoading: true,
    isSuccess: false,
    errorMessage: "",
    defaultFilter: {
        s: '',
        f: 'all',
        p: '',
        lp: '',
        sk: 'next_processing_date',
        sv: '',
        em : '',
        page: 1
    },
    ActivePlans:[]

}
export const reportsSlice = createSlice({
    name: "reports",
    initialState,
    extraReducers: {
        [getData.pending]: (state) => {
            state.isLoading = true;
        },
        [getData.fulfilled]: (state, { payload }) => {
            state.data.other_reports = payload

            // state.shopDomain = payload.shop?.id;///////////////////hitarthi
            state.isLoading = false;
            state.isSuccess = true;
        },
        [getData.rejected]: (state) => {
            state.isLoading = false;
        },
        [getUpcomingRenewals.pending]: (state) => {
            // state.isLoading = true;
        },
        [getUpcomingRenewals.fulfilled]: (state, { payload }) => {
            // state.isLoading = false;
            // state.isSuccess = true;
            state.data.upcoming_renewals = payload?.data;

        },
        [getUpcomingRenewals.rejected]: (state, { payload }) => {
            // state.isLoading = false;
            state.errorMessage = payload
        },
        [recentBillingAttempts.pending]: (state) => {
            // state.isLoading = false;
        },
        [recentBillingAttempts.fulfilled]: (state, { payload }) => {
            // state.isLoading = false;
            // state.isSuccess = true;
            state.data.recent_biilling_attempts = payload?.data;
        },
        [recentBillingAttempts.rejected]: (state, { payload }) => {
            state.isLoading = false;
            state.errorMessage = payload
        },
        [activePlans.pending]: (state) => {
            // state.isLoading = false;
        },
        [activePlans.fulfilled]: (state, { payload }) => {
            // state.isLoading = false;
            // state.isSuccess = true;
            state.ActivePlans = payload?.data;
        },
        [activePlans.rejected]: (state, { payload }) => {
            state.isLoading = false;
            state.errorMessage = payload
        },
        [newestMembers.pending]: (state) => {
            // state.isLoading = false;
        },
        [newestMembers.fulfilled]: (state, { payload }) => {
            // state.isLoading = false;
            // state.isSuccess = true;
            state.data.newest_members = payload?.data;
        },
        [newestMembers.rejected]: (state, { payload }) => {
            state.isLoading = false;
            state.errorMessage = payload
        },
        [recentCancellations.pending]: (state) => {
            // state.isLoading = false;
        },
        [recentCancellations.fulfilled]: (state, { payload }) => {
            state.data.recent_cancellation = payload?.data;
        },
        [recentCancellations.rejected]: (state, { payload }) => {
            state.isLoading = false;
            state.errorMessage = payload
        },


    },
    reducers: {
        upcomingSortReducer: (state, action) => {
            const { order } = action.payload;
            state.data.upcoming_renewals.data = state.data.upcoming_renewals.data.sort(
                (a, b) => {
                    const isReversed = order.split(" ")[1] === "asc" ? 1 : -1;
                    return order.split(" ")[0] === "first_name"
                        ? isReversed * (a.first_name - b.first_name)
                        : order.split(" ")[0] === "last_name"
                            ? isReversed * (a.last_name - b.last_name) :
                            order.split(" ")[0] === "next_order_date" ?
                                isReversed * (a.next_order_date - b.next_order_date) : ''
                }
            );
        },
        defultFilterUpdate: (state, action) => {
            const defaultFilter = action.payload;
            state.defaultFilter = {
                ...state.defaultFilter,
                ...defaultFilter
            }
        },
        filterReset: (state) => {
            state.defaultFilter = {
                s: '',
                f: 'all',
                p: '',
                lp: '',
                sk: '',
                sv: '',
                em : '',
                page: 1
            }
        }
    }
})


export const { upcomingSortReducer, defultFilterUpdate ,filterReset} = reportsSlice.actions;

export default reportsSlice.reducer;
