import React, { useCallback } from 'react'
import { Button, ButtonGroup, Icon, Text } from '@shopify/polaris'
import product from "../../../../../images/product.png"
import { useNavigate } from 'react-router-dom'
import { EditIcon } from '@shopify/polaris-icons';
import { useSelector } from 'react-redux';
import { useDispatch } from 'react-redux';
import { storeReducer } from '../../../../data/features/plans/plansSlice';
import { Link } from '@shopify/polaris';

export default function PlanProductDetail({ plan }) {
    const navigate = useNavigate();
    const dispatch = useDispatch();
    const newStore$ = useSelector((state) => state.plans?.newStore);

    const editPlan = useCallback(() => {
        dispatch(storeReducer(false));
    }, [newStore$])

    const shop$ = useSelector((state) => state?.plans?.data?.shop);
    const domain = shop$?.domain;
    const myshopify_domain = shop$?.myshopify_domain;
    // const url = "https://admin.shopify.com/store/" + (domain?.replace(".myshopify.com", "") || "") + "/products/" + plan?.shopify_product_id;
    let url = "";
    if (myshopify_domain?.includes(".myshopify.com")) {
        url = "https://admin.shopify.com/store/" + (myshopify_domain?.replace(".myshopify.com", "") || "")  + "/products/" + plan?.shopify_product_id;
    } else if(myshopify_domain?.includes(".com")){
        url = "https://admin.shopify.com/store/" + (myshopify_domain?.replace(".com", "") || "")  + "/products/" + plan?.shopify_product_id;
    }
    return (
        <div className='plans_product_detail'>

            {/* product image & title */}
            <div className='plans_products_detail_col product_picture_wrap'>
                <div className='image_wrap'>

                    {
                        plan?.img_src ?
                            <img src={plan?.img_src} alt="product image" />
                            :
                            <img src={product} alt='product image' />

                    }
                </div>
            </div>

            {/* product image & title */}
            <div className='plans_products_detail_col product_title_wrap flex-space-between'>
                <div className='name_wrap'>
                    <Link target='_blank' className='redirect_link' url={url} variant="headingMd" as="h6" fontWeight='semiBold'>{plan?.product_title}</Link>
                </div>

                <div className='edit_delete_block'>
                    <ButtonGroup segmented>

                        <Button onClick={() => { editPlan(), navigate(`/plans/${plan.shopify_product_id}/edit`) }} olor="base" icon={EditIcon}>Edit Plan</Button>;

                    </ButtonGroup>
                </div>
            </div>

        </div>
    )
}
