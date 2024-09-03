import { ActionList, Button, LegacyCard, Popover, Text, } from "@shopify/polaris";
import React, { useCallback, useState } from "react";
import CreateGiftCardModal from "./CreateGiftCardModal";
import UpdateBalanceModal from "./UpdateBalanceModal";

export default function MembershipStoreCredit() {
    // store credit edit popover
    const [popoverActive, setPopoverActive] = useState(false);

    const togglePopoverActive = useCallback(
        () => setPopoverActive((popoverActive) => !popoverActive),
        []
    );

    // Create Gift Card
    const [createCardActive, setcreateCardActive] = useState(false);

    const CreateCardModal = useCallback(
        () => setcreateCardActive(!createCardActive),
        [createCardActive]
    );

    // Update Balance
    const [updateBalance, setUpdateBalance] = useState(false);

    const updateBalanceModal = useCallback(
        () => setUpdateBalance(!updateBalance),
        [updateBalance]
    );

    return (
        <div className="store_credit_block ms-margin-top main_box_wrap">
            <LegacyCard>

                {/* Heading & Edit */}
                <div className="edit_header_block">
                    <Text variant="headingMd" as="h6" fontWeight="semibold">
                        Store Credit
                    </Text>

                    {/* store credit edit popover */}
                    <Popover
                        active={popoverActive}
                        activator={
                            <Button  onClick={togglePopoverActive} variant="plain">
                                Edit
                            </Button>
                        }
                        autofocusTarget="first-node"
                        onClose={togglePopoverActive}
                    >
                        <ActionList
                            actionRole="menuitem"
                            items={[
                                {
                                    content: "Update balance",
                                    onAction: () => setUpdateBalance(true),
                                },
                                {
                                    content: "Create gift card",
                                    onAction: () => setcreateCardActive(true),
                                },
                            ]}
                        />
                    </Popover>
                </div>

                <div className="usd_wrap ms-margin-top">
                    <Text variant="headingLg" as="h5">
                        USD 589.99
                    </Text>
                </div>

            </LegacyCard>

            {/* Create Gift Card modal */}
            <CreateGiftCardModal
                active={createCardActive}
                handleChange={CreateCardModal}
            />

            {/* Update Balance */}
            <UpdateBalanceModal
                active={updateBalance}
                handleChange={updateBalanceModal}
            />

        </div>
    );
}
