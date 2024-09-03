import { Layout, LegacyCard, SkeletonBodyText, SkeletonDisplayText, SkeletonPage, TextContainer } from '@shopify/polaris'
import React from 'react'

const FreeMembershipBannerSkeleton = () => {
    return (
        <div>
                <Layout>
                    {/* */}
                    <Layout.Section >
                        <LegacyCard>
                            <LegacyCard.Section>
                                <TextContainer>
                                    <SkeletonBodyText lines={3} />
                                    {/* <SkeletonDisplayText size="small" /> */}
                                </TextContainer>
                            </LegacyCard.Section>

                        </LegacyCard>

                    </Layout.Section>
                </Layout>
        </div>
    )
}

export default FreeMembershipBannerSkeleton
