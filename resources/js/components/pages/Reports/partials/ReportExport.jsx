import { Button, ButtonGroup, Link, Text, Modal, TextField } from '@shopify/polaris';
import React from 'react';

export default function ReportExport({ title, active, primaryaction, onclose, lable, email, onchange, error }) {

    return (
        <div>
            <Modal
                open={active}
                onClose={onclose}
                title={title}
                primaryAction={{
                    content: 'Send Mail',
                    onAction: primaryaction, // Removed curly braces
                }}
                secondaryActions={[
                    {
                        content: 'Cancel',
                        onAction: onclose, // Removed curly braces
                    },
                ]}
            >
                <Modal.Section>
                    <TextField
                        type='email'
                        label={lable}
                        value={email}
                        name='email'
                        onChange={onchange}
                        autoComplete="email"
                        error={error}
                    />
                </Modal.Section>
            </Modal>
        </div>
    );
}
