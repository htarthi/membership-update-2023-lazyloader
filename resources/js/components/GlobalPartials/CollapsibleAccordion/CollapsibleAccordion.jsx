import { Badge, Collapsible, Icon, Text } from "@shopify/polaris";
import React from "react";
import { ChevronDownIcon, ChevronUpIcon, AlertDiamondIcon } from "@shopify/polaris-icons";

export default function CollapsibleAccordion({ title, handleToggle, id, open, body, badge, status, showIcon = true }) {


    return (
        <div className="accordion_wrap">
            <div className="accordion_row">
                <button
                    className="accordion_col_wrap button_style"
                    onClick={() => handleToggle(id)}
                    aria-expanded={open === id}
                    aria-controls="basic-collapsible"
                >
                    <div className="title_badge_block">
                        <Text variant="bodyLg" as="h6" fontWeight="semiBold">{title}</Text>
                        {
                            badge !== undefined & badge != '' ?
                                <Badge
                                    tone={status}
                                    progress={status === "success" ? 'complete' : 'incomplete'}
                                >
                                    {badge}
                                </Badge> : ''
                        }

                        {
                            id == 0 && badge == "No Lengths" && status == "critical" &&
                            <span className='error_wrap' id="icon_len">
                                <Icon source={AlertDiamondIcon} color="critical" className='icon_len' />
                                At least one length is required
                            </span>
                        }

                    </div>

                    {
                        showIcon ? <div className="chevron_down_icon">
                            <Icon
                                source={
                                    open === id ? ChevronUpIcon : ChevronDownIcon
                                }
                                tone="base"
                            />
                        </div> : ''
                    }

                </button>

                {/* collapsible */}
                <Collapsible
                    open={open === id}
                    id="basic-collapsible"
                    transition={{
                        duration: "500ms",
                        timingFunction: "ease-in-out",
                    }}
                    expandOnPrint
                >
                    <div className="collapsible_block">{body}</div>
                </Collapsible>
            </div>
        </div>
    );
}
