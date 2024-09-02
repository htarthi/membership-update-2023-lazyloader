import { configureStore } from '@reduxjs/toolkit'

import membersReducer from './features/members/membersSlice'
import memberDetailsReducer from './features/memberDetails/memberDetailsSlice'
import appReducer from './features/app/appSlice'
import plansSlice from './features/plans/plansSlice'
import plansDetailsSlice from './features/plansDetails/plansDetailsSlice'
import settingsDataSlice from './features/settings/settingsDataSlice'
import installationSlice from './features/installation/installationSlice'
import dashboardSlice from './features/dashboard/dashboardSlice'
import reportSlice from './features/reports/reportSlice'



export const store = configureStore({
    reducer: {
        members: membersReducer,
        memberDetails: memberDetailsReducer,
        app: appReducer,
        plans: plansSlice,
        plansDetail: plansDetailsSlice,
        settings: settingsDataSlice,
        installation: installationSlice,
        dashboard:dashboardSlice,
        reports:reportSlice

    },
    middleware: (getDefaultMiddleware) => getDefaultMiddleware({ serializableCheck: false })
})
