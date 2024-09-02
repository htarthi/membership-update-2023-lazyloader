import { getType } from '@reduxjs/toolkit';
import { useSelector } from "react-redux";
import { Card, Icon, IndexTable, LegacyCard, Text } from '@shopify/polaris'
import React, { useCallback } from 'react'

export const PlansLength = ({ lengths }) => {
    const shop = useSelector((state) => state.plans?.data?.shop?.currency);
    const resourceName = {
        singular: "row",
        plural: "rows",
    };

    const rowMarkup = lengths?.length > 0 && lengths?.map(
        ({ id, shopify_plan_id, delivery_interval_count, delivery_interval, pricing_adjustment_value, has_many_contracts }, index) => (
            // <Draggable key={index} draggableId={id.toString()} index={index}>
            //     {(provided) => (
            <IndexTable.Row id={id} key={id} position={index} className="Polaris-IndexTable__TableRow membership_table_tr">
                <IndexTable.Cell>
                    {/* <div
                                className="drag_handle_minor_wrap"
                                ref={provided.innerRef}
                                {...provided.draggableProps}
                                {...provided.dragHandleProps}
                            > */}
                    {/* <Icon
                        source={DragHandleIcon}
                        tone="base"
                    /> */}
                    {/* </div> */}

                </IndexTable.Cell>
                <IndexTable.Cell >
                    <Text padding="0" variant='headingMd' as="span" fontWeight='semiBold'>{delivery_interval_count + " " + delivery_interval.charAt(0).toUpperCase() + delivery_interval.slice(1)}</Text><br />
                    <Text variant="bodyLg" as="span" tone="subdued" fontWeight='regular'>ID: {shopify_plan_id}</Text>
                </IndexTable.Cell>
                <IndexTable.Cell>
                    <Text padding="0" variant="headingMd" as="h5" fontWeight='medium'>{shop + " " + pricing_adjustment_value}</Text>
                </IndexTable.Cell>
                <IndexTable.Cell>
                    <Text padding="0" variant="headingMd" as="h5" fontWeight='medium'>{has_many_contracts.length}</Text>
                </IndexTable.Cell>
                <IndexTable.Cell>
                    <Text padding="0" variant="headingMd" as="h5" fontWeight='medium'>CAD $50</Text>
                </IndexTable.Cell>
            </IndexTable.Row>
            //         )}
            //     </Draggable>
        )
    );
    return (
        <>
            <div className='plans_lengths_list_wrap' >
                <LegacyCard >
                    {/* <DragDropContext onDragEnd={onDragEnd}>
                        <Droppable droppableId="table">
                            {(provided) => (
                                <div
                                    {...provided?.droppableProps}
                                    ref={provided?.innerRef}
                                > */}
                    <IndexTable
                        resourceName={resourceName}
                        itemCount={lengths?.length > 0 ? lengths?.length : 0}
                        selectable={false}
                        headings={[
                            { title: "" },
                            { title: "Membership Length" },
                            { title: "Price" },
                            { title: "Members" },
                            { title: "Store Credit" },

                        ]}
                    >
                        {rowMarkup}
                    </IndexTable>
                    {/* </div>
                            )}
                        </Droppable>
                    </DragDropContext> */}
                </LegacyCard>
            </div>
        </>
    )
}
