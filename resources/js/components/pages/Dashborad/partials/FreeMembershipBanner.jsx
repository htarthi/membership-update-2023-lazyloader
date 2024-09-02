import { Banner, ProgressBar, Text } from "@shopify/polaris";
import React, { useState, useEffect } from "react";
import { useSelector, useDispatch } from "react-redux";
import { useNavigate } from "react-router-dom";
import { getData } from "../../../../data/features/dashboard/dashboardAction";

export const FreeMembershipBanner = () => {
    const navigate = useNavigate();
    const [percentage, setPercentage] = useState(0);
    const handleClick = () => {
        navigate("/settings?Billing");
    };

    const dispatch = useDispatch();

    const dashboard$ = useSelector((state) => state?.dashboard?.data);

    const total_membership = dashboard$?.total_free_memberships;
    // const total_membership = 100;

    const count_membership = dashboard$?.count_free_memberships;

    console.log("total_membership", total_membership);

    // useEffect(() => {
    //     dispatch(getData());
    //     // const percentage = (count_membership / total_membership) * 100;
    //     // setPercentage((prev)=>{
    //     //     console.log("prev",prev,percentage)
    //     // })
    //     // setPercentage(percentage);
    // }, [])
    useEffect(() => {
        // dispatch(getData());
        const percentage = (count_membership / total_membership) * 100;
        // console.log("percentage",percentage)

        setPercentage(percentage);
    }, [total_membership]);

    return <>
        <Banner
            title={<Text as="h1" variant='bodyMd' fontWeight='medium'>Enjoy our free plan!</Text>}
            tone="info"
            action={{
                content: "Choose a plan",
                onAction: () => handleClick(),
            }}
        >
            <Text variant="bodyLg" as="h5">
                You get full access to Simplee Memberships for your first{" "}
                {total_membership} memberships. Once your program surpasses
                this amount, you can decide which plan to continue with.
            </Text>
            <div className="progressBar" style={{ marginTop: "8px" }}>
                <ProgressBar size="medium" progress={percentage} />
            </div>
        </Banner>
    </>;
};
