import React from "react";
import { Text, Banner, LegacyCard } from "@shopify/polaris";
import { AppProvider } from "@shopify/polaris";

function MaintainceBanner() {
    return (
        <AppProvider i18n={[]} >
            <div style={{ marginTop: 235, marginLeft: 100, marginRight: 100 }}>
                <LegacyCard title="Simplee Memberships" sectioned>
                    <Banner
                        title="SIMPLEE MEMBERSHIPS IS UNDERGOING SCHEDULED MAINTENANCE"
                        tone="info"
                    >
                        <Text as="h2" variant="bodyMd">
                            {" "}
                            New memberships and renewal orders will continue to
                            be processed during this window. Please check back a
                            bit later.
                        </Text>
                    </Banner>
                </LegacyCard>
            </div>
        </AppProvider>
    );
}
export default MaintainceBanner;
