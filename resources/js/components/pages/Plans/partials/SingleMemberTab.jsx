import { Checkbox, Icon, TextField } from '@shopify/polaris'
import React, {useCallback, useState} from "react";
import CommanLabel from '../../../GlobalPartials/CommanInputLabel/CommanLabel';
import { CheckCircleIcon } from '@shopify/polaris-icons';
import { useDispatch } from 'react-redux';
import { singleMembers } from '../../../../data/features/plans/plansSlice';
import { useSelector } from 'react-redux';

export default function SingleMemberTab({planGName}) {

    const singleMembers$ = useSelector((state) => state.plans?.single_members);
    const errors$ = useSelector((state) => state?.plans?.errors);
    const dispatch = useDispatch();

    // handle change
    const handleSingleMemberInput = useCallback((value, name) => {
        const data = {
            [name]: value
        }
        dispatch(singleMembers(data));
    }, [singleMembers$])

    return (
        <>
            {/* single member */}
            <div className="member_tab">
                {/* FirstName & LastName */}
                <div className="input_two_fields_wrap">
                    <div className="input_field_wrap">
                        <TextField
                            label="First name"
                            value={singleMembers$.firstname}
                            onChange={(val) =>
                                handleSingleMemberInput(val, "firstname")
                            }
                            autoComplete="off"
                            error={!singleMembers$.firstname ? errors$?.length > 0 ? errors$[0][`data.firstname`] : '' : ''}
                        />
                    </div>
                    <div className="input_field_wrap">
                        <TextField
                            label='Last name'
                            value={singleMembers$.lastname}
                            onChange={(val) =>
                                handleSingleMemberInput(val, "lastname")
                            }
                            autoComplete="off"
                            error={!singleMembers$.lastname ? errors$?.length > 0 ? errors$[0][`data.lastname`] : '' : ''}
                        />
                    </div>
                </div>

                {/* Email Address */}
                <div className="input_field_wrap ms-margin-top">
                    <TextField
                        label="Email"
                        value={singleMembers$.email}
                        onChange={(val) =>
                            handleSingleMemberInput(val, "email")
                        }
                        autoComplete="off"
                        error={!singleMembers$.email ? errors$?.length > 0 ? errors$[0][`data.email`] : '' : ''}
                    />
                </div>

                {/* checkbox */}
                <div className="single_member_checkboxes ms-margin-top">
                    <div className="checkbox_block">
                        <Checkbox
                            label={
                                <CommanLabel
                                    label={
                                        "Send customers invitation to create an account if they don’t have one"
                                    }
                                    content={""}
                                    isTooltip={true}
                                />
                            }
                            checked={singleMembers$.is_sendinvitation}
                            onChange={(val) =>
                                handleSingleMemberInput(
                                    val,
                                    "is_sendinvitation"
                                )
                            }
                        />
                    </div>
                    <div className="checkbox_block ms-margin-top-ten">
                        <Checkbox
                            label={
                                <CommanLabel
                                    label={
                                        "Send customers new membership email (from our app)"
                                    }
                                    content={""}
                                    isTooltip={true}
                                />
                            }
                            checked={
                                singleMembers$.is_sendnewmembershipmail
                            }
                            onChange={(val) =>
                                handleSingleMemberInput(
                                    val,
                                    "is_sendnewmembershipmail"
                                )
                            }
                        />
                    </div>
                </div>

                {/* note */}
                <div className="single_membership_note ms-margin-top">
                    <div className="note_icon_block">
                        <Icon
                            source={CheckCircleIcon}
                            color="highlight"
                        />
                    </div>

                    {/* list */}
                    <ul className="note_list_block">
                        <li>
                            This member will be added to {" "}
                            the <b>{planGName}</b> plan.
                        </li>
                        <li>
                            These members will not be charged for their
                            memberships now or in the future.
                        </li>
                    </ul>
                </div>
            </div>
        </>
    );
}
