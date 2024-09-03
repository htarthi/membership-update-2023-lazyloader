import { Modal, Tabs ,Text} from "@shopify/polaris";
import React, { useState, useCallback, useEffect } from "react";
import SingleMemberTab from "./SingleMemberTab";
import ImportMemberTab from "./ImportMemberTab";
import { useDispatch } from "react-redux";
import { useSelector } from "react-redux";
import { merchantMigrate } from "../../../../data/features/plans/planAction";
import { ismerchantMigrate, resteSingleMembers  } from "../../../../data/features/plans/plansSlice";


export default function AddMembersModal({ setActive, active, ssPlanGroupId, planGName,plangroupvariantindex,plangroupindex,membership_count}) {

    const dispatch = useDispatch();


    const singleMembers$ = useSelector((state) => state.plans?.single_members);
    const plans$ = useSelector((state) => state?.plans?.isAddMemberSuccess);
    const plansData$ = useSelector((state) => state?.plans?.data?.planG);
    const shop$ = useSelector((state) => state.plans?.data?.shop);

    // selected tabs
    const [selected, setSelected] = useState(0);
    const [checkerror, setCheckError] = useState(false);

    useEffect(() => {
        if (plans$ == true) {
            const indicies = [];
            plansData$.map((val) => {
                if (val['shopify_product_id'] == plangroupvariantindex) {
                    indicies.push(val);
                }
            })

            const freePlan = shop$?.is_membership_expired;
            const isfreemember = shop$?.memberCount;
            const isconmem = shop$?.contractCount;
            const isfreeMem = shop$?.freeMem;

            plangroupvariantindex  =  plansData$.indexOf(indicies[plangroupindex])

            dispatch(ismerchantMigrate({plangroupvariantindex,plangroupindex,membership_count,freePlan, isfreemember ,isconmem , isfreeMem}));
            dispatch(resteSingleMembers());
            setActive(false);
        }
    }, [plans$]);

    const handleTabChange = useCallback(
        (selectedTabIndex) => {
            setSelected(selectedTabIndex)
            setCheckError(false);
        }, [singleMembers$]);

    // tabs array
    const tabs = [
        {
            id: "1",
            content: "Single Member",
            panelID: "single-members-content-1",
        },
        {
            id: "2",
            content: "Bulk Members",
            panelID: "import-members-content-1",
        },
    ];
    // modal cancle event
    const handleCancle = useCallback(() => {
        dispatch(resteSingleMembers());
        setActive(false);
        setCheckError(false);
    }, [singleMembers$]);

    const addMembers = useCallback(() => {
        const data = {
            ss_plan_group_id: ssPlanGroupId,
            firstname: singleMembers$?.firstname,
            lastname: singleMembers$?.lastname,
            fileName: singleMembers$?.fileName,
            email: singleMembers$?.email,
            file: singleMembers$?.file,
            is_sendinvitation: singleMembers$?.is_sendinvitation,
            is_sendnewmembershipmail: singleMembers$?.is_sendnewmembershipmail,
        };
        if (selected === 0) {
            dispatch(merchantMigrate({ data, importType: 0 }));

        } else {
            setCheckError(true);
            if (singleMembers$?.fileName) {
                let formData = new FormData();
                formData.append('importType', 1);
                formData.append('file', singleMembers$?.file);
                formData.append('form', JSON.stringify(data));

                dispatch(merchantMigrate(formData));
                setCheckError(false);
            }
        }
    }, [singleMembers$, selected, checkerror]);

    return (
        <div className="add_members_modal">
            <Modal
                open={active}
                onClose={handleCancle}
                title={<Text>Create Non-Billable Members</Text>}
                primaryAction={{
                    content: "Add Members",
                    // tone : 'success',
                    onAction: addMembers,
                }}
                secondaryActions={[
                    {
                        content: "Cancel",
                        onAction: handleCancle,
                    },
                ]}
            >
                <Modal.Section >
                    <div className="single_import_members_block">
                        {/* tabination - single & import members */}
                        <div className="tabination_single_import_members">
                            <Tabs
                                tabs={tabs}
                                selected={selected}
                                onSelect={handleTabChange}
                                // fitted='true'
                                
                            ></Tabs>
                        </div>

                        {selected === 0 ? (
                            <>
                                {/* Single Member */}
                                <SingleMemberTab planGName={planGName} />
                            </>
                        ) : (
                            <>
                                {/* Import Member */}
                                <ImportMemberTab  planGName={planGName} checkerror={checkerror}  />
                            </>
                        )}
                    </div>
                </Modal.Section>
            </Modal>
        </div>
    );
}
