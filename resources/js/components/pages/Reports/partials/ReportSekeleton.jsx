import {
    Layout,
    LegacyCard,
    SkeletonBodyText,
    TextContainer,
    SkeletonDisplayText,
} from "@shopify/polaris";
import React from "react";

function ReportSekeleton() {
    return (
        <Layout>
            <Layout.Section>
                <LegacyCard sectioned>
                    <SkeletonBodyText lines={1} />
                </LegacyCard>
                <LegacyCard sectioned>
                    <SkeletonBodyText lines={1} />
                </LegacyCard>
            </Layout.Section>
            <Layout.Section secondary>
                <LegacyCard>
                    <LegacyCard.Section>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText lines={1} />
                        </TextContainer>
                    </LegacyCard.Section>
                    <LegacyCard.Section>
                        <SkeletonBodyText lines={1} />
                    </LegacyCard.Section>
                    <LegacyCard.Section>
                        <SkeletonBodyText lines={1} />
                    </LegacyCard.Section>
                    <LegacyCard.Section>
                        <SkeletonBodyText lines={1} />
                    </LegacyCard.Section>
                </LegacyCard>
                <LegacyCard subdued>
                    <LegacyCard.Section>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText lines={1} />
                        </TextContainer>
                    </LegacyCard.Section>
                    <LegacyCard.Section>
                        <SkeletonBodyText lines={1} />
                    </LegacyCard.Section>
                    <LegacyCard.Section>
                        <SkeletonBodyText lines={1} />
                    </LegacyCard.Section>
                    <LegacyCard.Section>
                        <SkeletonBodyText lines={1} />
                    </LegacyCard.Section>
                </LegacyCard>
            </Layout.Section>
        </Layout>
    );
}

export default ReportSekeleton;
