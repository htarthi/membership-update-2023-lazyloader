import React from "react";
import { Button, Icon, TextField } from "@shopify/polaris";
import { ProductIcon } from "@shopify/polaris-icons";
import { ResourcePicker } from "@shopify/app-bridge-react";
import { useSelector } from "react-redux";
import { getType } from "@reduxjs/toolkit";

export default function ChangeProducts({
    resource,
    is_alreadyselected,
    data,
    product_id,
    selectedId,
    value,
    label,
    handleResourcePickerToggle,
    resourcePickerOpen,
    onSelection,
    onCancel,
    placeHolder,
    error,
    selectMultiple,
    is_show_change_product = true,
    showVariants = false
}) {
    const newStore$ = useSelector((state) => state.plans?.newStore);

    if (data) {

        if (data.indexOf(",")) {
            var idArray = data.split(",");

            // Initialize an array to store response objects
            var responseArray = [];

            // Iterate through each ID
            if (resource == "Product") {
                idArray.forEach(function (id) {
                    var responseObject = {
                        id: "gid://shopify/Product/" + id,
                    };
                    responseArray.push(responseObject);
                });
            } else {
                idArray.forEach(function (id) {
                    var responseObject = {
                        id: "gid://shopify/Collection/" + id,
                    };
                    responseArray.push(responseObject);
                });
            }
        }
        else {
            var responseArray = {
                id: "gid://shopify/Product/" + data,

            }
        }


    }



    return <>
        {/* text field - product */}
        <div
            className={`${label === "" && "without_label"
                } input_fields_wrap product_input_field`}
        >
            {/* product field */}
            <TextField
                label={label}
                value={value}
                onChange={""}
                autoComplete="off"
                prefix={<Icon source={ProductIcon} color="base" />}
                placeholder={placeHolder ? placeHolder : ""}
                error={error}
                readOnly
            />

            {is_show_change_product && (
                <div div className="change_product_block">
                    <Button  onClick={handleResourcePickerToggle} variant="plain">
                        {resource == "Product"
                            ? newStore$
                                ? "Select Product"
                                : "Change Product"
                            : newStore$
                                ? "Select Collection"
                                : "Change Collection"}
                    </Button>
                </div>
            )}
        </div>

        {/* --------------- resource picker - Select Variant ---------------- */}
        {resourcePickerOpen && product_id == selectedId && (
            <>
                <ResourcePicker
                    resourceType={
                        resource == "Product" ? "Product" : "Collection"
                    }
                    open={resourcePickerOpen}
                    onSelection={onSelection}
                    selectMultiple={selectMultiple}
                    showVariants={showVariants}
                    onCancel={onCancel}
                    initialSelectionIds={
                        is_alreadyselected ? responseArray : []
                    }
                />
            </>
        )}
    </>;
}
