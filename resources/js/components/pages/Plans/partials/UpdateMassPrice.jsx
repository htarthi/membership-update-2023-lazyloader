import { Link, Modal, TextField ,Text } from "@shopify/polaris";
import React, {useCallback, useState} from "react";
import { useDispatch } from "react-redux";
import { updatePriceForSC } from "../../../../data/features/plans/planAction";

export default function UpdateMassPrice({massPriceData, setMassPriceData}) {

    const dispatch = useDispatch();

    const fileData = {
        fileName: "",
        file: {}
    }
    const [massPrice, setMassPrice] = useState(fileData);
    const [checkError, setCheckError] = useState(false);

    // file handle change..
    const handleImportFile = useCallback((file) => {
        setMassPrice({
            ...massPrice,
            fileName: file?.name,
            file: file
        })
    }, [massPrice])

    // API call of Update mass price..
    const UpdateMassPrice = useCallback(() => {
        let formData = new FormData();
        formData.append('file', massPrice?.file);

        if(massPrice?.fileName){
            dispatch(updatePriceForSC(formData));
            setCheckError(false);
        }else{
            setCheckError(true);
        }
    }, [massPrice, checkError])

    // modal close..
    const cancle = useCallback(() => {
        setMassPriceData({
            ...massPriceData,
            showMassPrice: false
        });
        setMassPrice(fileData);
    }, [])

    return (
        <Modal
            open={massPriceData?.showMassPrice}
            onClose={() => cancle()}
            title={<Text>{`Update the mass price for members of this selling plan: ${massPriceData?.planName}`}</Text>}
            primaryAction={{
                content: "Update The Mass Price",
                onAction: UpdateMassPrice,
                // tone : 'success',
            }}
            secondaryActions={[
                {
                    content: "Cancel",
                    onAction: () => cancle(),
                },
            ]}
        >
            <Modal.Section>
                <div className="member_tab">
                    <p>
                        To import many members, you will need a CSV file with
                        the first name, last name, and email address of each
                        member. These members will be given member access, but
                        will not be charged. You can download this{" "}
                        <a
                            className="link"
                            href="/CSVs/PriceUpdate.csv"
                        >
                            CSV template
                        </a>
                        , or make a copy of this{" "}
                        <Link
                            url="https://docs.google.com/spreadsheets/d/1Labbw5xeTS-faAjXWbCmdusVee2oqMM0e7pgBl9Loqc/edit?usp=sharing"
                            target="_blank"
                        >
                            Google Sheets document
                        </Link>
                        , then download it as a CSV.
                    </p>

                    {/* select file */}
                    <div className="select_file_wrap">
                        <div className="text_field_wrap">
                            <TextField
                                value={massPrice?.fileName}
                                autoComplete="off"
                                readOnly
                                error={!massPrice?.fileName && checkError ? 'Requierd' : ''}
                            />
                        </div>
                        <button className="select_file_button">
                            Select File
                            <input
                                id="select_file_field"
                                type="file"
                                onChange={(e) => {handleImportFile(e.target.files[0]);
                                }}
                            />
                        </button>
                    </div>
                </div>
            </Modal.Section>
        </Modal>
    );
}
