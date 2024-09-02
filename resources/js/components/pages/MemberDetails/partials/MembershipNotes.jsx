import { LegacyCard, Modal, Text, TextField } from '@shopify/polaris'
import React, { useCallback, useState, useEffect } from 'react'
import EditComp from './EditComp'
import { useSelector } from 'react-redux';
import { useDispatch } from 'react-redux';
import { customerReducer } from '../../../../data/features/memberDetails/memberDetailsSlice';
import { subscribeUpdate, subscriberEdit } from '../../../../data/features/memberDetails/membersDetailsAction';
import { useParams } from 'react-router-dom';

export default function MembershipNotes() {

    const dispatch = useDispatch();
    const { id } = useParams();
    const customer$ = useSelector((state) => state.memberDetails?.data?.contract?.customer);
    const memberDetailsData$ = useSelector((state) => state.memberDetails?.data?.contract);

    // ----- membership details model ------
    const [modalOpen, setModalOpen] = useState(false);

    // cancle modal
    const cancleHandleChange = useCallback(() => setModalOpen(!modalOpen), [modalOpen]);

    // -------- edit note --------
    const [editNote, setEditNote] = useState(customer$?.notes);

    const editNoteHandleChange = useCallback(
        (newValue) => setEditNote(newValue),
        [editNote]);

    // open modal
    const editHandleEvent = () => {
        setModalOpen(true);
    }

    // save note
    const saveHandleChange = useCallback(() => {
        if (editNote) {
            const subscribeData = {
                data: {
                    contract_id: parseInt(id),
                    customer_answer: memberDetailsData$?.customer_answer,
                    customer_id: memberDetailsData$?.customer?.id,
                    deleted: [],
                    line_items: memberDetailsData$?.lineItems,
                    next_order_date: memberDetailsData$?.next_order_date,
                    note: editNote,
                    prepaid_renew: memberDetailsData$?.prepaid_renew,
                    reactiveDate: "",
                    selling_plan_id: memberDetailsData$?.ss_plan_id,
                    shopify_discount_id: "",
                    type: "all"
                }
            }
            dispatch(subscribeUpdate({ id, subscribeData }))
            dispatch(customerReducer({ notes: editNote }))
        }
        setModalOpen(false);
    }, [editNote, customer$])
    return (
        <div className='ms_notes_wrap ms-margin-top main_box_wrap'>
            <LegacyCard>

                {/* Heading & Edit */}
                <EditComp title={"Notes"} editHandleEvent={editHandleEvent} />

                {
                    customer$?.notes &&
                    <div className='notes_block ms-margin-top'>
                        <TextField
                            value={customer$?.notes}
                            multiline={5}
                            autoComplete="off"
                            variant="bodyLg"
                            as="h6"
                            fontWeight='regular'
                            color='subdued'
                            readOnly
                        />
                    </div>
                }

            </LegacyCard>

            {/* Add a Note edit modal */}
            <Modal
                open={modalOpen}
                onClose={cancleHandleChange}
                title={<Text>Add a Note</Text>}
                primaryAction={{
                    content: `Save`,
                    // tone : 'success',
                    onAction: saveHandleChange,
                }}
                secondaryActions={[
                    {
                        content: `Cancel`,
                        onAction: cancleHandleChange,
                    }
                ]}
            >
                <Modal.Section>
                    <div className='edit_note_wrap'>
                        <TextField
                            value={editNote}
                            onChange={editNoteHandleChange}
                            multiline={5}
                            autoComplete="off"
                        />
                    </div>
                </Modal.Section>
            </Modal>
        </div>
    )
}
