import React from "react";
import { Text, Banner, SkeletonBodyText } from "@shopify/polaris";

const handleClick = (url) => {
    window.open(url, "_blank", "noreferrer");
};

const ErrorBanner = () => {
    return <>
        {/* <SkeletonBodyText lines= {4} /> */}

        <Banner
            title={<Text as="h1" variant='bodyMd' fontWeight='medium'>You’re not ready for Memberships yet</Text>}
            tone="critical"
            action={{
                content: "Review Requirements",
                onAction: () =>
                    handleClick(
                        "https://support.simplee.best/en/articles/4735846-can-i-use-simplee-memberships",
                    ),
            }}
        >
            <Text variant="bodyLg" as="h5">
                It looks like your store can’t use this app yet. This
                usually means you’re not using a gateway which is approved
                by shopify for recurring payments.
            </Text>
        </Banner>
    </>;
};

export default ErrorBanner;
