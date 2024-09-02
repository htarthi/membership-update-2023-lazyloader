import { createSlice } from '@reduxjs/toolkit'

const initialState = {
    data: {
        programs: [
            {
                product_info: {
                    product_title: "GOLD",
                    shopify_product_id: 6813705666718,
                },
                tiers: [
                    {
                        tier_name: "New Tier One",
                        tag_customer: "Test_Tag",
                        tag_order: "Order",
                        active_rules: 10,
                        active_form_fields: 3,
                        selling_plans: [
                            {
                                delivery_interval: "month",
                                delivery_interval_count: 1,
                                billing_interval: "month",
                                billing_interval_count: 1,
                                shopify_plan_id: "2671968443",
                                pricing_adjustment_value: 50,
                                pricing_adjustment_type: "PRICE",
                                pricing_adjustment_value: 500,

                            }
                        ]
                    }
                ],
            }
        ],
    },
    isLoading: false,
    isSuccess: false,
    errorMessage: ''
}

export const programsSlice = createSlice({
    name: 'programs',
    initialState,
    extraReducers: {

    },
    reducers: {
        changePrograms: (state) => {
            state.data.programs = []
        },
    },
})

// Action creators are generated for each case reducer function
export const { changePrograms } = programsSlice.actions

export default programsSlice.reducer
