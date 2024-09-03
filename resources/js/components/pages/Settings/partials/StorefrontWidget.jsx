import React, {useState, useCallback} from 'react'
import ColorPickerComp from "./ColorPickerComp";
import { TextField , Text } from '@shopify/polaris';
import CommanLabel from '../../../GlobalPartials/CommanInputLabel/CommanLabel';
import { useDispatch, useSelector } from 'react-redux';
import { updateSetting , setChanged } from '../../../../data/features/settings/settingsDataSlice';

export default function StorefrontWidget() {
    const settings$ = useSelector((state) => state?.settings?.data?.data?.setting);
    const dispatch = useDispatch();

    const handleWidgetChange = useCallback((value, name) => {
        dispatch(updateSetting({[name]: value}));

        
        dispatch(setChanged({['isChangeData']: true}));
    }, [settings$])


  return (
    <>
        {/* colors fields */}
        {/* <Text variant="bodyLg" as="h6" fontWeight="regular" >What heading should be displayed above the storefront widget?</Text> */}
{/*
        <div className="input_two_fields_wrap">
            <ColorPickerComp title={"Checkmark Background"} content={""} handleColorChange={handleWidgetChange} color={settings$?.widget_active_bg} name={'widget_active_bg'} />

            <ColorPickerComp title={"Checkmark"} content={""} handleColorChange={handleWidgetChange} color={settings$?.widget_active_text} name={'widget_active_text'} />
        </div> */}

        {/* Email address where notifications will be sent */}
        <div className="sent_notification_input_field ">
            <TextField
                label={ <CommanLabel label={"Widget heading"} content={''} /> }
                value={settings$?.widget_heading_text}
                onChange={(value) =>
                    handleWidgetChange(value, 'widget_heading_text')
                }
                autoComplete="off"
            />
        </div>


    </>
  )
}
