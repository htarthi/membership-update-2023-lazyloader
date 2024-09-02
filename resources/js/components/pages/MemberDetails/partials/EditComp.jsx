import { Button, Text } from '@shopify/polaris'
import React from 'react'

export default function EditComp({title, editHandleEvent}) {
  return (
    <div className='edit_header_block'>
        <Text variant="headingMd" as="h6"  fontWeight='regular' tone='base'>{title}</Text>
        <Button  onClick={editHandleEvent} variant="plain">Edit</Button>
    </div>
  );
}
