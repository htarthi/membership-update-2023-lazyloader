import { Button, Icon, IndexTable, Select, Text, TextField, Tooltip, Checkbox } from "@shopify/polaris";
import React, { useCallback, useState, useEffect } from "react";
import { DeleteIcon, DragHandleIcon, QuestionCircleIcon, } from "@shopify/polaris-icons";
import { useDispatch, useSelector } from "react-redux";
import { AddAdditionalQue, AdditionalQue, DeleteFields, UpdateFields, deletedUpdate, } from "../../../../../data/features/plansDetails/plansDetailsSlice";
import { DragDropContext, Draggable, Droppable } from "react-beautiful-dnd";
import DeleteModal from "../../../../GlobalPartials/DeleteModal/DeleteModal";
import { updateTiers } from "../../../../../data/features/plans/plansSlice";

export default function AdditionalQuestions() {
    const dispatch = useDispatch();
    const selectedIndex$ = useSelector((state) => state.plansDetail?.selectedIndex);
    const discountShipping$ = useSelector((state) => state?.plansDetail?.data.plan_groups[selectedIndex$]);
    const AdditionalQuestions$ = useSelector(
        (state) => state.plansDetail?.data
    );
    const updated_tiers$ = useSelector((state) => state?.plans?.updated_tiers);
    const errors$ = useSelector((state) => state?.plansDetail?.errors);
    const errorAdditionalQue = `data.tiers.${selectedIndex$}.formFields`

    // Field Types
    const fieldTypes = [
        { label: "Text Field", value: "Text Field" },
        { label: "Text Area", value: "Text Area" },
        { label: "Checkbox", value: "Checkbox" },
        { label: "Dropdown List", value: "Dropdown List" },
        { label: "Radio Group", value: "Radio Group" },
        { label: "File Upload", value: "File Upload" }
    ];
    // discounts fields - handleChange event
    const handleFieldChange = useCallback(
        (value, name, id) => {
            const updatedFields = { [name]: value };
            dispatch(UpdateFields({ id, updatedFields }));
            dispatch(updateTiers(discountShipping$?.tier_id))
        },
        [AdditionalQuestions$, updated_tiers$]
    );
    // Add Additional Questions - button click event
    const [counter, setCounter] = useState(Math.floor(Math.random() * 120));
    const addAdditionalQue = useCallback(() => {
        const newCounter = Math.floor(Math.random() * 120);
        setCounter(newCounter);
        const data = {
            id: "10000000-1000-4000-8000-100000000000".replace(/[018]/g, c =>
                (+c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> +c / 4).toString(16)
            ),
            field_label: "",
            field_type: "Text Field",
            field_options: "",
            field_required: 0,
            field_displayed: 0,
            isNew: true
        };
        dispatch(AddAdditionalQue(data));
        dispatch(updateTiers(discountShipping$?.tier_id))
    }, [AdditionalQuestions$, counter, updated_tiers$]);
    // drag&drop table cell...
    const onDragEnd = useCallback(
        (result) => {
            if (!result.destination) return;
            const updatedRows = [...AdditionalQuestions$?.formFields];
            const [movedRow] = updatedRows?.splice(result.source.index, 1);
            updatedRows.splice(result?.destination?.index, 0, movedRow);
            dispatch(AdditionalQue(updatedRows));
        },
        [AdditionalQuestions$]
    );
    // delete rules...
    // delete rules
    const deleteAdditionalQue = useCallback((deleteId) => {
        const findFormFieldsId = AdditionalQuestions$?.formFields?.find(item => item.id === deleteId);
        if (!findFormFieldsId?.isNew) {
            dispatch(deletedUpdate({ key: 'formFields', id: deleteId }))
        }
        dispatch(DeleteFields(deleteId));
        dispatch(updateTiers(discountShipping$?.tier_id))
    }, [AdditionalQuestions$,])

    // table cells
    const resourceName = {
        singular: "row",
        plural: "rows",
    };
    const rowMarkup = AdditionalQuestions$?.formFields?.map(({ id, field_label, field_type, field_options, field_required, field_displayed, }, index) => (
        <Draggable key={id} draggableId={id.toString()} index={index}>
            {(provided, snapshot) => (
                <tr id={id} key={index} position={index}>
                    <IndexTable.Cell>
                        <div
                            className="drag_handle_minor_wrap"
                            ref={provided.innerRef}
                            {...provided.draggableProps}
                            {...provided.dragHandleProps}
                        >
                            <Icon
                                source={DragHandleIcon}
                                color="base"
                            />
                        </div>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <div className="rule_name_select_field">
                            <TextField
                                type="text"
                                value={field_label}
                                onChange={(value) =>
                                    handleFieldChange(
                                        value,
                                        "field_label",
                                        id
                                    )
                                }
                                autoComplete="off"
                                error={!field_label ? errors$?.length > 0 ? errors$[0][`${errorAdditionalQue}.${index}.field_label`] : '' : ''}
                            />
                        </div>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <div className="rule_name_select_field">
                            <Select
                                options={fieldTypes}
                                value={field_type}
                                onChange={(value) =>
                                    handleFieldChange(
                                        value,
                                        "field_type",
                                        id
                                    )
                                }
                                error={!field_label ? errors$?.length > 0 ? errors$[0][`${errorAdditionalQue}.${index}.field_type`] : '' : ''}
                            />
                        </div>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <div className="rule_name_select_field">
                            {field_type === "Dropdown List" ||
                                field_type === "Radio Group" ? (
                                <TextField
                                    type="text"
                                    value={field_options}
                                    onChange={(value) =>
                                        handleFieldChange(
                                            value,
                                            "field_options",
                                            id
                                        )
                                    }
                                    autoComplete="off"
                                    placeholder={`e.g. ${field_type} 1, ${field_type} 1...`}
                                    error={!field_options ? errors$?.length > 0 ? errors$[0][`${errorAdditionalQue}.${index}.field_options`] : '' : ''}
                                />
                            ) : (
                                ""
                            )}
                        </div>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Checkbox
                            label="Required"
                            checked={field_required}
                            onChange={(field_required) =>
                                handleFieldChange(
                                    field_required,
                                    "field_required",
                                    id
                                )
                            }

                        />
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Button onClick={() => deleteAdditionalQue(id)}>
                            <Icon source={DeleteIcon} color="base" size="large"/>
                        </Button>
                    </IndexTable.Cell>
                </tr>
            )}
        </Draggable>
    )
    );

    return <>
        <div className="additional_questions_accordion">
            <Text variant="bodyLg" as="h6" fontWeight="medium">
                All memberships will already capture the customer’s name, email
                address, and payment information. Any fields you add below will
                be added to the product page and included in your member data.
            </Text>

            {/* additional questions table */}
            {
                AdditionalQuestions$?.formFields?.length > 0 &&
                <div className="add_restricted_table_block additional_questions_table ms-margin-top">
                    <DragDropContext onDragEnd={onDragEnd}>
                        <Droppable droppableId="table">
                            {(provided) => (
                                <div
                                    {...provided?.droppableProps}
                                    ref={provided?.innerRef}
                                >
                                    <IndexTable
                                        resourceName={resourceName}
                                        itemCount={
                                            AdditionalQuestions$?.formFields?.length
                                        }
                                        headings={[
                                            { id: 0, title: "" },
                                            { id: 1, title: "Label" },
                                            {
                                                id: 2,
                                                title: (
                                                    <span className="pagename_th">
                                                        Field Type
                                                        <Tooltip content="The type of field which will be displayed on the product page.">
                                                            <Icon
                                                                source={
                                                                    QuestionCircleIcon
                                                                }
                                                                color="base"
                                                            />
                                                        </Tooltip>
                                                    </span>
                                                ),
                                            },
                                            {
                                                id: 3,
                                                title: (
                                                    <span className="pagename_th">
                                                        Options
                                                        <Tooltip content="Additional information required for this question.">
                                                            <Icon
                                                                source={
                                                                    QuestionCircleIcon
                                                                }
                                                                color="base"
                                                            />
                                                        </Tooltip>
                                                    </span>
                                                ),
                                            },
                                            {
                                                id: 4,
                                                title: (
                                                    "Required?"
                                                )
                                            },
                                            { id: 5, title: "Actions" },
                                        ]}
                                        selectable={false}
                                    >
                                        {rowMarkup}
                                    </IndexTable>
                                </div>
                            )}
                        </Droppable>
                    </DragDropContext>
                </div>
            }
            {/* Add Additional Question - Button */}
            <div className="add_additional_que_btn ms-margin-top">
                <Button  onClick={addAdditionalQue} variant='primary' >
                    Add Additional Question
                </Button>
            </div>
            {/* delete modal */}
            {/* <DeleteModal active={deleteModal} setDeleteModal={setDeleteModal} deleteMethod={deleteAdditionalQue} /> */}
        </div>
    </>;
}
