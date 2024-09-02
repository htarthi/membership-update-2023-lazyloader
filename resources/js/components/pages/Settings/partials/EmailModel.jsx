import { Button, Icon, Modal, Select, TextField ,Text} from "@shopify/polaris";
import { useSelector } from "react-redux";
import { Editor } from '@tinymce/tinymce-react';
import React, { useRef, useState, useCallback, useEffect } from "react";
import { useDispatch } from "react-redux";
import { updateEmailTemplate } from "../../../../data/features/settings/settingsDataSlice";
import { editEmailBody, sendEmailTest } from "../../../../data/features/settings/settingAction";
import { AlertDiamondIcon } from '@shopify/polaris-icons';

export const EmailModel = ({ active, setActive, value }) => {

    const dispatch = useDispatch();
    const data$ = useSelector((state) => state.settings.data.data[value?.data])
    const recurringNotify$ = useSelector((state) => state.settings.data.data['recurringNotifyEmailNoti'])
    const errors$ = useSelector((state) => state.settings?.errors);
    const sentTestSuccess$ = useSelector((state) => state.settings.sentTestSuccess);
    const [checkError, setCheckError] = useState(false);
    const editorRef = useRef(null);
    const handlecancel = () => {
        setActive(false);
        setCheckError(false);
    };
    const [daysAhead, setDaysAhead] = useState(`${recurringNotify$?.days_ahead} ${recurringNotify$?.days_ahead + 1 > 1 ? 'days' : 'day'} before order date`);

    // save handle change
    const handleSaveClick = useCallback(() => {
        const emailBody = {
            "subject": data$?.subject,
            "category": value?.category,
            "days_ahead": parseInt(data$?.days_ahead),
            "html_body": editorRef.current.getContent(),
            "mailto": data$?.mailto
        }
        dispatch(editEmailBody({ data: emailBody }))
        dispatch(updateEmailTemplate({ template: value.data, updateField: emailBody }))
        data$.subject && editorRef.current.getContent() ? setActive(false) : '';
        setCheckError(true);
    }, [checkError, data$])

    // input, select handle change event
    const handleFieldChange = useCallback((val, key) => {
        const updateField = {
            [key]: val
        }
        dispatch(updateEmailTemplate({ template: value.data, updateField }))
        key === 'days_ahead' && setDaysAhead(val);
    }, [data$, daysAhead])

    // send test handle change
    const handleSendTest = useCallback(() => {
        const emailBody = {
            "subject": data$?.subject,
            "category": value?.category,
            "days_ahead": parseInt(data$?.days_ahead),
            "html_body": editorRef.current.getContent(),
            "mailto": data$?.mailto
        }
        dispatch(sendEmailTest({ data: emailBody }));
        setCheckError(true);
    }, [data$])

    // set null of mailto
    useEffect(() => {
        sentTestSuccess$ && dispatch(updateEmailTemplate({ template: value.data, updateField: { mailto: null } }))
    }, [sentTestSuccess$])

    document.addEventListener('focusin', (e) => {
        if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
            document.querySelectorAll('.tox-dialog').forEach(function(element) {
                element.style.zIndex = '2003';
            })
            e.stopImmediatePropagation();
        }
    });

    return (
        <Modal
            size="large"
            open={active}
            onClose={handlecancel}
            title={<Text>{value?.title}</Text>}
            primaryAction={{
                content: "Save",
                // tone : 'success',
                onClick: handleSaveClick,
            }}
            secondaryActions={[
                {
                    content: "Cancel",
                    onClick: handlecancel,
                },
            ]}
            footer={[
                <div className="send_test_wrap">
                    <TextField
                        size="small "
                        type="text"
                        autoComplete="off"
                        value={data$?.mailto || ''}
                        onChange={(value) => handleFieldChange(value, 'mailto')}
                        error={!data$?.mailto && checkError ? errors$?.length > 0 ? errors$[0]['data.mailto'] : '' : ''}
                    />
                    <Button variant='secondary' onClick={handleSendTest} >
                        Send test
                    </Button>
                </div>,
            ]}
        >
            <Modal.Section>
                <div className="setting_edit_modal_wrap">

                    <div className="setting_modal_field_wrap">
                        <div className="subject_field">
                            <TextField
                                label="Subject"
                                type="text"
                                autoComplete="off"
                                value={data$?.subject}
                                onChange={(value) => handleFieldChange(value, 'subject')}
                                error={!data$?.subject ? errors$?.length > 0 ? errors$[0]['data.subject'] : '' : ''}
                            />
                        </div>
                        {value.data === 'recurringNotifyEmailNoti' &&
                            <div className="notify_field">
                                <Select
                                    label="When to notify?"
                                    options={
                                        Array(15).fill().map((i, id) => {
                                            return { label: `${id + 1} ${id + 1 > 1 ? 'days' : 'day'} before order date`, value: `${id + 1} ${id + 1 > 1 ? 'days' : 'day'} before order date` }
                                        })
                                    }
                                    onChange={(value) => handleFieldChange(value, 'days_ahead')}
                                    value={daysAhead}
                                />
                            </div>
                        }                    </div>

                    <div className="editor_wrap">
                        <Editor
                                    tinymceScriptSrc='/tinymce/js/tinymce/tinymce.min.js'

                            apiKey='1rcs3k88yvqskndstp9m23nacrexuwycmlmalh5k8f3u5bx3'
                            className="evl-editor"
                            onInit={(evt, editor) => editorRef.current = editor}
                            initialValue={data$ ? data$?.html_body : ""}

                            init={{
                                height: 426,
                                width: 940,
                                menubar: true,
                                plugins: [
                                    'advlist', 'autolink','code',
                                    'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'searchreplace', 'visualblocks',
                                    'fullscreen','insertdatetime', 'media', 'table', 'help', 'wordcount'
                                ],
                                toolbar: 'undo redo | casechange blocks | bold italic backcolor | ' +
                                    'alignleft aligncenter alignright alignjustify | ' +
                                    'bullist numlist checklist outdent indent | removeformat | a11ycheck code table help'
                            }}
                        />
                    </div>
                    {
                        !data$?.html_body && checkError ?
                            (errors$?.length > 0 && errors$[0][`data.html_body`]) &&
                            <span className='error_wrap'>
                                <Icon source={AlertDiamondIcon} color="critical" />
                                {errors$[0][`data.html_body`]}
                            </span>
                            :
                            ''
                    }

                </div>

            </Modal.Section>
        </Modal>
    );
};
