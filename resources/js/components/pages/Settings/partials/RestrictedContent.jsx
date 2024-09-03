import React, { Component, useRef, useCallback } from 'react';
import { Editor } from '@tinymce/tinymce-react';
import { useSelector, useDispatch } from "react-redux";
import { updateSetting , setChanged } from "../../../../data/features/settings/settingsDataSlice";

export default function RestrictedContent() {

    const editorRef = useRef();
    const dispatch = useDispatch();
    const data$ = useSelector((state) => state.settings.data.data.setting);
    const settings$ = useSelector(
        (state) => state?.settings?.data?.data?.setting
    );
    const saveChanges = useCallback(
        () => {
            dispatch(updateSetting({ restricted_content: editorRef.current.getContent() }));
            dispatch(setChanged({['isChangeData']: true}));
        },
        [settings$]
    );

    document.addEventListener('focusin', (e) => {
        if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
            document.querySelectorAll('.tox-dialog').forEach(function(element) {
                element.style.zIndex = '2003';
            })
            e.stopImmediatePropagation();
        }
    });

    return (
        <>
            <div className='plans_row_block'>
                <div className='plans_col'>
                    <div className="col-lg-12">
                        <div className="editor_wrap">
                            <div className='resticted_content'>
                                {/* <div className='resticted_data'>
                                    <h3 className='resticted_heading'>Restricted Content Message</h3>
                                     <span className='resticted_deacription'>
                                    When non-members load a page, product, or blog post they don’t have access to, what message should be displayed in its place?</span>
                                </div> */}

                                <Editor
                                    // tinymceScriptSrc='/tinymce/js/tinymce/tinymce.min.js'
                                    apiKey='1rcs3k88yvqskndstp9m23nacrexuwycmlmalh5k8f3u5bx3'
                                    className="evl-editor"
                                    onInit={(evt, editor) => editorRef.current = editor}
                                    initialValue={data$ ? data$?.restricted_content : ""}
                                    onChange={saveChanges}
                                    init={{
                                        height: 426,
                                        width: 720,
                                        menubar: true,
                                        plugins: [
                                            'advlist', 'autolink',
                                            'lists', 'link', 'image', 'charmap','code' ,'preview', 'anchor', 'searchreplace', 'visualblocks',
                                            'fullscreen','insertdatetime', 'media', 'table', 'help', 'wordcount'
                                        ],
                                        toolbar: 'undo redo | casechange blocks | bold italic backcolor | ' +
                                            'alignleft aligncenter alignright alignjustify | ' +
                                            'bullist numlist checklist outdent indent | removeformat | a11ycheck code table help'
                                    }}
                                />
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

