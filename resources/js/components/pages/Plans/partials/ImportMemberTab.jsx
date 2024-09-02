import { Checkbox, Link, Text, TextField } from '@shopify/polaris'
import React, {useCallback} from 'react'
import CommanLabel from '../../../GlobalPartials/CommanInputLabel/CommanLabel';
import { useSelector } from 'react-redux';
import { useDispatch } from 'react-redux';
import { singleMembers } from '../../../../data/features/plans/plansSlice';

export default function ImportMemberTab({planGName,checkerror}) {

    const singleMembers$ = useSelector((state) => state.plans?.single_members);
    const errors$ = useSelector((state) => state.plans?.errors);
    const dispatch = useDispatch();

    // handle change
    const handleImportMember = useCallback((value, key) => {
        const data = {
            [key]: value,
        }
        dispatch(singleMembers(data));
    }, [singleMembers$])

    // file handle change
    const handleImportFile = useCallback((file) => {
        const data = {
            file: file
        }
        dispatch(singleMembers(data));
    }, [singleMembers$])

  return (
    <>
        {/* Import Member */}
        <div className='member_tab'>
            <p>
            To import many members, you will need a CSV file with the first name, last name, and
            email address of each member. These members will be given member access, but will not be charged. You
            can download this <a className='link' href="/CSVs/SimpleeMembershipsUpdate_MemberImport.csv">CSV template</a>, or make a copy
            of this <Link url="https://docs.google.com/spreadsheets/d/1Labbw5xeTS-faAjXWbCmdusVee2oqMM0e7pgBl9Loqc/edit?usp=sharing" target="_blank">Google Sheets document</Link>, then download it as a CSV.
            </p>

            {/* select file */}
            <div className='select_file_wrap'>
                <div className='text_field_wrap'>
                    <TextField
                        value={singleMembers$?.fileName}
                        autoComplete="off"
                        readOnly
                        error={!singleMembers$?.fileName && checkerror ? 'Import file required' : errors$?.length > 0 && errors$[0]?.fileError ? errors$[0]?.fileError : ''}
                    />
                </div>
                <button className='select_file_button'>
                    Select File
                    <input
                        id="select_file_field"
                        type='file'
                        onChange={(e) => {handleImportMember(e.target.files[0]?.name, 'fileName'), handleImportFile(e.target.files[0])}}
                    />
                </button>
            </div>

            {/* Adding member */}
            <div className='adding_member_wrap'>

                <Text as='h4'>
                    Members will be added to: {planGName}
                </Text>

                     {/* checkbox */}
                     <div className="single_member_checkboxes ms-margin-top">
                    <div className="checkbox_block">
                        <Checkbox
                            label={
                                <CommanLabel
                                    label={
                                        "Send customers invitation to create an account if they don’t have one"
                                    }
                                    content={''}
                                    isTooltip={true}
                                />
                            }
                            checked={singleMembers$?.is_sendinvitation}
                            onChange={(val) =>
                                handleImportMember(
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
                                    content={''}
                                    isTooltip={true}
                                />
                            }
                            checked={
                                singleMembers$?.is_sendnewmembershipmail
                            }
                            onChange={(val) =>
                                handleImportMember(
                                    val,
                                    "is_sendnewmembershipmail"
                                )
                            }
                        />
                    </div>
                </div>


            </div>

        </div>
    </>
  )
}
