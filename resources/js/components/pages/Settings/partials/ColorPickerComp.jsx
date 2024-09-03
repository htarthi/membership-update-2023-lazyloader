import React, { useState } from 'react'
import { Icon, Popover, TextField } from '@shopify/polaris'
import { ColorIcon } from '@shopify/polaris-icons';
import CommanLabel from '../../../GlobalPartials/CommanInputLabel/CommanLabel';
import { SketchPicker } from 'react-color';

export default function ColorPickerComp({title, content, handleColorChange, color, name}) {
    // popver state
    const [visible, setVisible] = useState(false);

    return (
        <>
            <Popover
                active={visible}
                preferredAlignment="left"
                preferInputActivator={false}
                preferredPosition="below"
                preventCloseOnChildOverlayClick
                onClose={() => setVisible(false)}
                activator={
                    <div className="input_fields_wrap">
                        <TextField
                            role="combobox"
                            label={ <CommanLabel label={title} content={content} /> }
                            suffix={
                                <Icon source={ColorIcon} color="base" />
                            }
                            prefix={<div className='set_color_bg' style={{backgroundColor: `${color}`}} ></div>}
                            value={color}
                            onFocus={() => setVisible(true)}
                            onChange={(value) => console.log(value)}
                            autoComplete="off"
                        />
                    </div>
                }
            >
                <SketchPicker
                    color={color}
                    onChange={(val) => handleColorChange(val.hex, name)}
                />
            </Popover>
        </>
    );
}
