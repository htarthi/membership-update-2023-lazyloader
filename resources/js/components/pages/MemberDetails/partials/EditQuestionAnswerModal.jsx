import { Modal, TextContainer, TextField , Text} from '@shopify/polaris';
import React, { useCallback } from 'react'
import { useDispatch } from 'react-redux';
import { useSelector } from 'react-redux';
import { useParams } from 'react-router-dom';
import { QuestionAnswerReducer } from '../../../../data/features/memberDetails/memberDetailsSlice';
import { questionAnswerUpdate } from '../../../../data/features/memberDetails/membersDetailsAction';
import _ from 'lodash';

export default function EditQuestionAnswerModal({ ModalOpen, setModalOpen }) {
    const dispatch = useDispatch();
    const customerAnswer$ = useSelector((state) => state.memberDetails?.data?.contract?.customer_answer);
    const contract$ = useSelector((state) => state.memberDetails?.data?.contract);

    const { id } = useParams();
    const cancleHandleChange = useCallback(() => {
        setModalOpen(!ModalOpen);
    }, [ModalOpen]);

    const handleChangeEvent = useCallback((value, index, key) => {
        const payload = {
            [key]: value
        }
        dispatch(QuestionAnswerReducer({ index: index, data: payload }))
    }, [customerAnswer$])


    const handleSaveEvent = useCallback(() => {
        const payload = {
            reactiveDate: "",
            contract_id: id,
            customer_id: contract$.ss_customer_id,
            type: "all",
            deleted: [],
            line_items: contract$.lineItems,
            note: contract$.customer.notes,
            prepaid_renew: 0,
            customer_answer: customerAnswer$,
            next_order_date: contract$.next_order_date,
            selling_plan_id: contract$.ss_plan_id,
            shopify_discount_id: ""
        }
        dispatch(questionAnswerUpdate({ id: parseInt(id), payload: payload }))
        setModalOpen(false)
        ModalOpen(false)
    }, [customerAnswer$])

    return (
        <>

            <Modal
                open={ModalOpen}
                onClose={cancleHandleChange}
                title={<Text>Question Answer Details</Text>}
                primaryAction={{
                    content: 'Update',
                    // tone : 'success',
                    onAction: handleSaveEvent
                    
                }}
                secondaryActions={[
                    {
                        content: 'Cancel',
                        onAction: cancleHandleChange
                    },
                ]}
            >
                <Modal.Section>
                    {
                        customerAnswer$.map((data, index) => {
                            return (
                                <div className="membership_detail_edit_wrap">

                                    <div className="input_fields_wrap input-margin-top" key={index}>

                                        <TextField onChange={(value) => handleChangeEvent(value, index, 'answer')} key={index} label={data.question} value={data.answer}></TextField>
                                    </div>
                                </div>)
                        })
                    }
                </Modal.Section>
            </Modal>
        </>
    )
}
