import React from "react";
import { Layout, LegacyCard, SkeletonBodyText, TextContainer, SkeletonDisplayText, } from "@shopify/polaris";

export default function PlansSkeleton() {
    return (
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
    );
}
