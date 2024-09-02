import React from "react";
import {
    SkeletonPage,
    Layout,
    LegacyCard,
    SkeletonBodyText,
    TextContainer,
    SkeletonDisplayText,
} from "@shopify/polaris";

export default function PlansSkeleton() {
    return (
        <SkeletonPage primaryAction>
            <Layout>
                <Layout.Section>
                    <LegacyCard sectioned>
                        <SkeletonBodyText />
                    </LegacyCard>
                    <LegacyCard sectioned>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText />
                        </TextContainer>
                    </LegacyCard>
                    <LegacyCard sectioned>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText />
                        </TextContainer>
                    </LegacyCard>
                    <LegacyCard sectioned>
                        <TextContainer>
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText />
                        </TextContainer>
                    </LegacyCard>
                </Layout.Section>
            </Layout>
        </SkeletonPage>
    );
}
