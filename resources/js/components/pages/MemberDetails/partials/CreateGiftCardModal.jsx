import React, { useCallback, useState } from 'react'
import {
    Modal,
    Text,
    TextField,
} from "@shopify/polaris";

export default function CreateGiftCardModal({active, handleChange}) {

    const [value, setValue] = useState('');

    const cardAmountHandleChange = useCallback(
      (newValue) => setValue(newValue),
    []);

  return (
    <Modal
        open={active}
        onClose={handleChange}
        title={<Text>Create Gift Card</Text>}
        primaryAction={{
            content: "Create Gift Card",
            onAction: handleChange,
            // tone : 'success',
        }}
        secondaryActions={[
            {
                content: "Cancel",
                onAction: handleChange,
            },
        ]}
    >
        <Modal.Section>
            <div className="current_credit_balance">
                <Text variant="bodyLg" as="h6">Current credit balance</Text>
                <div className="ms-margin-top-ten">
                    <Text variant="headingLg" as="h5"> USD 589.99 </Text>
                </div>
            </div>

            <div className="input_field ms-margin-top">
                <TextField
                label="Gift card amount"
                type="number"
                value={value}
                onChange={cardAmountHandleChange}
                autoComplete="off"
                prefix="USD $"
                />
            </div>
        </Modal.Section>
    </Modal>
  )
}
