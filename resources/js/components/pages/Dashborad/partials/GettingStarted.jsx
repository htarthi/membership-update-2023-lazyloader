import { Text, Popover, ActionList, Button, Link, SkeletonBodyText, Modal } from '@shopify/polaris'
import Installation from '../../../../../images/new_free_installation.jpg'
import HelpCenter from '../../../../../images/new_help_center.jpg'
import GettingStart from '../../../../../images/new_getting_started.jpg'
import Dot from '../../../../../images/horizontal-dots_minor.svg'
import { React, useState, useCallback, useEffect } from 'react'

const GettingStarted = () => {
    const [startActive, setStartActive] = useState(false);
    const [openModal, setopenModal] = useState(true);
    const toggleStart = useCallback(
        () => setStartActive((startActive) => !startActive),
        [],
    );

    const clickActionButton = useCallback((e, url) => {
        e.stopPropagation();
        window.open(url, "_blank", "noreferrer");

    });

    const activator = (
        <Button onClick={toggleStart} disclosure>
            <img src={Dot} alt="Dot" />

        </Button>
    );

    useEffect(() => {
        const favDialog = document.getElementById("favDialog");
        const openDialog = document.querySelector('.mainWrap');
        openDialog.addEventListener("click", (event) => {
            event.preventDefault(); // Prevent default form submission behavior
            favDialog.close(); // Close the dialog
            // Access the specific iframe element using querySelector
            const iframe = document.querySelector(".yt_player_iframe");
            // Check if the iframe exists before trying to pause the video
            if (iframe) {
                iframe.src = "https://www.youtube.com/embed/HDUBmkKi0Hg?loop=1"

            }
        });
    }, []);

    return (
        <div className='startCardWrap' >
            <div className='startCard'>
                <div className='topImg'>
                    <Link onClick={() => favDialog.showModal()}>
                        <img src={GettingStart} alt="GettingStart" /></Link>
                </div>
                <div className='bottomSec'>
                    <div className='headWrap'>
                    </div>
                    <Text variant="bodyLg" as="h6" fontWeight='regular'>
                        Learn how to quickly setup the most common Simplee Memberships settings on your store
                    </Text>
                    <Button onClick={() => favDialog.showModal()}>
                        <Link url="#">Watch Video</Link>
                    </Button>
                </div>
            </div>
            <div className='startCard'>
                <div className='topImg'>
                    <img src={Installation} alt="Installation" />
                </div>
                <div className='bottomSec'>
                    <div className='headWrap'>

                        {/* <Popover
                            active={popoverActive}
                            activator={activator}
                            autofocusTarget="first-node"
                            onClose={togglePopoverActive}
                        >
                            <ActionList
                                actionRole="menuitem"
                                items={[{ content: 'Import' }, { content: 'Export' }]}
                            />
                        </Popover> */}

                    </div>

                    <Text variant="bodyLg" as="h6" fontWeight='regular'>
                        Let us know how you’d like to use Simplee Memberships, and our team will configure everything for you.
                    </Text>
                    <Button onClick={(val) => clickActionButton(val, "https://admin233798.typeform.com/to/JPKoivrn")}>
                        Request Installation
                        {/* <Link url="https://admin233798.typeform.com/to/JPKoivrn"  target='__blank'>Request Installation</Link> */}
                    </Button>
                </div>
            </div>
            <div className='startCard'>
                <div className='topImg'>
                    <img src={HelpCenter} alt="Installation" />
                </div>
                <div className='bottomSec'>
                    <div className='headWrap'>
                        {/* <Popover
                            active={popoverActive}
                            activator={activator}
                            autofocusTarget="first-node"
                            onClose={togglePopoverActive}
                        >
                            <ActionList
                                actionRole="menuitem"
                                items={[{ content: 'Import' }, { content: 'Export' }]}
                            />
                        </Popover> */}

                    </div>
                    <Text variant="bodyLg" as="h6" fontWeight='regular'>
                        Explore our help center to find useful guides and common questions and answers
                    </Text>
                    <Button onClick={(val) => clickActionButton(val, "https://support.simplee.best")}>
                        Launch help centre
                    </Button>
                </div>
            </div>
            <dialog id="favDialog">
                <iframe class="yt_player_iframe" style={{ minHeight: "400px", minWidth: "100%" }}
                    src="https://www.youtube.com/embed/tgbNymZ7vqY&loop=1"
                    title="YouTube video player"
                    frameborder="0"
                ></iframe>
            </dialog>
        </div>
    )
}

export default GettingStarted
