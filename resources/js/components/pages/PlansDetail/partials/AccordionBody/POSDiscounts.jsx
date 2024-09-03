import { Button, Icon, IndexTable, Text, TextField, Tooltip, Select} from "@shopify/polaris";
import React, { useCallback, useState } from "react";
import { DeleteIcon, DragHandleIcon, QuestionCircleIcon, } from "@shopify/polaris-icons";
import { useDispatch, useSelector } from "react-redux";
import { AddDiscounts, DeleteDiscounts, Discounts, UpdateDiscounts, deletedUpdate, } from "../../../../../data/features/plansDetails/plansDetailsSlice";
import { DragDropContext, Draggable, Droppable } from "react-beautiful-dnd";
import DeleteModal from "../../../../GlobalPartials/DeleteModal/DeleteModal";
import { updateTiers } from "../../../../../data/features/plans/plansSlice";

export default function POSDiscounts() {
    const dispatch = useDispatch();

    const [selected, setSelected] = useState('today');


    const options = [
        { label: '%', value: '%' },
        { label: 'CA$', value: 'CA$' },
    ];

    const selectedIndex$ = useSelector((state) => state.plansDetail?.selectedIndex);
    const POSDiscounts$ = useSelector(
        (state) => state.plansDetail?.data.plan_groups[selectedIndex$]
    );
    const updated_tiers$ = useSelector((state) => state?.plans?.updated_tiers);
    const errors$ = useSelector((state) => state?.plansDetail?.errors);

    const errorPos = `data.tiers.${selectedIndex$}.discounts`

    // discounts fields - handleChange event
    const handleDiscountsChange = useCallback(
        (value, name, id) => {
            const updatedFields = { [name]: value };
            dispatch(UpdateDiscounts({ id, updatedFields }));
            dispatch(updateTiers(POSDiscounts$?.tier_id))
        },
        [POSDiscounts$, updated_tiers$]
    );

    // add discounts - button click event
    const [counter, setCounter] = useState(Math.floor(Math.random() * 120));
    const addDiscounts = useCallback(() => {
        const newCounter = Math.floor(Math.random() * 120);
        setCounter(newCounter);
        const data = {
            id: newCounter,
            discount_name: "",
            discount_amount: "",
            discount_amount_type: "%",
            isNew: true
        };
        dispatch(AddDiscounts(data));
        dispatch(updateTiers(POSDiscounts$?.tier_id))
    }, [POSDiscounts$, counter, updated_tiers$]);

    // drag&drop table cell...
    const onDragEnd = useCallback(
        (result) => {
            if (!result.destination) return;
            const updatedRows = [...POSDiscounts$?.discounts];
            const [movedRow] = updatedRows.splice(result.source.index, 1);
            updatedRows.splice(result.destination.index, 0, movedRow);
            dispatch(Discounts(updatedRows));
        },
        [POSDiscounts$]
    );


    // delete discounts...
    const [deleteModal, setDeleteModal] = useState(false);
    const [deleteId, setDeleteId] = useState();

    const deleteDiscountsModel = useCallback((id) => {
        setDeleteModal(!deleteModal);
        setDeleteId(id);
    }, [POSDiscounts$, deleteModal])

    // delete discounts...
    const deleteDiscounts = useCallback(() => {
        const findDiscountId = POSDiscounts$?.discounts?.find(item => item.id === deleteId);
        if (!findDiscountId?.isNew) {
            dispatch(deletedUpdate({ key: 'discounts', id: deleteId }))
        }
        dispatch(DeleteDiscounts(deleteId));
        setDeleteModal(!deleteModal);
        dispatch(updateTiers(POSDiscounts$?.tier_id));
    }, [POSDiscounts$, deleteId])

    // table cells
    const resourceName = {
        singular: "row",
        plural: "rows",
    };
    const rowMarkup = POSDiscounts$?.discounts?.map(
        (
            { id, discount_name, discount_amount, discount_amount_type },
            index
        ) => (
            <Draggable key={index} draggableId={id.toString()} index={index}>
                {(provided) => (
                    <tr id={id} key={index} position={index}>
                        <IndexTable.Cell>
                            <div
                                className="drag_handle_minor_wrap"
                                ref={provided.innerRef}
                                {...provided.draggableProps}
                                {...provided.dragHandleProps}
                            >
                                <Icon source={DragHandleIcon} color="base" />
                            </div>
                        </IndexTable.Cell>
                        <IndexTable.Cell>
                            <div className="rule_name_select_field">
                                <TextField
                                    type="text"
                                    value={discount_name}
                                    onChange={(value) =>
                                        handleDiscountsChange(
                                            value,
                                            "discount_name",
                                            id
                                        )
                                    }
                                    autoComplete="off"
                                    error={!discount_name ? errors$?.length > 0 ? errors$[0][`${errorPos}.${index}.discount_name`] : '' : ''}
                                />
                            </div>
                        </IndexTable.Cell>
                        <IndexTable.Cell>
                            <div className="rule_name_select_field discount_amount_field">
                                <div className="discount_amount_input">
                                    <TextField
                                        type="number"
                                        value={discount_amount}
                                        onChange={(value) =>
                                            handleDiscountsChange(
                                                value < 0 ? '0' : value,
                                                "discount_amount",
                                                id
                                            )
                                        }
                                        autoComplete="off"
                                        error={!discount_amount ? errors$?.length > 0 ? errors$[0][`${errorPos}.${index}.discount_amount`] : '' : ''}
                                    />
                                </div>
                                <div className="discount_percentage_input">
                                    <Select

                                        options={options}
                                        value={discount_amount_type ? discount_amount_type : "%"}
                                        onChange={(value) =>
                                            handleDiscountsChange(
                                                value,
                                                "discount_amount_type",
                                                id
                                            )
                                        }

                                    />
                                    {/* <TextField
                                        type="number"
                                        value={discount_amount_type}
                                        onChange={(value) =>
                                            handleDiscountsChange(
                                                value < 0 ? '0' : value,
                                                "discount_amount_type",
                                                id
                                            )
                                        }
                                        autoComplete="off"
                                        prefix="%"
                                    /> */}
                                </div>
                            </div>
                        </IndexTable.Cell>
                        <IndexTable.Cell>
                            <Button onClick={() => deleteDiscountsModel(id)}>
                                <Icon source={DeleteIcon} color="base" size="large"/>
                            </Button>
                        </IndexTable.Cell>
                    </tr>
                )}
            </Draggable>
        )
    );

    return (
        <div className="pos_discounts_accordion">
            <Text variant="bodyLg" as="h6" fontWeight="medium">
                If you would like your members to receive a discount when
                purchasing using Shopify POS, create one or more discounts
                below. These can then be added to your POS to offer discounts on
                in-store purchases.
            </Text>

            {/* Add Restricted Content Table */}
            {
                POSDiscounts$?.discounts?.length > 0 &&
                <div className="add_restricted_table_block pos_discount_table ms-margin-top">
                    <DragDropContext onDragEnd={onDragEnd}>
                        <Droppable droppableId="table">
                            {(provided) => (
                                <div
                                    {...provided?.droppableProps}
                                    ref={provided?.innerRef}
                                >
                                    <IndexTable
                                        resourceName={resourceName}
                                        itemCount={POSDiscounts$?.discounts?.length}
                                        headings={[
                                            { id: 0, title: "" },
                                            { id: 1, title: "Discount Name" },
                                            {
                                                id: 2,
                                                title: (
                                                    <span className="pagename_th">
                                                        Discount Amount
                                                        <Tooltip content="This order has shipping labels.">
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
                                            { id: 3, title: "Actions" },
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

            {/* Add Discount - Button */}
            <div className="add_discount_button ms-margin-top">
                <Button  onClick={addDiscounts} variant='primary' >
                    Add Discount
                </Button>
            </div>


            {/* delete modal */}
            <DeleteModal active={deleteModal} setDeleteModal={setDeleteModal} deleteMethod={deleteDiscounts} />
        </div>
    );
}
