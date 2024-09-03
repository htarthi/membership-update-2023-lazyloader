import React, { useCallback, useState } from 'react'
import {
    Modal,
    TextField,
    Text
} from "@shopify/polaris";

export default function UpdateBalanceModal({active, handleChange}) {

    const [value, setValue] = useState('');

    const updateBalanceHandleChange = useCallback(
      (newValue) => setValue(newValue),
      [],
    );

  return (
    <Modal
        open={active}
        onClose={handleChange}
        title={<Text>Update Balance</Text>}
        primaryAction={{
            content: "Update",
            // tone : 'success',
            onAction: handleChange,
        }}
        secondaryActions={[
            {
                content: "Cancel",
                onAction: handleChange,
            },
        ]}
    >
        <Modal.Section>
            <div className="input_field">
                <TextField
                label="Update balance"
                type="number"
                value={value}
                onChange={updateBalanceHandleChange}
                autoComplete="off"
                prefix="USD $"
                />
            </div>
        </Modal.Section>
    </Modal>
  )
}
