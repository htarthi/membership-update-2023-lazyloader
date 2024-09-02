import React, { useEffect, useCallback, useState } from 'react'
import Editor from '@monaco-editor/react';
import { ArrowLeftIcon } from "@shopify/polaris-icons";
import { useDispatch } from 'react-redux';
import { getThemeFiles, updateThemeFiles } from '../../../../data/features/settings/settingAction';
import { useSelector } from 'react-redux';
import InstallationSkeleton from '../../Installation/partials/InstallationSkeleton';
import { Button, Icon, Page , Text } from '@shopify/polaris';
import { useNavigate, NavLink } from 'react-router-dom';
import { updateEditCodeData } from '../../../../data/features/settings/settingsDataSlice';

export default function EditCode() {

    const editcode$ = useSelector((state) => state?.settings?.data?.data?.portal?.editcode);
    const isloading$ = useSelector((state) => state?.settings?.isLoading)
    const [currentFile, setcurrentFile] = useState('liquid')
    const dispatch = useDispatch();
    const navigate = useNavigate();

    useEffect(() => {
        dispatch(getThemeFiles());
    }, [])

    const filename = [{
        name: "liquid"
    }, {
        name: "css",
    }, { name: "js" }]

    const handleFileChnage = useCallback((value) => {
        setcurrentFile(value)
    }, [filename])

    const handleChangeEvent = useCallback((value, file) => {
        const updateddata = {
            [file]: value,
        }
        dispatch(updateEditCodeData(updateddata));
    }, [editcode$])

    const handleSaveButton = useCallback(() => {
        dispatch(updateThemeFiles(editcode$.portal))
    },)

    const handleBackEvent = useCallback(() => {
        navigate('/settings')
    }, [])

    return <>
        {
            <Page fullWidth>
                <div className="top-head">
                    <div className='navlink-warp'>
                        <div className="members_navigate_wrap">
                            <a onClick={() => handleBackEvent()}>
                                <NavLink className="back_arrow_wrap"  >
                                    <Icon source={ArrowLeftIcon} tone="base" />
                                </NavLink>
                            </a>
                        </div>
                    </div>
                    <div style={{ marginTop : '10px'}}>
                        <Text variant='headingXl' fontWeight='medium' as='h2'>Member portal Edit Code</Text>
                    </div>
                </div>
                <div className='editcode-wrap main'>
                    <div className='left-btn'>
                        {filename.map((data, key) => {
                            return <ul>
                                <li className={`${currentFile === data.name ? "active" : ""}`} onClick={(value) => handleFileChnage(data.name)} value={data.name} key={key}>portal.{data.name}</li>
                            </ul>

                        })}
                    </div>
                    <div className="plans_list_wrap setting_list_wrap " >
                        <h2>portal.{currentFile}</h2>
                        {
                            !isloading$ ?
                                <Editor height="70vh" onChange={(e) => handleChangeEvent(e, currentFile)} language={currentFile == "js" ? "javascript" : currentFile} value={editcode$?.portal?.[currentFile]} /> : <InstallationSkeleton />
                        }
                    </div>
                </div>
                {
                    !isloading$ ?
                        <div className="buttons">
                            <Button id="tn-save"  onClick={(e) => handleSaveButton()} variant="primary" >Save Changes</Button>

                        </div> : ""
                }
            </Page>

        }
    </>;
}
