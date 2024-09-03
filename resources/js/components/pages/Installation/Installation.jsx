import React from "react";
import ThemeInstallation from "./partials/ThemeInstallation";
import SubHeader from "../../GlobalPartials/SubHeader/SubHeader";
import { Page } from "@shopify/polaris";

function Installation() {

    return (
        <>
            <SubHeader
                title={"Installation"}
                secondButtonState={false}
                needHelp={false}
            />

            <div className="installation_wrap">
                    <Page fullWidth>
                        <div className="simplee_membership_container">
                            <ThemeInstallation />
                        </div>
                    </Page>
            </div>
        </>
    );
}

export default Installation;
