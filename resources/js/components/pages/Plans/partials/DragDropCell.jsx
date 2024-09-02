import { Icon, IndexTable, Text } from "@shopify/polaris";
import React, { useState } from "react";
import { DragHandleIcon } from "@shopify/polaris-icons";
import { DragDropContext, Draggable, Droppable } from "react-beautiful-dnd";
import { useSelector } from "react-redux";

export default function DragDropCell({hashPlans}) {

    // Redux Data
    const shop$ = useSelector((state) => state.plans?.data?.shop);

    const [rows, setRows] = useState(hashPlans);

    const onDragEnd = (result) => {
        if (!result.destination) return;
        const updatedRows = [...rows];
        const [movedRow] = updatedRows.splice(result.source.index, 1);
        updatedRows.splice(result.destination.index, 0, movedRow);
        setRows(updatedRows);
    };
    // droppableId="droppable" type="ROWS"

    return (
        <div className="plans_list_table ms-margin-top">
        {
            shop$?.length > 0 &&
            <DragDropContext onDragEnd={onDragEnd}>
                <Droppable droppableId="table">
                    {(provided) => (
                        <div
                            {...provided.droppableProps}
                            ref={provided.innerRef}
                        >
                            <IndexTable
                                resourceName={{
                                    singular: "hasPlan",
                                    plural: "hasPlans",
                                }}
                                itemCount={rows?.length}
                                headings={[
                                    { title: "" },
                                    { title: "Membership Length" },
                                    { title: "Price" },
                                    { title: "Members" },
                                    { title: "Store Credit" },
                                ]}
                                selectable={false}
                            >
                                {
                                    rows?.map((has_plans, index) => (
                                        <Draggable
                                            key={String(has_plans?.id)}
                                            draggableId={String(has_plans?.id)}
                                            index={index}
                                        >
                                            {(provided) => (
                                                <tr
                                                    id={String(has_plans?.id)}
                                                    key={String(has_plans?.id)}
                                                    position={index}
                                                >
                                                    <IndexTable.Cell>
                                                        <div className="drag_handle_minor_wrap"
                                                        tabindex="-1"
                                                        ref={provided.innerRef}
                                                        {...provided.draggableProps}
                                                        {...provided.dragHandleProps}>
                                                            <Icon source={ DragHandleIcon } color="base" />
                                                        </div>
                                                    </IndexTable.Cell>
                                                    <IndexTable.Cell>
                                                        <div className="membership_length_block ms-margin-bottom-five">
                                                            <Text variant="bodyMd" fontWeight="medium" as="span" > {has_plans?.billing_interval_count || '-'} {has_plans?.billing_interval || ""} </Text>
                                                        </div>
                                                        <Text variant="bodyMd" fontWeight="medium" as="span" tone="subdued" > ID: {has_plans?.shopify_plan_id || "-"} </Text>
                                                    </IndexTable.Cell>
                                                    <IndexTable.Cell>
                                                        <Text variant="bodyMd" fontWeight="medium" as="span" > {shop$?.currency || "-"} {has_plans?.pricing_adjustment_value || ''} </Text>
                                                    </IndexTable.Cell>
                                                    <IndexTable.Cell>
                                                        <Text variant="bodyMd" fontWeight="medium" as="span" > {has_plans?.has_many_contracts?.length || "-"} </Text>
                                                    </IndexTable.Cell>
                                                    <IndexTable.Cell>
                                                        <Text variant="bodyMd" fontWeight="medium" as="span" > {'' || "-"} </Text>
                                                    </IndexTable.Cell>
                                                </tr>
                                            )}
                                        </Draggable>
                                ))}
                            </IndexTable>
                            {provided.placeholder}
                        </div>
                    )}
                </Droppable>
            </DragDropContext>
        }
        </div>
    );
}
