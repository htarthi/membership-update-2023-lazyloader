import { createSlice } from "@reduxjs/toolkit";
import { getMembersList } from "./membersAction";


const initialState = {
    data: {
        activePlans: [],
        memberships: {},
        shop: ""
    },
    isLoading: false,
    isSuccess: false,
    errorMessage: "",
    defaultFilter: {
        s: '',
        f: 'all',
        p: '',
        lp: '',
        sk: 'created_at',
        sv: '',
        page: 1
    }
};

export const membersSlice = createSlice({
    name: "members",
    initialState,
    extraReducers: {
        [getMembersList.pending]: (state) => {
            state.isLoading = false;
        },
        [getMembersList.fulfilled]: (state, { payload }) => {

            state.isLoading = false;
            state.isSuccess = true;
            state.data.memberships = payload?.data?.memberships;
            state.data.activePlans = payload?.data?.activePlans;
            state.data.shop = payload?.data?.shop;
        },
        [getMembersList.rejected]: (state, { payload }) => {
            state.isLoading = false;
            state.isSuccess = false;
            state.errorMessage = payload;
        },
    },
    reducers: {
        changeMembers: (state) => {
            state.data.members = [];
        },
        memberSortREducer: (state, action) => {
            const { order } = action.payload;
            state.data.memberships.data = state.data.memberships.data.sort(
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
        }
    },
});

// Action creators are generated for each case reducer function
export const { changeMembers, memberSortREducer, defultFilterUpdate } = membersSlice.actions;
export default membersSlice.reducer;
