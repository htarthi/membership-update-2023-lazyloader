import { createSlice } from '@reduxjs/toolkit'

const initialState = {
    lang: 'en',
    currency: "CAD",
}

export const appSlice = createSlice({
    name: 'members',
    initialState,
    reducers: {
        changeLang: (state, action) => {
            state.lang = action.payload
        },
    },
})

export const { changeLang } = appSlice.actions
export default appSlice.reducer
