import {
    DatePicker,
    Icon,
    Modal,
    Popover,
    Select,
    Text,
    TextField,
} from "@shopify/polaris";
import React, { useCallback, useEffect, useState } from "react";
import { CalendarIcon } from "@shopify/polaris-icons";
import ChangeProducts from "../../../GlobalPartials/ChangeProducts/ChangeProducts";
import { useDispatch } from "react-redux";
import {
    contractReducer,
    lineItemsReducer,
    updatememberActivity,
} from "../../../../data/features/memberDetails/memberDetailsSlice";
import { useSelector } from "react-redux";
import {
    subscribeUpdate,
    subscriberEdit,
} from "../../../../data/features/memberDetails/membersDetailsAction";
import { useParams } from "react-router-dom";
import ReactivateMembership from "./ReactivateMembership";
import ContextualBar from "../../../GlobalPartials/ContextualBar/ContextualBar";

export default function EditMembersDetailModal({ modalOpen, setModalOpen }) {
    const dispatch = useDispatch();
    contractReducer
    const lineItems$ = useSelector(
        (state) => state.memberDetails?.data?.contract?.lineItems
    );
    const contract$ = useSelector(
        (state) => state.memberDetails?.data?.contract
    );
    const members = useSelector((state) => state.members?.data?.memberships);

    const { id } = useParams();

    const shop$ = useSelector((state) => state.memberDetails?.data?.shop);

    const isMemberActivity$ = useSelector(
        (state) => state.memberDetails?.isMemberActivity
    );

    useEffect(() => {
        if (isMemberActivity$ === true) {
            dispatch(
                subscriberEdit({
                    page: members?.current_page,
                    id: parseInt(id),
                })
            );
            dispatch(updatememberActivity({ status: "cancelled" }));
        }
    }, [isMemberActivity$]);

    const [deleteModal, setDeleteModal] = useState(false);
    const [reactivateModal, setReactivateModal] = useState(false);
    const [error, setError] = useState("");

    const [changeProduct , setChangeProduct] = useState(false);
    const [newProductID , setnewProductID] = useState('');

    // selected date state
    const [selectedDate, setSelectedDate] = useState(
        contract$?.next_processing_date
            ? new Date(contract$?.next_processing_date)
            : new Date()
    );

    // edit fields object
    let editVal = {
        product: lineItems$[0]?.title,
        image: "",
        line_items: lineItems$[0],
        recurring_code: contract$?.pricing_adjustment_value,
        discount_price: lineItems$[0]?.discount_amount,
        sub_total: contract$?.pricing_adjustment_value,
        billing_frequency: contract$?.billing_interval_count,
        billing_frequency_period: contract$?.billing_interval,
        quantity :  lineItems$[0]?.quantity
    };

    const [editFieldVal, setEditFieldVal] = useState(editVal);
    useEffect(() => {
        setEditFieldVal(editVal);
    }, [lineItems$, contract$]);

    // edit input handle change
    const editInputHandleChange = useCallback(
        (value, name) => {
            setError("");
            if (
                name == "billing_frequency" ||
                name == "billing_frequency_period"
            ) {
                if (
                    (name == "billing_frequency_period" &&
                        value == "year" &&
                        editFieldVal.billing_frequency > 5) ||
                    (name == "billing_frequency" &&
                        value > 5 &&
                        editFieldVal.billing_frequency_period == "year")
                ) {
                    setError("Billing Frequency is not greater than 5 years");
                }
            }
            if (name == "billing_frequency" && value == 0) {
                setError("Billing Frequency must be atleast 1");
            }

            if (name == "discount_price") {
                const data = {
                    discount_amount: value,
                };
                const updatedEditVal = editFieldVal
                updatedEditVal.line_items = {
                    ...updatedEditVal.line_items,
                    discount_amount: value,
                };
                setEditFieldVal(updatedEditVal);
            }

            if (name == "quantity") {
                const data = {
                    quantity: value,
                };
                const updatedEditVal = editFieldVal
                updatedEditVal.line_items = {
                    ...updatedEditVal.line_items,
                    quantity: value,
                };
                setEditFieldVal(updatedEditVal);
            }

            setEditFieldVal({
                ...editFieldVal,
                [name]: value,
            });
        },
        [editFieldVal]
    );

    // Billing frequency options
    const BillingFrequencyOptions = [
        { label: "1", value: "1" },
        { label: "2", value: "2" },
        { label: "3", value: "3" },
    ];
    // Billing frequency options
    const BillingPeriodsOptions = [{ label: "Month(s)", value: "Month(s)" }];

    // ----- membership details model ------

    // cancle modal
    const cancleHandleChange = useCallback(() => {
        setEditFieldVal(editVal);
        setModalOpen(!modalOpen);
        setError("");
        setSelectedDate(
            contract$?.next_processing_date
                ? new Date(contract$?.next_processing_date)
                : new Date()
        );
    }, [editFieldVal, modalOpen]);

    // cancle and remove access modal
    const [type, setType] = useState(false);
    const cancleRemoveAccess = useCallback(
        (type) => {
            setDeleteModal(!deleteModal);
            setType(type);
        },
        [deleteModal, type]
    );

    // confirmation model...
    const confirmation = useCallback(() => {
        const subscribeData = {
            data: {
                contract_id: parseInt(id),
                customer_answer: [],
                customer_id: contract$?.customer?.id,
                deleted: [],
                line_items: contract$?.lineItems,
                next_order_date: "",
                note: "",
                prepaid_renew: contract$?.prepaid_renew,
                reactiveDate: "",
                selling_plan_id: "",
                shopify_discount_id: "",
                type: type,
                changeProduct : changeProduct,
                newProductID  : newProductID
            },
        };
        dispatch(subscribeUpdate({ id, subscribeData }));
        dispatch(contractReducer({ status: "cancelled" }));

        setDeleteModal(false);
        setModalOpen(false);
    }, [modalOpen, contract$, deleteModal]);

    // Reactivate
    const reactivateHandleChange = useCallback(() => {
        setReactivateModal(!reactivateModal);
    }, [reactivateModal]);

    const reactivateMethod = useCallback(
        (date) => {
            // reactive API
            renewalAPI(date, "reactive");

            // store data in redux...
            const data = {
                shopify_variant_image: editFieldVal?.image,
                title: editFieldVal?.product,
                discount_amount: editFieldVal?.discount_price,
                quantity: editFieldVal?.quantity,
            };
            dispatch(lineItemsReducer(data));
            dispatch(
                contractReducer({
                    pricing_adjustment_value: editFieldVal?.sub_total,
                    reactiveDate: selectedDate?.toLocaleString("default", {
                        month: "short",
                        day: "numeric",
                        year: "numeric",
                    }),
                })
            );
            setEditFieldVal(editVal);

            setModalOpen(false);
            setReactivateModal(false);
        },
        [lineItems$, editFieldVal, selectedDate, contract$]
    );

    // ------ ResourcePicker -------
    const [resourcePickerOpen, setResourcePickerOpen] = useState(false);

    // variant selection
    const handleResourceSelection = useCallback(
        (resources) => {

            const test = setEditFieldVal({
                ...editFieldVal,
                product: resources?.selection[0]?.title,
            });
            let id = resources?.selection[0]?.id;
            let getId = parseInt(id?.replace("gid://shopify/Product/", ""));
            if(getId && editFieldVal?.line_items?.ss_contract_id) {
                setChangeProduct(true);
                setnewProductID(getId);
            }
            setResourcePickerOpen(false);
        },
        [editFieldVal]
    );

    // resource picker open/close
    const handleResourcePickerToggle = useCallback(() => {
        setResourcePickerOpen(!resourcePickerOpen);
    }, [resourcePickerOpen]);

    // ------- Renewal / Biling date ------

    // popver state
    const [visible, setVisible] = useState(false);

    const [dateobject, setDateObject] = useState("");

    // set month and year
    const [{ month, year }, setDate] = useState({
        month: selectedDate?.getMonth(),
        year: selectedDate?.getFullYear(),
    });

    // month change
    function handleMonthChange(month, year) {
        setDate({ month, year });
    }

    // select date
    const [showContextualBar, setShowContextualBar] = useState(false);
    function handleDateSelection({ end: newSelectedDate }) {
        setSelectedDate(newSelectedDate);
        setShowContextualBar(true);
        setVisible(false);
    }

    useEffect(() => {
        if (selectedDate) {
            setDate({
                month: selectedDate?.getMonth(),
                year: selectedDate?.getFullYear(),
            });
        }
    }, [selectedDate]);

    // save handle change event of context bar
    const saveContextHandleChange = useCallback(() => {
        renewalAPI(selectedDate?.toLocaleDateString("en-IN"), "all");
    }, [selectedDate]);


    // Renewal/Reactive date API calling method..
    const renewalAPI = useCallback(
        (date, type) => {
            const currentTime = new Date();

            const parts = date?.split("/");
            const day = parseInt(parts[0]);
            const month = parseInt(parts[1]) - 1;
            const year = parseInt(parts[2]);
            const hour = currentTime.getHours();
            const min = currentTime.getMinutes();
            const sec = currentTime.getSeconds();

            const dateObject = new Date(year, month, day, hour, min, sec);

            setDateObject(dateObject.toISOString())

            // api call...
            const subscribeData = {
                data: {
                    contract_id: parseInt(id),
                    customer_answer: [],
                    customer_id: contract$?.customer?.id,
                    deleted: [],
                    line_items: [],
                    next_order_date: dateObject.toISOString(),
                    note: "",
                    prepaid_renew: contract$?.prepaid_renew,
                    reactiveDate: dateObject.toISOString(),
                    selling_plan_id: "",
                    shopify_discount_id: "",
                    type: type,
                    changeProduct : changeProduct ,
                    newProductID  : newProductID
                },
            };
            dispatch(subscribeUpdate({ id, subscribeData }));
            dispatch(
                contractReducer({
                    next_processing_date: dateObject.toLocaleString("en-US", {
                        month: "short",
                        day: "numeric",
                        year: "numeric",
                    }),
                })
            );
            type === "reactive" &&
                dispatch(
                    contractReducer({
                        status: "active",
                    })
                );
        },
        [contract$]
    );

    const saveHandleBtn = useCallback(() => {


        let dateObject = null;
        if (showContextualBar) {
            const currentTime = new Date();

            const parts = selectedDate?.toLocaleDateString("en-IN")?.split("/");
            const day = parseInt(parts[0]);
            const month = parseInt(parts[1]) - 1;
            const year = parseInt(parts[2]);
            const hour = currentTime.getHours();
            const min = currentTime.getMinutes();
            const sec = currentTime.getSeconds();

            dateObject = new Date(year, month, day, hour, min, sec);
        }

        const subscribeData = {
            data: {
                contract_id: parseInt(id),
                customer_answer: [],
                customer_id: contract$?.customer?.id,
                deleted: [],
                line_items: [editFieldVal.line_items],
                note: "",
                prepaid_renew: contract$?.prepaid_renew,
                selling_plan_id: "",
                shopify_discount_id: "",
                type: "all",
                billing_interval: editFieldVal.billing_frequency_period,
                billing_interval_count: editFieldVal.billing_frequency,
                next_order_date: dateObject ? dateObject : '',
                changeProduct : changeProduct ,
                newProductID  : newProductID

            },
        };


        dispatch(subscribeUpdate({ id, subscribeData }));
        dispatch(
            contractReducer({
                billing_interval: editFieldVal.billing_frequency_period,
                billing_interval_count: editFieldVal.billing_frequency,
            })
        );
        // dispatch(subscriberEdit({ page: members?.current_page, id: parseInt(id) }))

    }, [editFieldVal, contract$, selectedDate]);

    const interval = [
        { label: "Day(s)", value: "day" },
        { label: "Week(s)", value: "week" },
        { label: "Month(s)", value: "month" },
        { label: "Year(s)", value: "year" },
    ];

    return <>
        <Modal
            open={modalOpen}
            onClose={cancleHandleChange}
            title={<Text>Membership Details </Text>}
            primaryAction={
                contract$?.status === "cancelled"
                    ? [
                        {
                            content: `Reactivate`,
                            onAction: reactivateHandleChange,

                        },
                        {
                            content: `Save`,
                            onAction: saveHandleBtn,
                            disabled: error ? true : false,
                        },
                    ]
                    : {
                        content: `Save`,
                        onAction: saveHandleBtn,
                        // tone : 'success',
                        disabled: error ? true : false,
                    }
            }
            secondaryActions={
                contract$?.status === "active" && [
                    {
                        content: `Cancel Next Renewal`,
                        onAction: () => cancleRemoveAccess("cancelled"),

                        destructive: true,
                    },
                    {
                        content: `Cancel Immediately`,
                        onAction: () =>
                            cancleRemoveAccess("cancelled-removeaccess"),
                        destructive: true,
                    },
                ]
            }
        >
            <Modal.Section>
                <div
                    className="membership_detail_edit_wrap"
                    onClick={() => setVisible(false)}
                >
                    {/* text field - change product (Resource Picker) */}
                    {
                        contract$?.shopify_contract_id ?
                                    <ChangeProducts
                                        resource="Product"
                                        value={editFieldVal?.product}
                                        data={editFieldVal?.product}
                                        label={"Product "}
                                        is_alreadyselected={false}
                                        handleResourcePickerToggle={
                                            handleResourcePickerToggle
                                        }
                                        resourcePickerOpen={resourcePickerOpen}
                                        onSelection={handleResourceSelection}
                                        onCancel={() => setResourcePickerOpen(false)}
                                        error={""}
                                        selectMultiple={false}
                                        is_show_change_product={true}
                                        showVariants={true}
                                    />

                            :
                            <div className="input_fields_wrap">
                                <TextField
                                    label="Product"
                                    value={editFieldVal.product}
                                    readOnly
                                />
                            </div>
                    }



                    {/* Recurring code */}
                    {
                        contract$?.shopify_contract_id ?
                            <div className="input_two_fields_wrap">
                                <div className="input_fields_wrap">
                                    <TextField
                                        label="Recurring amount"
                                        type="number"
                                        value={editFieldVal.discount_price}
                                        onChange={(value) =>
                                            editInputHandleChange(
                                                value,
                                                "discount_price"
                                            )
                                        }
                                        autoComplete="off"
                                        prefix={contract$.currency_code}
                                    />
                                </div>
                                <div className="input_fields_wrap">
                                    <TextField
                                        label="Quantity"
                                        type="number"
                                        value={editFieldVal.quantity}
                                        onChange={(value) =>
                                            editInputHandleChange(
                                                value,
                                                "quantity"
                                            )
                                        }
                                        autoComplete="off"
                                    />
                                </div>
                            </div> : <div className="input_fields_wrap">
                                    <TextField
                                        label="Recurring amount"
                                        type="number"
                                        value={editFieldVal.discount_price}
                                        onChange={(value) =>
                                            editInputHandleChange(
                                                value,
                                                "discount_price"
                                            )
                                        }
                                        autoComplete="off"
                                        prefix={contract$.currency_code}
                                    />
                                </div>

                    }

                    {/* Discount price & Sub-total */}
                    {/* <div className="input_two_fields_wrap">
                        <div className="input_fields_wrap">
                            <TextField
                                label="Discount price"
                                type="number"
                                value={`${parseInt(editFieldVal.discount_price)}.00`}
                                onChange={(value) =>
                                    editInputHandleChange(
                                        value,
                                        "discount_price"
                                    )
                                }
                                autoComplete="off"
                                prefix="USD $"
                                readOnly
                            />
                        </div>
                        <div className="input_fields_wrap">
                            <TextField
                                label="Sub-total"
                                type="number"
                                value={editFieldVal.sub_total}
                                onChange={(value) =>
                                    editInputHandleChange(value, "sub_total")
                                }
                                autoComplete="off"
                                prefix="USD $"
                                readOnly
                            />
                        </div>
                    </div> */}

                    {/* Renewal / Biling date */}
                    <div className="input_fields_wrap">
                        <Popover
                            active={visible}
                            preferredAlignment="left"
                            fullWidth
                            preferInputActivator={false}
                            preferredPosition="below"
                            preventCloseOnChildOverlayClick
                            onClose={(e) => {
                                e.stopPropagation();
                                setVisible(false);
                            }}
                            activator={
                                <div className="input_fields_wrap">
                                    <TextField
                                        role="combobox"
                                        label={"Renewal / Biling date"}
                                        suffix={
                                            <Icon source={CalendarIcon} />
                                        }
                                        value={selectedDate?.toLocaleDateString(
                                            "en-IN"
                                        )}
                                        onFocus={(e) => {
                                            e.stopPropagation();
                                            setVisible(true);
                                        }}
                                        onChange={""}
                                        autoComplete="off"
                                    />
                                </div>
                            }
                        >
                            <div
                                className="date_picker_popover"
                                onClick={(e) => e.stopPropagation()}
                            >
                                <DatePicker
                                    month={month}
                                    year={year}
                                    selected={selectedDate}
                                    onMonthChange={handleMonthChange}
                                    onChange={handleDateSelection}
                                    disableDatesBefore={new Date()}
                                />
                            </div>
                        </Popover>
                    </div>

                    {/* Billing frequency & Billing frequency period */}
                    <div className="input_two_fields_wrap">
                        <div className="input_fields_wrap">
                            <TextField
                                label="Billing frequency"
                                type="number"
                                min={"1"}
                                value={editFieldVal.billing_frequency}
                                onChange={(value) =>
                                    editInputHandleChange(
                                        value,
                                        "billing_frequency"
                                    )
                                }
                                error={error ? error : ""}
                            />
                        </div>
                        <div className="input_fields_wrap">
                            <Select
                                label="Billing frequency period"
                                options={interval}
                                value={
                                    editFieldVal.billing_frequency_period
                                }
                                onChange={(value) =>
                                    editInputHandleChange(
                                        value,
                                        "billing_frequency_period"
                                    )
                                }
                            />
                        </div>
                    </div>
                </div>
            </Modal.Section>
        </Modal>

        {/* confirmation modal */}
        <Modal
            size="small"
            open={deleteModal}
            onClose={() => setDeleteModal(false)}
            title={<Text>Cancel Membership!</Text>}
            primaryAction={{
                content: "Yes",
                // tone : 'success',
                onAction: confirmation,
            }}
            secondaryActions={[
                {
                    content: "No",
                    onAction: () => setDeleteModal(false),
                },
            ]}
        >
            <Modal.Section>
                <Text variant="headingMd" as="h6" fontWeight='regular'>
                    Are you sure you want to cancel this membership?
                </Text>
            </Modal.Section>
        </Modal>

        {/* confirmation modal */}
        <ReactivateMembership
            active={reactivateModal}
            setReactivateModal={setReactivateModal}
            reactivateMethod={reactivateMethod}
        />

        {/* Contextual Save Bar */}
        {/* {showContextualBar && <ContextualBar setShowContextualBar={setShowContextualBar} saveContextHandleChange={saveContextHandleChange} />} */}
    </>;
}
