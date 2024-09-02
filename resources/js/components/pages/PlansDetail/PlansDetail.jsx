import { Icon, Page } from "@shopify/polaris";
import React, { useCallback, useState, useEffect } from "react";
import Breadcrums from "../../GlobalPartials/Breadcrums/Breadcrums";
import ChangeProducts from "../../GlobalPartials/ChangeProducts/ChangeProducts";
import CreateNewTier from "./partials/CreateNewTier";
import { useDispatch, useSelector } from "react-redux";
import {
    AddProduct,
    changeIndex,
    resetIsStoreTiers,
    resetPlanData,
    resetPlanErrors,
    resetisShopifyPlanId,
    sellingPlanExists,
    setProduct,
} from "../../../data/features/plansDetails/plansDetailsSlice";
import { ImageIcon } from "@shopify/polaris-icons";
import {
    checkSellingPlanExists,
    getDefaultPlanData,
    getRestricatedContents,
} from "../../../data/features/plansDetails/planAction";
import { useNavigate, useParams } from "react-router-dom";
import PlanDetailsSkeleton from "./partials/PlanDetailsSkeleton";
import {
    resetupdateTiers,
    storeReducer,
} from "../../../data/features/plans/plansSlice";

export default function PlansDetail() {
    let { id } = useParams();
    const navigate = useNavigate();
    const dispatch = useDispatch();

    const selectedIndex$ = useSelector(
        (state) => state?.plansDetail?.selectedIndex
    );
    const plan_groups$ = useSelector(
        (state) => state?.plansDetail?.data?.plan_groups
    );
    const errors$ = useSelector((state) => state.plansDetail?.errors);
    const isSellingPlanLoading$ = useSelector(
        (state) => state.plansDetail?.isSellingPlanLoading
    );
    const sellingPlanExists$ = useSelector(
        (state) => state.plansDetail?.sellingPlanExists
    );
    const product$ = useSelector((state) => state.plansDetail?.data?.product);
    const newStore$ = useSelector((state) => state.plans?.newStore);
    const products_list$ = useSelector(
        (state) => state?.plans?.data?.products_list
    );
    const isLoading$ = useSelector((state) => state?.plansDetail?.isLoading);

    // ------ ResourcePicker -------
    const [resourcePickerOpen, setResourcePickerOpen] = useState(false);
    // variant selection
    const handleResourceSelection = useCallback(
        (resources) => {
            let id = resources.selection[0].id;
            let val = {
                id: parseInt(id.replace("gid://shopify/Product/", "")),
                name: resources.selection[0].title,
                images: resources.selection[0].images,
            };
            dispatch(setProduct(val));

            // if (newStore$) {
            dispatch(checkSellingPlanExists(val?.id));
            // }
            // Close the ResourcePicker after handling the selection
            setResourcePickerOpen(false);
        },
        [product$, plan_groups$]
    );

    // selling plan is exists then redirect to plan edits
    useEffect(() => {
        if (!isSellingPlanLoading$) {
            if (sellingPlanExists$?.isAvl) {
                navigate(`/plans/${sellingPlanExists$?.id}/edit`);
                dispatch(getDefaultPlanData(sellingPlanExists$?.id));
                dispatch(sellingPlanExists());
                dispatch(storeReducer(false));
            }
            if (sellingPlanExists$?.isAvl == false) {
                dispatch(storeReducer(true));
                dispatch(resetPlanData());
                dispatch(resetPlanErrors());
                dispatch(resetIsStoreTiers(false))
                dispatch(changeIndex(0));
                dispatch(getRestricatedContents());
                navigate('/plans/new');
                dispatch(AddProduct(product$));
            }
        }
    }, [isSellingPlanLoading$, resourcePickerOpen]);

    // resource picker open/close
    const handleResourcePickerToggle = useCallback(() => {
        setResourcePickerOpen(!resourcePickerOpen);
    }, [resourcePickerOpen]);

    // API calling of default planData & reset planErrors
    useEffect(() => {
        dispatch(resetPlanErrors());
        if (id) {
            dispatch(getDefaultPlanData(id));
        }
    }, []);

    // next and previous methods.....
    // let getIndex = products_list$?.findIndex((item) => item == id);

    // prev.....
    // let index = getIndex;
    // const prevMethod = useCallback(() => {
    //     index = index === 0 ? 0 : index - 1;
    //     navigate(`/plans/${products_list$[index]}/edit`);

    //     dispatch(getDefaultPlanData(products_list$[index]));
    //     dispatch(changeIndex(0));
    // }, [products_list$, plan_groups$]);

    // next.....
    // const nextMethod = useCallback(() => {
    //     index =
    //         index == products_list$?.length - 1
    //             ? products_list$?.length - 1
    //             : index + 1;
    //     navigate(`/plans/${products_list$[index]}/edit`);

    //     dispatch(getDefaultPlanData(products_list$[index]));
    //     dispatch(changeIndex(0));
    // }, [products_list$, plan_groups$]);

    // back method.....
    const back = useCallback(() => {
        dispatch(resetPlanData());
        dispatch(changeIndex(0));
        dispatch(resetPlanErrors());
        dispatch(resetupdateTiers());
        dispatch(resetisShopifyPlanId());
    }, [selectedIndex$]);

    return (
        <Page>
            {!isLoading$ ? (
                <div className="plans_details_wrap">
                    <div className="simplee_membership_container">
                        {/* members breadcrums & pagination */}
                        {/* <Breadcrums
                            is_plandetail_show={false}
                            to={"/plans"}
                            title={"This is a plan name"}
                            member_number={""}
                            email={""}
                            phone_number={""}
                            shopify_contract_id={""}
                            status={"active"}
                            showEmail={false}
                            prevMehod={prevMethod}
                            nextMehod={nextMethod}
                            back={back}
                            hasNext={index < products_list$?.length - 1}
                            hasPrev={index >= 1}
                        /> */}

                        {/* product details */}
                        <div className="plans_details_row ms-margin-top">
                            {/* product & Create New Tier */}
                            <div className="plans_details_col">
                                <div className="change_product_wrap">
                                    {/* product image */}
                                    <div className="product_image_wrap">
                                        {product$?.images?.length > 0 ? (
                                            <img
                                                src={
                                                    product$?.images[0]
                                                        ?.originalSrc
                                                }
                                                alt="product image"
                                            />
                                        ) : (
                                            <Icon
                                                source={ImageIcon}
                                                color="base"
                                            />
                                        )}
                                    </div>



                                    {/* text field - change product (Resource Picker) */}
                                    <ChangeProducts
                                        resource="Product"
                                        is_alreadyselected={true}
                                        data={product$.id ? product$.id.toString() : ""}
                                        value={product$?.name}
                                        label={"Product"}
                                        handleResourcePickerToggle={
                                            handleResourcePickerToggle
                                        }
                                        resourcePickerOpen={resourcePickerOpen}
                                        onSelection={handleResourceSelection}
                                        onCancel={() =>
                                            setResourcePickerOpen(false)
                                        }
                                        // inputName={"product"}
                                        // onChange={''}
                                        placeHolder={""}
                                        error={
                                            errors$?.length > 0 &&
                                                !product$?.name
                                                ? errors$[0][
                                                "data.product.id"
                                                ][0]
                                                : ""
                                        }
                                        selectMultiple={false}
                                    />
                                </div>

                                {/* Create New Tier */}
                                <CreateNewTier />
                            </div>
                        </div>
                    </div>
                </div>
            ) : (
                <PlanDetailsSkeleton />
            )}
        </Page>
    );
}
