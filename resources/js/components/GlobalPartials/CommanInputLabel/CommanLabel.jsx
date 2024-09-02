import { Icon, Text, Tooltip } from "@shopify/polaris";
import React from "react";
import { QuestionCircleIcon } from "@shopify/polaris-icons";

export default function CommanLabel({ label, content, isTooltip=false }) {

    return (
        <div className="label_block">
            <Text
                variant="bodyLg"
                as="h6"
                fontWeight="regular"
                
            >
                {label}
                {
                    isTooltip === true ?
                    <Tooltip content={content}>
                        <Icon source={QuestionCircleIcon} color="subdued" />
                    </Tooltip>
                    :
                    <>
                        {
                            content &&
                            <div className="tooltip_wrap">
                                <Icon source={QuestionCircleIcon} color="subdued" />
                                    <span className="tooltip_content">
                                        {content}
                                    </span>
                            </div>
                        }
                    </>
                }
            </Text>
        </div>
    );
}
