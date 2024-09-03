import { Page } from "@shopify/polaris";
import React, { useState, useCallback, useEffect, lazy, Suspense } from "react";
import { LegacyCard } from "@shopify/polaris";
import { useSelector } from "react-redux";
import SubHeader from "../../GlobalPartials/SubHeader/SubHeader";
import { useNavigate } from "react-router-dom";
// import PlanProductDetail from "./partials/PlanProductDetail";
// import PlansTierList from "./partials/PlansTierList";
// import EmptyPage from "../../GlobalPartials/EmptyPage/EmptyPage";
// import AddMembersModal from "./partials/AddMembersModal";
import { useDispatch } from "react-redux";
import { getPlanGroup } from "../../../data/features/plans/planAction";
import { keyBy } from "lodash";
import {
    changeIndex,
    resetIsDeleteSuccess,
    resetIsStoreTiers,
    resetPlanData,
    resetPlanErrors,
} from "../../../data/features/plansDetails/plansDetailsSlice";
import {
    deleteTiers,
    storeReducer,
} from "../../../data/features/plans/plansSlice";
// import DeleteModal from "../../GlobalPartials/DeleteModal/DeleteModal";
// import PlansSkeleton from "./partials/PlansSkeleton";
import {
    deleteTier,
    getRestricatedContents,
} from "../../../data/features/plansDetails/planAction";
import { PlansLength } from "./partials/PlansLength";

const PlanProductDetail = lazy(() => import("./partials/PlanProductDetail"));
const PlansTierList = lazy(() => import("./partials/PlansTierList"));
const EmptyPage = lazy(() =>import("../../GlobalPartials/EmptyPage/EmptyPage"));
const AddMembersModal = lazy(() => import("./partials/AddMembersModal"));
const DeleteModal = lazy(() =>import("../../GlobalPartials/DeleteModal/DeleteModal"));
const PlansSkeleton = lazy(() =>import("./partials/PlansSkeleton"));

export default function Plans() {
    const navigate = useNavigate();
    const dispatch = useDispatch();

    // Redux Data
    let plans = useSelector((state) => state.plans?.data?.planG);
    const newStore$ = useSelector((state) => state.plans?.newStore);
    const plansDetail$ = useSelector((state) => state.plansDetail);
    const isDeleteSuccess$ = useSelector(
        (state) => state?.plansDetail?.isDeleteSuccess
    );
    const isLoading$ = useSelector((state) => state?.plans?.isLoading);
    let shop = useSelector((state) => state.plans?.data?.shop);

    // const[plans,setplans] = useState(useSelector((state) => state.plans?.data?.planG));

    // fetch plan group data
    useEffect(() => {
        dispatch(getPlanGroup());
    }, []);

    // add members modal
    const [active, setActive] = useState(false);
    const [ssPlanGroupId, setSsPlanGroupId] = useState("");
    const [plangroupvariantindex, setplangroupvariantindex] = useState("");
    const [plangroupindex, setplangroupindex] = useState("");
    const [membership_count, setmembership_count] = useState("");

    const [planGName, setPlanGName] = useState("");

    const handleAddMembersChange = useCallback(
        (id, name, index, key, membership_count) => {
            setmembership_count(membership_count);
            setplangroupvariantindex(key);
            setplangroupindex(index);
            setPlanGName(name);
            setSsPlanGroupId(id);
            setActive(!active);
        },
        [active, ssPlanGroupId]
    );

    // Group the data by 'shopify_product_id'
    const groupedData = plans?.reduce((arr, item) => {
        if (!arr[item.shopify_product_id]) {
            arr[item.shopify_product_id] = [];
        }
        arr[item.shopify_product_id].push(...item.plan_groups);
        return arr;
    }, {});

    // create plan
    const createPlan = useCallback(() => {
        console.log("dsada");
        dispatch(storeReducer(true));
        dispatch(getRestricatedContents());
        dispatch(resetPlanData());
        dispatch(resetPlanErrors());
        dispatch(resetIsStoreTiers(false));
        dispatch(changeIndex(0));
        navigate("/plans/new");
    }, [newStore$, plansDetail$]);

    const [deleteModal, setDeleteModal] = useState(false);
    const [deleteId, setDeleteId] = useState();

    // open delete modal
    const handleDeletePlan = useCallback(
        (id) => {
            setDeleteModal(!deleteModal);
            setDeleteId(id);
        },
        [deleteModal, deleteId]
    );

    // delete plan
    const deletePlan = useCallback(() => {
        dispatch(deleteTier(deleteId));
        setDeleteModal(!deleteModal);
    }, [plans, deleteId]);

    useEffect(() => {
        if (isDeleteSuccess$ && !deleteModal) {
            dispatch(deleteTiers(deleteId));
            dispatch(resetIsDeleteSuccess());
        }
    }, [isDeleteSuccess$, deleteId]);
    return (
        <>
            {!isLoading$ ? (
                <>
                    {plans?.length ? (
                        <>
                            <SubHeader
                                title="Plans"
                                secondButtonState={true}
                                secButtonName={"Create Plan"}
                                buttonHandleEvent={() => createPlan()}
                                isPlanExport={true}
                            />

                            <Page fullWidth>
                                <div className="plans_list_wrap">
                                    <div className="simplee_membership_container">
                                        {Object.keys(groupedData)?.map(
                                            (shopify_product_id, key) => {
                                                return (
                                                    <div
                                                        className="plans_card_wrap"
                                                        key={key}
                                                    >
                                                        <LegacyCard>
                                                            {/* plans product detail */}
                                                            <Suspense
                                                                fallback={
                                                                    <PlansSkeleton />
                                                                }
                                                            >
                                                                <PlanProductDetail
                                                                    plan={
                                                                        keyBy(
                                                                            plans,
                                                                            "shopify_product_id"
                                                                        )[
                                                                            shopify_product_id
                                                                        ]
                                                                    }
                                                                />
                                                            </Suspense>
                                                            {/* tier list */}
                                                            <Suspense
                                                                fallback={
                                                                    <PlansSkeleton />
                                                                }
                                                            >
                                                                <PlansTierList
                                                                    planGroup={
                                                                        groupedData[
                                                                            shopify_product_id
                                                                        ]
                                                                    }
                                                                    shopify_product_id={
                                                                        shopify_product_id
                                                                    }
                                                                    handleAddMembersChange={
                                                                        handleAddMembersChange
                                                                    }
                                                                    handleDeletePlan={
                                                                        handleDeletePlan
                                                                    }
                                                                    lengths={
                                                                        groupedData[
                                                                            shopify_product_id
                                                                        ]
                                                                    }
                                                                    plang_key={
                                                                        shopify_product_id
                                                                    }
                                                                />
                                                            </Suspense>

                                                            {/* <PlansLength
                                                                    lengths={groupedData[shopify_product_id][0]['has_many_plan']}
                                                                /> */}
                                                        </LegacyCard>
                                                    </div>
                                                );
                                            }
                                        )}
                                    </div>
                                </div>
                            </Page>
                        </>
                    ) : (
                        <Suspense fallback={<PlansSkeleton />}>
                            <EmptyPage showButton={true} />
                        </Suspense>
                    )}
                </>
            ) : (
                <PlansSkeleton />

            )}

            {/* add members modal */}
            <Suspense fallback={<PlansSkeleton />}>
                <AddMembersModal
                    setActive={setActive}
                    active={active}
                    ssPlanGroupId={ssPlanGroupId}
                    planGName={planGName}
                    plangroupvariantindex={plangroupvariantindex}
                    plangroupindex={plangroupindex}
                    membership_count={membership_count}
                />
            </Suspense>

            {/* delete modal */}
            <Suspense fallback={<PlansSkeleton />}>
                <DeleteModal
                    active={deleteModal}
                    setDeleteModal={setDeleteModal}
                    deleteMethod={deletePlan}
                />
            </Suspense>
        </>
    );
}
