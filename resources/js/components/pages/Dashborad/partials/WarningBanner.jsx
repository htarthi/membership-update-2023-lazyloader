import React from "react";
import { Page, Text, Banner, SkeletonBodyText } from "@shopify/polaris";
import { useSelector, useDispatch } from "react-redux";
import { updateAppEmbeded } from "../../../../data/features/dashboard/dashboardAction";

function WarningBanner() {
    const dispatch = useDispatch();
    const dashboard$ = useSelector((state) => state?.dashboard?.data);

    const themes$ = dashboard$?.themes;
    const domain = dashboard$?.shop?.domain;

    const shop_url =
        "admin.shopify.com/store/" +
        dashboard$?.shop?.myshopify_domain.replace(".myshopify.com", "");
    const handleClick = (url) => {
        window.open(url, "_blank", "noreferrer");
    };

    return <>
        {/* <SkeletonBodyText lines={4} /> */}

        <Banner
            title={<Text as="h1" variant='bodyMd' fontWeight='medium'>App Embed is not enabled on your published theme</Text>}
            action={{
                content: "Click here and save your theme",
                onAction: () =>
                    handleClick(
                        "https://" +
                            shop_url +
                            "/admin/themes/" +
                            themes$ +
                            "/editor?context=apps&activateAppId=" +
                            import.meta.env.VITE_SHOPIFY_APP_EMBEDED_ID +
                            "%2Fapp-block",
                    ),
            }}
            tone="warning"
        >
            <Text variant="bodyLg" as="h5">
                To enable the membership widget and other important
                functionality, click the button bellow then save your theme
            </Text>
        </Banner>
    </>;
}

export default WarningBanner;
