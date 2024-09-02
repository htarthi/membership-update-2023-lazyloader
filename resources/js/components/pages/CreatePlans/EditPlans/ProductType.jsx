import {
    Badge,
    Button,
    ButtonGroup,
    Icon,
    Select,
    Text,
    TextField,
} from "@shopify/polaris";
import React from "react";
import { DeleteIcon } from "@shopify/polaris-icons";
import TooltipComp from "../../Plans/partials/TooltipComp";

export default function ProductType({
    typeOptions,
    tagOptions,
    handleChangeEvent,
    productVal,
}) {
    return (
        <div className="products_type_block ms-margin-top">
            {/* tooltip */}
            <TooltipComp
                title={"Products"}
                content={"This order has shipping labels."}
            />

            {/* when product */}
            <div className="when_product_type_tag ms-margin-top">
                {/* when product - type */}
                <div className="when_product_type">
                    <Text variant="bodyLg" as="h6" fontWeight="regular">
                        When product
                    </Text>

                    <div className="select_type_block">
                        <Select
                            options={typeOptions}
                            onChange={(value) =>
                                handleChangeEvent(value, "select_product_type")
                            }
                            value={productVal.select_product_type}
                            placeholder="Type"
                        />
                    </div>

                    <Text variant="bodyLg" as="h6" fontWeight="regular">
                        is
                    </Text>

                    <div className="input_type_block">
                        <TextField
                            value={productVal.input_product_type}
                            onChange={(value) =>
                                handleChangeEvent(value, "input_product_type")
                            }
                            autoComplete="off"
                        />
                    </div>

                    <div className="delete_block">
                        <Button>
                            <Icon source={DeleteIcon} color="base" size="large" />
                        </Button>
                    </div>
                </div>

                {/* or */}
                <div className="or_block ms-margin-top-bottom-ten">
                    <Text variant="bodyLg" as="h6" fontWeight="regular">
                        or
                    </Text>
                </div>

                {/* when product tag */}
                <div className="when_product_type">
                    <Text variant="bodyLg" as="h6" fontWeight="regular">
                        When product
                    </Text>

                    <div className="select_type_block">
                        <Select
                            options={tagOptions}
                            onChange={(value) =>
                                handleChangeEvent(value, "select_product_tag")
                            }
                            value={productVal.select_product_tag}
                            placeholder="Tag"
                        />
                    </div>

                    <Text variant="bodyLg" as="h6" fontWeight="regular">
                        is
                    </Text>

                    <div className="input_type_block">
                        <TextField
                            value={productVal.input_product_tag}
                            onChange={(value) =>
                                handleChangeEvent(value, "input_product_tag")
                            }
                            autoComplete="off"
                        />
                    </div>

                    <div className="or_and_block">
                        <ButtonGroup segmented>
                            <Button>Or</Button>
                            <Button>And</Button>
                        </ButtonGroup>
                    </div>

                    <div className="delete_block">
                        <Button>
                            <Icon source={DeleteIcon} color="base"  size="large"/>
                        </Button>
                    </div>
                </div>

                {/* Assigned to 24 products */}
                <div className="assigned_to_products ms-margin-top">
                    <Badge tone="attention">
                        Assigned to <b>24</b> products
                    </Badge>
                </div>
            </div>
        </div>
    );
}
