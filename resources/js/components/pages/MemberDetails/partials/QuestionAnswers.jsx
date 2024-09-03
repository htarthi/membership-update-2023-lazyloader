import { Badge, Collapsible, Icon, LegacyCard, Text,Button } from '@shopify/polaris'
import React, { useCallback, useState } from 'react'
import { ChevronDownIcon, ChevronUpIcon } from '@shopify/polaris-icons';
import { useSelector } from 'react-redux';
import EditQuestionAnswerModal from './EditQuestionAnswerModal';
export default function QuestionAnswers() {

    const customerAnswer$ = useSelector((state) => state.memberDetails?.data?.contract?.customer_answer);

    const [open, setOpen] = useState(false);
    const [ModalOpen , setModalOpen] = useState(false);

    const handleToggle = useCallback(() => setOpen((open) => !open), []);


    const editHandleEvent = useCallback(() => {
        setModalOpen(true);
    }, [ModalOpen])
  return <>
  {
      customerAnswer$?.length > 0 ?
      <div className='question_answers_wrap ms-margin-top main_box_wrap'>
          <LegacyCard>

              {/* Heading & Edit */}
              <div className='edit_header_block'>

                  <button className={`button_style accordian_question_block ${customerAnswer$?.length <= 0 ? 'disabled_button' : ''}`} onClick={handleToggle}>
                      <Text variant="headingMd" as="h6" fontWeight="semibold">Questions and answers</Text>
                      <div className={customerAnswer$?.length <= 0 ? 'disabled_icon' : ''}>
                          <Icon source={open ? ChevronUpIcon : ChevronDownIcon} color="base" />
                      </div>
                  </button>

                  <div className='total_answer_edit_block'>
                      <Badge>{customerAnswer$?.length} {customerAnswer$?.length == 1 ?  "Answer" : "Answers"}</Badge>
                      <Button onClick={()=>editHandleEvent()}  variant="plain">Edit</Button>
                  </div>

              </div>

              {/* question & answers */}
              <Collapsible
              open={open}
              id="basic-collapsible"
              transition={{duration: '500ms', timingFunction: 'ease-in-out'}}
              expandOnPrint>
                  <div className='question_answer_row'>
                      {
                          customerAnswer$?.length > 0 ?
                          customerAnswer$?.map((item, index) => {
                              return(
                                  <div className='question_answer_col ms-margin-top' key={index}>
                                      <Text variant="bodyLg" as="h6" >{item?.question}</Text>
                                      <Text variant="bodyLg" as="h6" fontWeight='regular'>{item?.answer}</Text>
                                  </div>
                              )
                          })
                          :
                          ''
                      }
                  </div>
              </Collapsible>

          </LegacyCard>
          <EditQuestionAnswerModal  ModalOpen={ModalOpen}  setModalOpen={setModalOpen} />

      </div>
      :
      ''

  }
  </>;
}
