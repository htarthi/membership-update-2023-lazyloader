import { Avatar, Button, LegacyCard, Text, TextField } from '@shopify/polaris'
import React, { useCallback, useState, useEffect } from 'react'
import { useDispatch } from 'react-redux';
import { useSelector } from 'react-redux';
import { commentPost } from '../../../../data/features/memberDetails/membersDetailsAction';

export default function PostComments() {
    const dispatch = useDispatch();
    const activityLog = useSelector((state) => state.memberDetails?.data?.contract?.activityLog);
    const contract$ = useSelector((state) => state.memberDetails?.data?.contract);
    const [value, setValue] = useState('');

    const handleChange = useCallback(
        (newValue) => setValue(newValue),
        [value]);

    // post comments
    const postComment = useCallback((e) => {
        e.preventDefault();
        if (value !== '') {

            const data = {
                customer_id: contract$?.customer?.id,
                msg: value,
                id: contract$?.id
            }

            dispatch(commentPost({ data }));
            setValue('');
        }
    }, [activityLog, value])
    // set height on the comment list
    useEffect(() => {
        let getCommnetWrapHeight = document.querySelector('.commnets_list_wrap');
        if (getCommnetWrapHeight?.offsetHeight > 300 && getCommnetWrapHeight !== null) {
            getCommnetWrapHeight.style.maxHeight = '560px';
            getCommnetWrapHeight.style.overflowY = 'auto';
        } else {
            getCommnetWrapHeight.style.maxHeight = `max-content`;
            getCommnetWrapHeight.style.overflow = 'unset';
        }
    }, [activityLog, contract$])

    return (
        <div className='post_comments_wrap ms-margin-top main_box_wrap'>
            {/* Leave a comment... */}
            <LegacyCard>
                <form className='leave_comments_wrap' onSubmit={(e) => postComment(e)}>
                    {/* input  & avatar */}
                    <div className='leave_comments_field'>
                        <div className='avatar_wrap'>
                            <Avatar customer />
                        </div>
                        <div className='input_field'>
                            <TextField
                                value={value}
                                onChange={handleChange}
                                autoComplete="off"
                                placeholder='Leave a comment...'
                            />
                        </div>
                    </div>
                    {/* post */}
                    <Button  submit variant="primary">Post</Button>
                </form>
            </LegacyCard>

            {/* comments */}
            <div className='commnets_list_wrap'>
                <div className='comments_wrap'>
                    <div className='comment_note'>
                        <Text variant="bodyLg" as="h6" tone="subdued" fontWeight="regular" >Only you and other staff can see comments.</Text>
                    </div>

                    {/* comments of customer */}
                    {
                        activityLog?.length > 0 && activityLog?.map((message, index) => {
                            return (
                                <div className='comment_block' key={index}>
                                    <div className='comment_date'>
                                        <Text variant="bodyLg" as="h6" tone="subdued" fontWeight="regular" >{message?.create}</Text>
                                    </div>
                                    <div className={`${message?.user_type !== "user" ? 'admin_comments' : 'customer_comments'} time_comments`}>

                                        {/* pic of admin */}
                                        {
                                            message?.user_type !== "user" &&
                                            <div className='admin_avatar'>
                                                <Avatar customer />
                                            </div>
                                        }

                                        {message?.user_type === 'System' ? (
                                            <div className='comment_msg'>
                                                <Text variant="headingMd" as="h6" tone="error" fontWeight="regular">{message?.message}</Text>
                                            </div>
                                        ) : message?.user_type === 'user' ? (
                                            <div className='comment_msg'>
                                                <Text id='purple_text' variant="headingMd" as="h6" tone="error" fontWeight="regular">Merchant: {message?.message}</Text>
                                            </div>
                                        ) : (
                                            <div className='comment_msg'>
                                                <Text id='black_text' variant="headingMd" as="h6" tone="neutral" fontWeight="regular">{ message?.message}</Text>
                                            </div>
                                        )}
                                        <Text  ext variant="bodyLg" as="h6" tone="subdued" fontWeight="regular" >{message?.time}</Text>
                                    </div>
                                </div>
                            );
                        })
                    }
                </div>
            </div>

        </div>
    );
}
