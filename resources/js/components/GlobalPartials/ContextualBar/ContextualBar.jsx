import React from 'react'
import {ContextualSaveBar, Frame} from '@shopify/polaris';

export default function ContextualBar({setShowContextualBar, saveContextHandleChange}) {
  return (
    <div className='context_save_bar_wrap'>
        <Frame>
            <ContextualSaveBar
            alignContentFlush
            message="Unsaved changes"
            saveAction={{
                onAction: () => saveContextHandleChange(),
            }}
            discardAction={{
                onAction: () => setShowContextualBar(false),
            }}
            />
        </Frame>
    </div>
  )
}
