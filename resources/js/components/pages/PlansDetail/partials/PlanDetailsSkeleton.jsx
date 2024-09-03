import React from "react";
import {
    SkeletonPage,
    Layout,
    LegacyCard,
    SkeletonBodyText,
    TextContainer,
    SkeletonDisplayText,
} from "@shopify/polaris";

export default function PlanDetailsSkeleton() {
    return (
        <SkeletonPage primaryAction>
            <LegacyCard sectioned>
                <Layout>
                    <Layout.Section>
                        <LegacyCard sectioned>
                            <SkeletonBodyText lines={2} />
                        </LegacyCard>
                        <LegacyCard sectioned>
                            <TextContainer>
                                <SkeletonDisplayText size="small" />
                                <SkeletonBodyText lines={2} />
                            </TextContainer>
                        </LegacyCard>
                        <LegacyCard sectioned>
                            <TextContainer>
                                <SkeletonDisplayText size="small" />
                                <SkeletonBodyText lines={2} />
                            </TextContainer>
                        </LegacyCard>
                        <LegacyCard sectioned>
                            <TextContainer>
                                <SkeletonDisplayText size="small" />
                                <SkeletonBodyText lines={2} />
                            </TextContainer>
                        </LegacyCard>
                    </Layout.Section>
                </Layout>
            </LegacyCard>
        </SkeletonPage>
    );
}
