import React, { Suspense,lazy } from "react";
import {
    Routes, Route
} from "react-router-dom";

import GlobalSkeleton from "../GlobalPartials/GlobalSkeleton";
// import Dashboard from "../pages/Dashborad/Dashboard";
// import Members from "../pages/Members/Members";
// import NewMemberDetails from "../pages/MemberDetails/NewMemberDetails";
// import Plans from "../pages/Plans/Plans";
// import CreateProgram from "../pages/CreatePlans/CreateProgram";
// import EditPlans from "../pages/CreatePlans/EditPlans/EditPlans";
// import PlansDetail from "../pages/PlansDetail/PlansDetail";
// import Settings from "../pages/Settings/Settings";
// import Installation from "../pages/Installation/Installation";
// import { Transalation } from "../pages/Settings/partials/Transalation";
// import EditCode from "../pages/Settings/partials/EditCode";
// import Reports from "../pages/Reports/Reports";

//Loader
const Dashboard = lazy(() => import("../pages/Dashborad/Dashboard"));
const Members = lazy(() => import("../pages/Members/Members"));
const NewMemberDetails = lazy(() => import("../pages/MemberDetails/NewMemberDetails"));
const Plans = lazy(() => import("../pages/Plans/Plans"));
const CreateProgram = lazy(() => import("../pages/CreatePlans/CreateProgram"));
const EditPlans = lazy(() => import("../pages/CreatePlans/EditPlans/EditPlans"));
const PlansDetail = lazy(() => import("../pages/PlansDetail/PlansDetail"));
const Settings = lazy(() => import("../pages/Settings/Settings"));
const Installation = lazy(() => import("../pages/Installation/Installation"));
const Transalation = lazy(() => import("../pages/Settings/partials/Transalation"));
const EditCode = lazy(() => import("../pages/Settings/partials/EditCode"));
const Reports = lazy(() => import("../pages/Reports/Reports"));



// const Dashboard = React.lazy(() => import("../pages/Dashborad/Dashboard"));
// const Members = React.lazy(() => import("../pages/Members/Members"));


export default function RoutePath() {

    return (
        <>
            <Suspense fallback={<GlobalSkeleton />}>
                <Routes>
                    <Route exact path='/' element={<Dashboard />} />
                    <Route exact path='/members' element={<Members />} />
                    <Route exact path='/members/:id/edit' element={<NewMemberDetails />} />
                    <Route exact path='/plans' element={<Plans />} />
                    <Route exact path='/create-program' element={<CreateProgram />} />
                    <Route exact path='/plans/:id/edit' element={<PlansDetail />} />
                    <Route exact path='/plans/new' element={<PlansDetail />} />
                    <Route exact path='/reports' element={<Reports />} />

                    {/* <Route exact path='/plans-details' element={<PlansDetail />} /> */}
                    <Route exact path='/settings' element={<Settings />} />
                    <Route exact path='/installation' element={<Installation />} />
                    <Route exact path='/settings/transalations' element={<Transalation/>} />
                    <Route exact path='/settings/editcode' element={<EditCode/>} />

                </Routes>
            </Suspense>
        </>
    )
}
