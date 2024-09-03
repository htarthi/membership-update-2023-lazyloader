import { Icon, Text, Tooltip } from '@shopify/polaris'
import React from 'react'
import { QuestionCircleIcon } from '@shopify/polaris-icons';

export default function TooltipComp({title, content}) {
  return (
    <>
        {/* tooltip */}
        <div className='products_tooltip'>
            <Text variant="bodyLg" as="h6" fontWeight='semiBold' >{title}</Text>

            <div className='tooltip_block'>
                <Tooltip content={content}>
                    <Icon source={QuestionCircleIcon} color="base" />
                </Tooltip>
            </div>
        </div>
    </>
  )
}
