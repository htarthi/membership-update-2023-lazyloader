import { Button, Icon, IndexTable, Link, Select, Text, Tooltip, TextField} from "@shopify/polaris";
import React, { useCallback, useState } from "react";
import { DeleteIcon, QuestionCircleIcon, } from "@shopify/polaris-icons";
import { useDispatch, useSelector } from "react-redux";
import { AddRules, DeleteRules, Rules, UpdateRules, deletedUpdate, } from "../../../../../data/features/plansDetails/plansDetailsSlice";
import ChangeProducts from "../../../../GlobalPartials/ChangeProducts/ChangeProducts";
import DeleteModal from "../../../../GlobalPartials/DeleteModal/DeleteModal";
import { updateTiers } from "../../../../../data/features/plans/plansSlice";

export default function RestrictedContent() {
    const dispatch = useDispatch();

    // redux data
    const selectedIndex$ = useSelector((state) => state.plansDetail?.selectedIndex);
    const rulesDetail$ = useSelector(
        (state) => state.plansDetail?.data?.plan_groups[selectedIndex$]
    );


    const planData$ = useSelector((state) => state.plansDetail?.data?.storeData);
    const updated_tiers$ = useSelector((state) => state?.plans?.updated_tiers);

    // rule names options
    const ruleNames = [
        { label: "Show all pages to members only", value: "all_pages" },
        { label: "Show specific page to members only", value: "page" },
        { label: "Show pages with a specific template to members only", value: "show_page_specific_template" },

        { label: "Show all blogs to members only", value: "all_blogs" },
        { label: "Show specific blog to members only", value: "blog" },

        { label: "Show specific blog posts to members only", value: "article" },
        { label: "Show blog posts with a specific tag to members only", value: "show_blog_post_specific_tag" },
        { label: "Show blog posts with a specific template to members only", value: "show_blog_post_specific_template" },

        { label: "Show all products to members only", value: "all_products" },
        { label: "Show specific products to members only", value: "product" },
        { label: "Show products with a specific tag to members only", value: "show_product_specific_tag" },
        { label: "Show products with a specific vendor to members only", value: "show_product_specific_vendor" },
        {
            label: "Show specific collections to members only",
            value: "collection",
        },
        { label: "Show collections with a specific template to members only", value: "show_collection_specific_template" },


        { label: "Show product prices to members only", value: "prices" },
        { label: "Show Add to Cart button to members only", value: "cart" },




        { label: "Show URLs containing the following string to members only", value: "urls" },
        { label: "Show Add to Cart button for specific products to members only", value: "add_to_cart_specific_product" },
        { label: "Show Add to Cart button for specific collections to members only", value: "add_to_cart_specific_collection" },




    ];

    // rule name select handlechange event
    const handleRuleChange = useCallback(
        (value, name, id, index) => {

            console.log("valuse mis an " + value);
            if (name === 'rule_attribute1') {
                const updatedFields = {
                    rule_attribute1: value !== undefined ? value : ''
                };
                console.log("valuse updatedFieldsupdatedFields an " + updatedFields);

                dispatch(UpdateRules({ id, updatedFields }));
                return
            }

            let getRuleValue = ruleNames.filter((item) => item.value === value);
            const updatedFields = {
                [name]: getRuleValue[0].label,
                rule_type: value,
                rule_attribute1: (planData$?.[value]?.length > 0 && value !== undefined) ? planData$?.[value][0]?.id : ''
            };
            dispatch(UpdateRules({ id, updatedFields }));

            dispatch(updateTiers(rulesDetail$?.tier_id));
        }, [rulesDetail$, updated_tiers$]);

    // Add Restricted Content - Button
    const [counter, setCounter] = useState(Math.floor(Math.random() * 120));
    const addRestrictedContent = useCallback(() => {
        const newCounter = Math.floor(Math.random() * 120);
        setCounter(newCounter);
        const data = {
            id: newCounter,
            rule_type: "all_pages",
            rule_name: "Show all pages to members only",
            rule_attribute1: "",
            rule_attribute2: "",
            rule_attribute1_handle: "",
            isNew: true
        };
        dispatch(AddRules(data));
        dispatch(updateTiers(rulesDetail$?.tier_id));
    }, [rulesDetail$, counter, updated_tiers$]);

    // ------ ResourcePicker -------
    const [resourcePickerOpen, setResourcePickerOpen] = useState(false);

    // variant selection
    const [prodcuctId, setProductId] = useState();

    // ResourcePicker open/close
    const handleResourcePickerToggle = useCallback((id) => {
        setProductId(id);
        setResourcePickerOpen(!resourcePickerOpen);
    }, [resourcePickerOpen, prodcuctId]);


    const handleResourceSelection = useCallback((resources, id) => {

        let product_ids = [];
        let product_names = [];

        const data = resources?.selection?.map((item, index) => {

            var numberPattern = /\d+/g;
            console.log(item);

            // Extract numbers from the input string
            var numbersOnly = item?.id.match(numberPattern).join("");
            product_ids.push(numbersOnly);
            product_names.push(item?.title ? item?.title : '');

        })

        const updatedFields = {

            rule_attribute1: product_ids.toString(),
            rule_attribute1_handle: product_names.toString()
        };
        dispatch(UpdateRules({ id, updatedFields }));

        // Close the ResourcePicker after handling the selection
        setResourcePickerOpen(false);
    }, [rulesDetail$]);


    // drag&drop table cell...
    const onDragEnd = useCallback(
        (result) => {
            if (!result.destination) return;
            const updatedRows = [...rulesDetail$?.rules];
            const [movedRow] = updatedRows.splice(result.source.index, 1);
            updatedRows.splice(result.destination.index, 0, movedRow);
            dispatch(Rules(updatedRows));
        },
        [rulesDetail$]
    );


    // delete rules...
    // const [deleteModal, setDeleteModal] = useState(false);
    // const [deleteId, setDeleteId] = useState();
    // open delete modal method
    // const deleteRulesModel = useCallback((id) => {
    //     setDeleteModal(!deleteModal);
    //     setDeleteId(id);
    // }, [rulesDetail$, deleteModal])

    // delete rules method
    const deleteRules = useCallback((id) => {
        const findRulesId = rulesDetail$?.rules.find(item => item.id === id);
        console.log(findRulesId)

    if(!findRulesId?.isNew){
        dispatch(deletedUpdate({key: 'rules', id: id}))
    }
    dispatch(DeleteRules(id));
    dispatch(updateTiers(rulesDetail$?.tier_id));
    console.log(`Delete id ${id}`);
}, [rulesDetail$]);


    // table cells
    const resourceName = {
        singular: "row",
        plural: "rows",
    };

    const rowMarkup = rulesDetail$?.rules?.map(({ id, rule_type, rule_name, rule_attribute1, rule_attribute2, rule_attribute1_handle, }, index) => (
        <IndexTable.Row id={id} key={index} position={index}>

            <IndexTable.Cell>
                <div className="rule_name_select_field">
                    <Select
                        options={ruleNames}
                        value={rule_type}
                        onChange={(value) =>
                            handleRuleChange(value, "rule_name", id, index)
                        }
                    />
                </div>
            </IndexTable.Cell>
            <IndexTable.Cell>
                <div className="rule_name_select_field">
                    {rule_type === "article" ||
                        rule_type === "blog" ||
                        rule_type === "page"
                        ? (
                        <Select
                            options={
                                planData$?.[rule_type]?.map((option, id) => {
                                    return {
                                        label: `${option?.title}`,
                                        value: `${option?.id}`,
                                    };
                                })
                            }
                            onChange={(value) =>
                                handleRuleChange(
                                    value,
                                    "rule_attribute1",
                                    id,
                                    index
                                )
                            }
                            value={rule_attribute1}
                        />
                    ) : rule_type === "product" ||
                        rule_type === "collection" ||
                        rule_type === "add_to_cart_specific_product"||  //hitarthi
                        rule_type === "add_to_cart_specific_collection" //hitarthi
                        ?(
                        <>
                            <ChangeProducts
                                resource= { rule_type === "product" ||  rule_type ==="add_to_cart_specific_product" ? "Product" :  "Collection" }  //hitarthi
                                is_alreadyselected = { rule_name === "Show specific products to members only" || rule_name === "Show specific collections to members only" ? true :  false }
                                data = {rule_attribute1}

                                value={rule_attribute1_handle}
                                label={""}
                                // inputName={rule_type}
                                // onChange={''}
                                handleResourcePickerToggle={() => handleResourcePickerToggle(id)}
                                resourcePickerOpen={resourcePickerOpen}
                                onSelection={(resources) => handleResourceSelection(resources, prodcuctId)}
                                onCancel={() => setResourcePickerOpen(false)}
                                placeHolder={rule_type}
                                error={''}
                                selectMultiple={true}
                            />
                        </>
            //////////// hitarthi
                    ) :rule_type === "urls"||
                    rule_type === "show_page_specific_template"|| //template
                    rule_type === "show_product_specific_vendor"|| //vendor selector
                    rule_type === "show_blog_post_specific_tag"||       //tag selector
                    rule_type === "show_collection_specific_template"||//tag selector
                    rule_type === "show_product_specific_tag" ||//tag selector
                    rule_type === "show_blog_post_specific_template"    //tag selector
                     ?(
                        <>
                            <TextField
                                autoComplete="off"
                                value={rule_attribute1}
                                onChange={(value) =>
                                    handleRuleChange(
                                        value,
                                        "rule_attribute1",
                                        id,
                                        index
                                    )
                                }
                                />
                        </>
            /////////// hitarthi
                    ):(
                        ""
                    )}
                </div>
            </IndexTable.Cell>
            <IndexTable.Cell>
                <Button onClick={() => deleteRules(id)}>
                    <Icon source={DeleteIcon} color="base" />
                </Button>
            </IndexTable.Cell>
        </IndexTable.Row>
    )
    );

    return <>
        <div className="restricted_content_accordion">
            <Text variant="bodyLg" as="h6" fontWeight="regular">
                If you would like to show some content exclusively to members,
                create rules to define what will be hidden from non-members. If
                you are hiding products, prices, or the add to cart button,
                additional configuration will be required. Contact us for
                assistance, or get more information on this{" "}
                <Link url="https://support.simplee.best/en/articles/5498795-advanced-membership-rules" target="_blank">support article.</Link>
            </Text>

            {/* Add Restricted Content Table */}
            {
                rulesDetail$?.rules?.length > 0 &&
                <div className="add_restricted_table_block ms-margin-top">
                    <IndexTable
                        resourceName={resourceName}
                        itemCount={rulesDetail$?.rules?.length}
                        headings={[
                            { id: 1, title: "Rule Name" },
                            {
                                id: 2,
                                title: (
                                    <span className="pagename_th">
                                        Page Name
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
            }
            <div className="add_restricted_content_button ms-margin-top">
                <Button onClick={addRestrictedContent} variant='primary' >
                    Add Restricted Content
                </Button>
            </div>

            {/* delete modal */}
            {/* <DeleteModal active={deleteModal} setDeleteModal={setDeleteModal} deleteMethod={deleteRules} /> */}
        </div>
    </>;
}
