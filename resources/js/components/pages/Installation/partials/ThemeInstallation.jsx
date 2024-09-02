import {
    Banner,
    CalloutCard,
    Card,
    Select,
    Text,
    Button,
} from "@shopify/polaris";
import React from "react";
import { useState, useCallback, useEffect } from "react";
import EnableHiddenContent from "../../../../../images/enable-hidden-content.png";
import EnableAppEmbed from "../../../../../images/enable-app-embed.png";
import RequestInstallation from "../../../../../images/request-installation.png";
import { useDispatch } from "react-redux";
import {
    getThemes,
    enableHiddenContent,
} from "../../../../data/features/installation/installationAction";
import { useSelector } from "react-redux";
import InstallationSkeleton from "./InstallationSkeleton";

function ThemeInstallation() {
    const dispatch = useDispatch();
    const themes$ = useSelector((state) => state.installation?.data?.themes);
    const installation$ = useSelector((state) => state.installation?.data);
    const isLoading$ = useSelector((state) => state.installation?.isLoading);
    const isEnableContent$ = useSelector(
        (state) => state.installation?.isEnableContent,
    );

    const [selected, setSelected] = useState("");
    const [selectedTheme, setSelectedTheme] = useState(themes$[0]);

    useEffect(() => {
        setSelectedTheme(themes$[0]);
        themes$?.length > 0
            ? themes$?.map((item) => {
                  if (item?.role === "main") {
                      setSelected(`${item?.id}`);
                      setSelectedTheme(item);
                  }
              })
            : "";
    }, [isLoading$]);

    const eligibleForSubscriptions$ = useSelector(
        (state) => state.installation?.data?.eligibleForSubscriptions,
    );

    // Select Theme event..
    const handleSelectChange = useCallback(
        (value) => {
            setSelected(value);
            setSelectedTheme(themes$.find((theme) => theme.id == value));
        },
        [selected, selectedTheme],
    );

    // themes option list..
    const options =
        themes$?.length > 0
            ? themes$?.map((item) => {
                  return {
                      label: `${item?.name} ${item?.role === "main" ? "(published)" : ""}`,
                      value: `${item?.id}`,
                  };
              })
            : "";

    // enable hidden content event..
    function enableHiddenContentHandler() {
        dispatch(enableHiddenContent({ data: selectedTheme }));
    }

    // API call of Themes..
    useEffect(() => {
        dispatch(getThemes());
    }, []);

    return (
        <>
            {!isLoading$ ? (
                <>
                    {!eligibleForSubscriptions$ && (
                        <div className="mb-20">
                            <Banner
                                title={<Text as="h1" variant='bodyMd' fontWeight='medium'>You’re Not Ready For Memberships Yet</Text>}
                                action={{
                                    content: "Review Requirements",
                                    url: "https://support.simplee.best/en/articles/4735846-can-i-use-simplee-memberships",
                                    target: "_blank",
                                }}
                                tone="critical"
                            >
                                <p>
                                    It looks like your store can't use this app
                                    yet. This usually means you're not using a
                                    gateway which is approved by Shopify for
                                    recurring payments.
                                </p>
                            </Banner>
                        </div>
                    )}

                    <div className="mb-20">
                        <Card>
                            <div>
                                <Text as="h6" variant="headingMd" fontWeight='bold'>
                                    Theme Installation
                                </Text>

                                <div className="mt-9">
                                    <Text as="p" variant="bodyMd">
                                        Installing Simplee Memberships is easy!
                                        Choose your theme, click the "Enable App
                                        Embed" button, choose your widget style
                                        and colors, then save - that's it! If
                                        you will be hiding products or content
                                        from members, please also run the
                                        "Enable Hidden Content" button for the
                                        selected theme.
                                    </Text>
                                </div>
                            </div>
                            <div className="my-16">
                                <Select
                                    value={selected}
                                    label="Select Theme"
                                    options={options}
                                    onChange={handleSelectChange}
                                />
                            </div>
                            <CalloutCard
                                title={<Text fontWeight='semibold'>Step 1: All Installations</Text>}
                                illustration={EnableAppEmbed}
                                primaryAction={{
                                    content: "Enable App Embed",
                                    url: `https://${installation$?.name}/admin/themes/${selectedTheme?.id}/editor?context=apps&activateAppId=${import.meta.env.VITE_SHOPIFY_APP_EMBEDED_ID}%2Fapp-block`,
                                    // url: `https://nikunj-membership-dev-ruchita.myshopify.com/admin/themes/${selectedTheme?.id}/editor?context=apps&activateAppId=98dbd34c-3b30-4548-bf99-4be96b3cbecb%2Fapp-block`,
                                    target: "_blank",
                                }}
                            >
                                <p>
                                    Click the button to open your theme
                                    customizer. Expand the Simplee Memberships
                                    app embed, choose your widget style and
                                    other settings, then save the theme.
                                </p>
                            </CalloutCard>
                            <div className="enable_hidden_content_wrap mt-16">
                                <CalloutCard
                                    title={<Text fontWeight='semibold'>Step 2: Hidden Content</Text>}
                                    illustration={EnableHiddenContent}
                                    primaryAction={{
                                        content: "",
                                    }}
                                >
                                    <p>
                                        Only complete this step if you are
                                        restricting products, collections,
                                        pages, or blogs from your members. It
                                        will make a minor adjustment to your
                                        theme.liquid file
                                    </p>

                                    <Button
                                        onClick={enableHiddenContentHandler}
                                        loading={
                                            isEnableContent$ ? true : false
                                        }
                                    >
                                        Enable Hidden Content
                                    </Button>
                                </CalloutCard>
                            </div>
                        </Card>
                    </div>
                    <CalloutCard
                        title="Want Some Help Installing Our App?"
                        illustration={RequestInstallation}
                        primaryAction={{
                            content: "Request Installation",
                            url: "https://admin233798.typeform.com/to/JPKoivrn?typeform-source=membershipsdev.crawlapps.com",
                            target: "_blank",
                        }}
                    >
                        <p>
                            Whether this is a new installation, or you’ve
                            decided to use a new Shopify theme, we can help!
                        </p>
                    </CalloutCard>
                </>
            ) : (
                <InstallationSkeleton />
            )}
        </>
    );
}

export default ThemeInstallation;
