import { Button, Text } from '@shopify/polaris'
import React, { useCallback } from 'react'
import { useDispatch } from 'react-redux';
import { useSelector } from 'react-redux';
import { customerCancelMembership, getThemeFiles, gettranslationData } from "../../../../data/features/settings/settingAction"
import { updatePortal, updateSetting ,setChanged } from '../../../../data/features/settings/settingsDataSlice';
import { useNavigate } from 'react-router-dom';

export default function MemberPortal() {

    const navigate = useNavigate();
    const portal$ = useSelector((state) => state?.settings?.data?.data?.setting);
    const dispatch = useDispatch();

    const portalHandleChange = useCallback(() => {
        dispatch(updateSetting({ portal_can_cancel: portal$?.portal_can_cancel === 0 ? 1 : 0 }))
        dispatch(customerCancelMembership({ portal_can_cancel: portal$?.portal_can_cancel === 0 ? 1 : 0 }))
    }, [portal$])

    const handleTranslations = useCallback(() => {
        // dispatch(gettranslationData())

        navigate('/settings/transalations');
    }, [portal$])

    const habndleEditCodeEvent = useCallback(()=>{
        // dispatch(getThemeFiles());

        navigate('/settings/editcode')
    },[])

    return (
        <div className='member_portal_block'>
    {/* <Text variant='bodyLg' as="h6" fontWeight='medium' >Adjust some key member portal settings, and access the entire code that’s loaded for members.</Text> */}

            <div className='member_portal_col '>
                <Text
                    variant="bodyLg"
                    as="h6"
                    fontWeight="regular"
                >
                    Customers <b style={{ fontWeight: '600' }}>can cancel</b> their memberships
                </Text>
                {/* polaris-migrator: Unable to migrate the following expression. Please upgrade manually. */}
                <Button primary={portal$?.portal_can_cancel === 0} onClick={portalHandleChange}>{portal$?.portal_can_cancel === 0 ? 'Enable' : 'Disable'}</Button>
            </div>
            <div className='member_portal_col ms-margin-top'>
                <Text
                    variant="bodyLg"
                    as="h6"
                    fontWeight="regular"
                >
                    Update the text on the member portal
                </Text>
                <Button onClick={() => handleTranslations()} >Translations</Button>
            </div>
            <div className='member_portal_col ms-margin-top'>
                <Text
                    variant="bodyLg"
                    as="h6"
                    fontWeight="regular"
                >
                    Update the member portal HTML and CSS
                </Text>
                <Button onClick={()=>habndleEditCodeEvent()} >Edit Code</Button>
            </div>
        </div>
    );
}
