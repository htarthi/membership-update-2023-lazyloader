import {Modal, Text} from '@shopify/polaris';
import React from 'react';

export default function DeleteModal({active, setDeleteModal, deleteMethod}) {
    return (
        <div className="delete_modal_block">
            <Modal
                size="small"
                open={active}
                onClose={() => setDeleteModal(false)}
                title={<Text>Delete this tier ?</Text>}
                primaryAction={{
                    content: "Delete",
                    onAction: deleteMethod,
                    // tone : 'success',
                }}
                secondaryActions={[
                    {
                        content: "Cancel",
                        onAction: () => setDeleteModal(false),
                    },
                ]}
            >
                <Modal.Section>
                    <Text variant="headingMd" as="h6" tone="base" fontWeight='regular'>Do you want to delete this tier from the plan ?</Text>
                </Modal.Section>
            </Modal>
        </div>
    );
}
