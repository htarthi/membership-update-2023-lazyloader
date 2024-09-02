import {
    Badge,
    IndexTable,
    SkeletonBodyText,
    SkeletonDisplayText,
    SkeletonThumbnail,
    Text,
} from "@shopify/polaris";
import React from "react";

export default function MembersListSkeleton() {
    const resourceName = {
        singular: "row",
        plural: "rows",
    };

    return (
        <div>
            <IndexTable
                resourceName={resourceName}
                itemCount={8}
                headings={[
                    { title: "Member #" },
                    { title: "Customer" },
                    { title: "Status" },
                    { title: "Store Credit", alignment: 'center'},
                    { title: "Start Date" },
                    { title: "Next Billing Date" },
                    { title: "Last Payment" },
                    { title: "Actions" },
                ]}
                selectable={false}
            >
                {Array(7)
                    .fill()
                    .map((val, index) => {
                        return (
                            <IndexTable.Row key={index} position={index}>
                                <IndexTable.Cell>
                                    <div className="member_detail_wrap">
                                        <div className="member_profile_wrap">
                                            <SkeletonThumbnail size="small" />
                                        </div>
                                    </div>
                                </IndexTable.Cell>
                                <IndexTable.Cell>
                                    <Text
                                        fontWeight="regular"
                                        variant="bodyLg"
                                        as="h6"
                                    >
                                        <SkeletonBodyText lines={1} />
                                    </Text>
                                </IndexTable.Cell>
                                <IndexTable.Cell>
                                    <div
                                        className="member_name_wrap"
                                        style={{ width: "100%" }}
                                    >
                                        <SkeletonBodyText lines={1} />
                                    </div>
                                </IndexTable.Cell>
                                <IndexTable.Cell>
                                    <Text
                                        fontWeight="regular"
                                        variant="bodyLg"
                                        as="h6"
                                    >
                                        <SkeletonBodyText lines={1} />
                                    </Text>
                                </IndexTable.Cell>
                                <IndexTable.Cell>
                                    <Text
                                        fontWeight="regular"
                                        variant="bodyLg"
                                        as="h6"
                                    >
                                        <SkeletonBodyText lines={1} />
                                    </Text>
                                </IndexTable.Cell>
                                <IndexTable.Cell>
                                    <Text
                                        fontWeight="regular"
                                        variant="bodyLg"
                                        as="h6"
                                    >
                                        <SkeletonBodyText lines={1} />
                                    </Text>
                                </IndexTable.Cell>
                                <IndexTable.Cell>
                                    <div className="lastpayment_wrap">
                                        <SkeletonBodyText lines={1} />
                                    </div>
                                </IndexTable.Cell>
                                <IndexTable.Cell>
                                    <div className="lastpayment_wrap">
                                        <SkeletonBodyText lines={1} />
                                    </div>
                                </IndexTable.Cell>
                            </IndexTable.Row>
                        );
                    })}
            </IndexTable>
        </div>
    );
}
