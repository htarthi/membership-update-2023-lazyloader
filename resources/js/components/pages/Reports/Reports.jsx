import {
    Box, Button, DatePicker, Icon, OptionList, Page, Popover, Scrollable, Select, TextField, useBreakpoints, InlineGrid, InlineStack, BlockStack, Listbox,
    Link,
    AutoSelection,
    EmptySearchResult,
    Text,Modal,
    SkeletonBodyText
} from '@shopify/polaris'
import SubHeader from '../../GlobalPartials/SubHeader/SubHeader'
import React, { useEffect, useState, useRef ,useCallback,lazy,Suspense} from 'react'
import {
    CalendarIcon,
    ArrowRightIcon,
    CaretDownIcon
} from '@shopify/polaris-icons';
import ReportSekeleton from './partials/ReportSekeleton';

import { useDispatch } from 'react-redux';
import { useSelector } from 'react-redux';
import { getData } from '../../../data/features/reports/reportAction';
import { useLocation, useNavigate } from 'react-router-dom';
import instance from '../../shopify/instance';
import { toast } from 'react-toastify';
import { filterReset } from '../../../data/features/reports/reportSlice';

import OtherReports from './partials/OtherReports';
const UpcomingRenewals = lazy(() => {
    console.log("Loading CreditCardsRetries...");
    return import('./partials/UpcomingRenewals');
});
// Lazy load components
// const UpcomingRenewals = lazy(() => import('./partials/UpcomingRenewals'));
const BillingAttempts = lazy(() => import("./partials/BillingAttempts"));
const NewestMembers = lazy(() => import('./partials/NewestMembers'));
const RecentCancellation = lazy(() => import("./partials/RecentCancellation"));

function Reports() {

    const dispatch = useDispatch()
    const isLoading = useSelector((state) => state?.reports?.other_reports?.isLoading);
    const navigate = useNavigate()
    const [UpcomingRenewalsvisible, setUpcomingRenewalsvisible] = useState(false);
    const location = useLocation();

    const defaultFilter$ = useSelector((state) => state?.reports?.defaultFilter);
    const [active, setActive] = useState(false);
    const [email, setEmail] = useState("");
    const [checkerror, setCheckError] = useState(false);
    const plans$ = useSelector((state) => state.plans?.data);
    const shopDomain = useSelector((state) => state?.reports?.data?.other_reports?.shop?.id);

    useEffect(() => {
        dispatch(getData("last_7_days"));
    }, [])
    const { mdDown, lgUp } = useBreakpoints();
    const shouldShowMultiMonth = lgUp;
    const today = new Date(new Date().setHours(0, 0, 0, 0));
    const yesterday = new Date(
        new Date(new Date().setDate(today.getDate() - 1)).setHours(0, 0, 0, 0)
    );
    const oneYearAgo = new Date(today);
    oneYearAgo.setFullYear(oneYearAgo.getFullYear() - 1);
    const ranges = [
        {
            title: "Today",
            alias: "today",
            period: {
                since: today,
                until: today,
            },
        },
        {
            title: "Yesterday",
            alias: "yesterday",
            period: {
                since: yesterday,
                until: yesterday,
            },
        },
        {
            title: "Last 7 days",
            alias: "last_7_days",
            period: {
                since: new Date(
                    new Date(new Date().setDate(today.getDate() - 7)).setHours(0, 0, 0, 0)
                ),
                until: yesterday,
            },
        },
        {
            title: "Last month",
            alias: "last_30_days",
            period: {
                since: new Date(
                    new Date(new Date().setDate(today.getDate() - 30)).setHours(0, 0, 0, 0)
                ),
                until: yesterday,
            },
        },
        {
            title: "Last 3 month",
            alias: "last_90_days",
            period: {
                since: new Date(
                    new Date(new Date().setDate(today.getDate() - 90)).setHours(0, 0, 0, 0)
                ),
                until: yesterday,
            },
        },
        {
            title: "Last year",
            alias: "last_365_days",
            period: {
                since: oneYearAgo,
                until: yesterday,
            },
        },
    ];
    const [popoverActive, setPopoverActive] = useState(false);
    const [activeDateRange, setActiveDateRange] = useState(ranges[2]);
    const [inputValues, setInputValues] = useState({});
    const [{ month, year }, setDate] = useState({
        month: activeDateRange.period.since.getMonth(),
        year: activeDateRange.period.since.getFullYear(),
    });
    const datePickerRef = useRef(null);
    const VALID_YYYY_MM_DD_DATE_REGEX = /^\d{4}-\d{1,2}-\d{1,2}/;
    function isDate(date) {
        return !isNaN(new Date(date).getDate());
    }
    function isValidYearMonthDayDateString(date) {
        return VALID_YYYY_MM_DD_DATE_REGEX.test(date) && isDate(date);
    }
    function isValidDate(date) {
        return date.length === 10 && isValidYearMonthDayDateString(date);
    }
    function parseYearMonthDayDateString(input) {
        const [year, month, day] = input.split("-");
        return new Date(Number(year), Number(month) - 1, Number(day));
    }
    function formatDate(date) {
        const options = { month: 'long', day: 'numeric', year: 'numeric' };
        return new Date(date).toLocaleDateString('en-US', options);
    }
    function nodeContainsDescendant(rootNode, descendant) {
        if (rootNode === descendant) {
            return true;
        }
        let parent = descendant.parentNode;
        while (parent != null) {
            if (parent === rootNode) {
                return true;
            }
            parent = parent.parentNode;
        }
        return false;
    }
    function isNodeWithinPopover(node) {
        return datePickerRef?.current
            ? nodeContainsDescendant(datePickerRef.current, node)
            : false;
    }
    function handleStartInputValueChange(value) {
        setInputValues((prevState) => {
            return { ...prevState, since: value };
        });
        if (isValidDate(value)) {
            const newSince = parseYearMonthDayDateString(value);
            setActiveDateRange((prevState) => {
                const newPeriod =
                    prevState.period && newSince <= prevState.period.until
                        ? { since: newSince, until: prevState.period.until }
                        : { since: newSince, until: newSince };
                return {
                    ...prevState,
                    period: newPeriod,
                };
            });
        }
    }
    function handleEndInputValueChange(value) {
        setInputValues((prevState) => ({ ...prevState, until: value }));
        if (isValidDate(value)) {
            const newUntil = parseYearMonthDayDateString(value);
            setActiveDateRange((prevState) => {
                const newPeriod =
                    prevState.period && newUntil >= prevState.period.since
                        ? { since: prevState.period.since, until: newUntil }
                        : { since: newUntil, until: newUntil };
                return {
                    ...prevState,
                    period: newPeriod,
                };
            });
        }
    }
    function handleInputBlur({ relatedTarget }) {
        const isRelatedTargetWithinPopover =
            relatedTarget != null && isNodeWithinPopover(relatedTarget);
        if (isRelatedTargetWithinPopover) {
            return;
        }
        setPopoverActive(false);
    }
    function handleMonthChange(month, year) {
        setDate({ month, year });
    }
    function handleCalendarChange({ start, end }) {
        const newDateRange = ranges.find((range) => {
            return (
                range.period.since.valueOf() === start.valueOf() &&
                range.period.until.valueOf() === end.valueOf()
            );
        }) || {
            alias: new Date(start).getFullYear() + "-" + ("0" + (new Date(start).getMonth() + 1)).slice(-2) + "-" + ("0" + new Date(start).getDate()).slice(-2) + "to" + new Date(end).getFullYear() + "-" + ("0" + (new Date(end).getMonth() + 1)).slice(-2) + "-" + ("0" + new Date(end).getDate()).slice(-2),
            title: "Custom",
            period: {
                since: start,
                until: end,
            },
        };
        setActiveDateRange(newDateRange);
    }
    function apply() {
        if (activeDateRange?.alias === "last_7_days") {
            dispatch(getData("last_7_days"));
        } else {
            dispatch(getData(activeDateRange?.alias));
        }
        setPopoverActive(false);
    }
    function cancel() {
        setPopoverActive(false);
    }
    useEffect(() => {
        if (activeDateRange) {
            setInputValues({
                since: formatDate(activeDateRange.period.since),
                until: formatDate(activeDateRange.period.until),
            });
            function monthDiff(referenceDate, newDate) {
                return (
                    newDate.month -
                    referenceDate.month +
                    12 * (referenceDate.year - newDate.year)
                );
            }
            const monthDifference = monthDiff(
                { year, month },
                {
                    year: activeDateRange.period.until.getFullYear(),
                    month: activeDateRange.period.until.getMonth(),
                }
            );
            if (monthDifference > 1 || monthDifference < 0) {
                setDate({
                    month: activeDateRange.period.until.getMonth(),
                    year: activeDateRange.period.until.getFullYear(),
                });
            }
        }

    }, [activeDateRange]);

    const sinceDate = activeDateRange.period.since;
    const untilDate = activeDateRange.period.until;
    // Function to get month abbreviation
    function getMonthAbbreviation(month) {
        const monthAbbreviations = [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ];
        return monthAbbreviations[month];
    }
    const formattedUntilDate = `${getMonthAbbreviation(untilDate.getMonth())} ${untilDate.getDate()}, ${untilDate.getFullYear()}`;
    const todayDate = new Date();
    const isToday = sinceDate.getDate() === todayDate.getDate() &&
        sinceDate.getMonth() === todayDate.getMonth() &&
        sinceDate.getFullYear() === todayDate.getFullYear();
    let buttonValue;
    if (isToday) {
        buttonValue = formattedUntilDate;
    } else {
        const formattedSinceDate = `${getMonthAbbreviation(sinceDate.getMonth())} ${sinceDate.getDate()}`;
        buttonValue = `${formattedSinceDate} - ${formattedUntilDate}`;
    }


    const handleChange = useCallback(
        () => setActive(!active), [active]

    );
    const handleEmailChange = useCallback((newValue, name) => {
        setEmail(newValue);
        setCheckError(false);
    }, [email]);

    const isValidEmail = (email) => {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return pattern.test(email);
    }
    const resetData = () => {
        setCheckError(false);
        setEmail("");
        handleChange();
    }

    const handleExport = async () => {
        try {
            if (!isValidEmail(email)) {
                setCheckError(true);
            } else {
                setCheckError(false);
                const p = (defaultFilter$?.p) ? `${defaultFilter$?.p}` : '';
                const lp = (defaultFilter$?.lp) ? `${defaultFilter$?.lp}` : '';
                const em = (defaultFilter$?.em) ? `${defaultFilter$?.em}` : '';
                const s = (defaultFilter$?.s) ? `${defaultFilter$?.s}` : '';

                const response = await instance.get(`/reports/export/${shopDomain}/${email}/${selectedSegmentIndex}?p=${p}&lp=${lp}&em=${em}&s=${s}`);
                toast.success("Export will be emailed to " + email);
                setEmail("");
                setActive(false);
            }
        } catch (error) {
            console.error('Error exporting CSV:', error);
        }
    };


    // DropDown function
    const segments = [
        {
            label: 'View Other Reports',
            id: 0,
            value: '0',
        },

        {
            label: 'Upcoming Renewals',
            id: 1,
            value: '1'
        },
        {
            label: 'Recent Billing Attempts',
            id: 2,
            value: '2',
        },
        {
            label: 'Newest Members',
            id: 3,
            value: '3',
        },
        {
            label: 'Recent Cancellations',
            id: 4,
            value: '4',
        },
    ];

    let stateIndex = 0;
    if (location.search == '?=upcoming_reports') {
        stateIndex = 1;
    } else if (location.search == '?=recent_billing_attempt_report') {
        stateIndex = 2;
    } else if (location.search == '?=newest_member_report') {
        stateIndex = 3;
    } else if (location.search == '?=recent_cancellation_report') {
        stateIndex = 4;
    }
    const [pickerOpen, setPickerOpen] = useState(false);
    const [selectedSegmentIndex, setSelectedSegmentIndex] = useState(stateIndex);

    const handleOpenPicker = () => {
        setPickerOpen(true);
        // const dropdownBtn = document.querySelector('.dropdownBtn');
        // if (dropdownBtn) {
        //     const dropdownWrap =document.querySelector('.dropdown_wrap');
        //     console.log("object",dropdownWrap)

        //     dropdownBtn.appendChild(dropdownWrap);
        // }
    };

    const handleClosePicker = () => {
        setPickerOpen(false);
    };

    // hitarthi
    const handleSegmentSelect = useCallback((segmentIndex) => {
        dispatch(filterReset());
        // Navigate and set the selected segment index
        if (segmentIndex === 1) navigate(`/reports?=upcoming_reports`, { replace: true });
        else if (segmentIndex === 2) navigate(`/reports?=recent_billing_attempt_report`, { replace: true });
        else if (segmentIndex === 3) navigate(`/reports?=newest_member_report`, { replace: true });
        else if (segmentIndex === 4) navigate(`/reports?=recent_cancellation_report`, { replace: true });
        else navigate(`/reports`, { replace: true });

        setSelectedSegmentIndex(Number(segmentIndex));

        // Close the picker after a short delay
        setTimeout(() => {
            setPickerOpen(false);
        }, 100);
    }, [dispatch, navigate]);
    // hitarthi

    const activator = (
        <div className='dropdownBtn' style={{display:"flex"}} id="reports">
            <Button
                size="slim"
                onClick={handleOpenPicker}
                textAlign='left'
            >
            <Text fontWeight='medium' alignment='start'>{segments[selectedSegmentIndex].label}</Text>
            <div className="other-reports">
                <Icon
                source={CaretDownIcon}
                tone="base"
                alignItems='end'
                />
            </div>
            </Button>
        </div>
    );

    const segmentList =
        segments.length > 0
            ? segments
                .map(({ label, id, value }) => {
                    const selected = segments[selectedSegmentIndex].id === id;
                    return (
                        <Listbox.Option key={id} value={value} selected={"selected"}>
                            <Listbox.TextOption selected={selected}>
                                <Text fontWeight='medium'>{label}</Text>
                            </Listbox.TextOption>
                        </Listbox.Option>
                    );
                })
            : null;

    const listboxMarkup = (
        <Listbox
            onSelect={handleSegmentSelect}
        >
            {segmentList}
        </Listbox>
    );

    return (
        <div className="reportsWrap">
            <div className="report_subheader">
                <InlineStack align="space-between" blockAlign="center">
                    <SubHeader
                        title={"Reports"}
                        needHelp={false}
                        secondButtonState={false}
                        exportButtonState={false}
                    />
                    {/* hitarthi */}
                    {selectedSegmentIndex !== 0 && (
                        <div style={{ marginRight: "40px" }}>
                            <Button onClick={handleChange}>Export</Button>
                        </div>
                    )}
                    <Modal
                        open={active}
                        onClose={resetData}
                        title={<Text fontWeight="medium">Export Report</Text>}
                        primaryAction={{
                            content: "Send Mail",
                            onAction: handleExport,
                        }}
                        secondaryActions={[
                            {
                                content: "Cancel",
                                onAction: resetData,
                            },
                        ]}
                    >
                        <Modal.Section>
                            <TextField
                                type="email"
                                label={"Email"}
                                value={email}
                                name="email"
                                onChange={(val) => handleEmailChange(val)}
                                autoComplete="email"
                                error={checkerror ? "Email is Invalid" : ""}
                            />
                        </Modal.Section>
                    </Modal>
                </InlineStack>
            </div>
            <div className="simplee_membership_main_wrap report_add">
                <div className="simplee_membership_container">
                    <Page fullWidth>
                        <div className="calanderWrap">
                            {isLoading ? (
                                <ReportSekeleton />
                            ) : (
                                <>
                                    {/* DropDown selecter */}

                                    <Popover
                                        active={pickerOpen}
                                        activator={activator}
                                        ariaHaspopup="listbox"
                                        preferredAlignment="left"
                                        autofocusTarget="first-node"
                                        onClose={handleClosePicker}
                                    >
                                        <div className="dropdown_wrap">
                                            <Popover.Pane fixed>
                                                <div
                                                    style={{
                                                        alignItems: "stretch",
                                                        borderTop:
                                                            "1px solid #DFE3E8",
                                                        display: "flex",
                                                        flexDirection: "column",
                                                        justifyContent:
                                                            "stretch",
                                                        position: "relative",
                                                        width: "100%",
                                                        height: "100%",
                                                        overflow: "hidden",
                                                    }}
                                                >
                                                    <Scrollable
                                                        shadow
                                                        style={{
                                                            position:
                                                                "relative",
                                                            width: "100vw",
                                                            height: "132%",
                                                            padding:
                                                                "var(--p-space-200) 0",
                                                            borderBottomLeftRadius:
                                                                "var(--p-border-radius-200)",
                                                            borderBottomRightRadius:
                                                                "var(--p-border-radius-200)",
                                                        }}
                                                    >
                                                        {listboxMarkup}
                                                    </Scrollable>
                                                </div>
                                            </Popover.Pane>
                                        </div>
                                    </Popover>

                                    {selectedSegmentIndex === 0 && (
                                        <Popover
                                            active={popoverActive}
                                            autofocusTarget="none"
                                            preferredAlignment="left"
                                            preferredPosition="below"
                                            fluidContent
                                            sectioned={false}
                                            fullHeight
                                            activator={
                                                <div
                                                    className="dropdownBtn"
                                                    style={{ display: "flex" }}
                                                    id="reports"
                                                >
                                                    <Button
                                                        size="slim"
                                                        onClick={() =>
                                                            setPopoverActive(
                                                                !popoverActive
                                                            )
                                                        }
                                                        textAlign="left"
                                                    >
                                                        <Text
                                                            fontWeight="medium"
                                                            alignment="start"
                                                        >
                                                            {buttonValue}
                                                        </Text>
                                                        <div className="other-reports">
                                                            <Icon
                                                                source={
                                                                    CalendarIcon
                                                                }
                                                                tone="base"
                                                                alignItems="end"
                                                            />
                                                        </div>
                                                    </Button>
                                                </div>
                                            }
                                            onClose={() =>
                                                setPopoverActive(false)
                                            }
                                        >
                                            <InlineGrid
                                                columns={{
                                                    xs: "1fr",
                                                    mdDown: "1fr",
                                                    md: "max-content max-content",
                                                }}
                                                gap={0}
                                                ref={datePickerRef}
                                            >
                                                <Box
                                                    maxWidth={
                                                        mdDown
                                                            ? "516px"
                                                            : "141px"
                                                    }
                                                    width={
                                                        mdDown
                                                            ? "100%"
                                                            : "141px"
                                                    }
                                                    padding={{ xs: 500, md: 0 }}
                                                    paddingBlockEnd={{
                                                        xs: 100,
                                                        md: 0,
                                                    }}
                                                    id="calanderBox"
                                                >
                                                    {mdDown ? (
                                                        <Select
                                                            label="dateRangeLabel"
                                                            labelHidden
                                                            onChange={(
                                                                value
                                                            ) => {
                                                                const result =
                                                                    ranges.find(
                                                                        ({
                                                                            title,
                                                                            alias,
                                                                        }) =>
                                                                            title ===
                                                                                value ||
                                                                            alias ===
                                                                                value
                                                                    );
                                                                setActiveDateRange(
                                                                    result
                                                                );
                                                                const month =
                                                                    result.period.since.getMonth();
                                                                const year =
                                                                    result.period.since.getFullYear();
                                                                setDate({
                                                                    month,
                                                                    year,
                                                                });
                                                            }}
                                                            value={
                                                                activeDateRange?.title ||
                                                                activeDateRange?.alias ||
                                                                ""
                                                            }
                                                            options={ranges.map(
                                                                ({
                                                                    alias,
                                                                    title,
                                                                }) =>
                                                                    title ||
                                                                    alias
                                                            )}
                                                        />
                                                    ) : (
                                                        <Scrollable
                                                            style={{
                                                                height: "334px",
                                                            }}
                                                        >
                                                            <OptionList
                                                                options={ranges.map(
                                                                    (
                                                                        range
                                                                    ) => ({
                                                                        value: range.alias,
                                                                        label: range.title,
                                                                    })
                                                                )}
                                                                selected={
                                                                    activeDateRange.alias
                                                                }
                                                                onChange={(
                                                                    value
                                                                ) => {
                                                                    const selectedRange =
                                                                        ranges.find(
                                                                            (
                                                                                range
                                                                            ) =>
                                                                                range.alias ===
                                                                                value[0]
                                                                        );
                                                                    setActiveDateRange(
                                                                        selectedRange
                                                                    );
                                                                    const month =
                                                                        selectedRange.period.since.getMonth();
                                                                    const year =
                                                                        selectedRange.period.since.getFullYear();
                                                                    setDate({
                                                                        month,
                                                                        year,
                                                                    });
                                                                }}
                                                            />
                                                        </Scrollable>
                                                    )}
                                                </Box>
                                                <Box
                                                    padding={{ xs: 500 }}
                                                    maxWidth={
                                                        mdDown
                                                            ? "320px"
                                                            : "516px"
                                                    }
                                                >
                                                    <BlockStack gap="400">
                                                        <InlineStack
                                                            gap="2"
                                                            align="space-between"
                                                            blockAlign="center"
                                                        >
                                                            <div>
                                                                <TextField
                                                                    role="combobox"
                                                                    label="Since"
                                                                    labelHidden
                                                                    prefix={
                                                                        <Icon
                                                                            source={
                                                                                CalendarIcon
                                                                            }
                                                                        />
                                                                    }
                                                                    value={
                                                                        inputValues.since
                                                                    }
                                                                    onChange={
                                                                        handleStartInputValueChange
                                                                    }
                                                                    onBlur={
                                                                        handleInputBlur
                                                                    }
                                                                    autoComplete="off"
                                                                    disabled
                                                                />
                                                            </div>
                                                            <Icon
                                                                source={
                                                                    ArrowRightIcon
                                                                }
                                                            />
                                                            <div>
                                                                <TextField
                                                                    role="combobox"
                                                                    label="Until"
                                                                    labelHidden
                                                                    prefix={
                                                                        <Icon
                                                                            source={
                                                                                CalendarIcon
                                                                            }
                                                                        />
                                                                    }
                                                                    value={
                                                                        inputValues.until
                                                                    }
                                                                    onChange={
                                                                        handleEndInputValueChange
                                                                    }
                                                                    onBlur={
                                                                        handleInputBlur
                                                                    }
                                                                    autoComplete="off"
                                                                    disabled
                                                                />
                                                            </div>
                                                        </InlineStack>
                                                        <div>
                                                            <DatePicker
                                                                month={month}
                                                                year={year}
                                                                selected={{
                                                                    start: activeDateRange
                                                                        .period
                                                                        .since,
                                                                    end: activeDateRange
                                                                        .period
                                                                        .until,
                                                                }}
                                                                onMonthChange={
                                                                    handleMonthChange
                                                                }
                                                                onChange={
                                                                    handleCalendarChange
                                                                }
                                                                multiMonth={
                                                                    shouldShowMultiMonth
                                                                }
                                                                allowRange
                                                                disableDatesAfter={
                                                                    today
                                                                }
                                                            />
                                                        </div>
                                                    </BlockStack>
                                                </Box>
                                            </InlineGrid>
                                        </Popover>
                                    )}
                                </>
                            )}
                        </div>
                    </Page>
                    <Page fullWidth>
                        <Suspense fallback={<ReportSekeleton />}>
                            {selectedSegmentIndex === 1 && <UpcomingRenewals />}
                            {selectedSegmentIndex === 2 && <BillingAttempts />}
                            {selectedSegmentIndex === 3 && <NewestMembers />}
                            {selectedSegmentIndex === 4 && (
                                <RecentCancellation />
                            )}
                            {selectedSegmentIndex === 0 && <OtherReports />}
                        </Suspense>
                    </Page>



                    {/* <Page fullWidth>
                        {selectedSegmentIndex == 2 &&

                        <BillingAttempts />}
                    </Page>
                    {/*
                     <Page fullWidth>
                            <UpcomingRenewals/>
                    </Page> */}

                    {/*<Page fullWidth>
                        {selectedSegmentIndex === 1 && <UpcomingRenewals />}
                    </Page>

                    <Page fullWidth >
                        {selectedSegmentIndex === 0 &&
                            <OtherReports />}
                    </Page>

                    <Page fullWidth >
                        {selectedSegmentIndex === 3 &&
                            <NewestMembers />}
                    </Page>
                    <Page fullWidth >
                        {selectedSegmentIndex === 4 &&
                            <RecentCancellation />}
                    </Page> */}
                </div>
            </div>
        </div>
    );
}

export default Reports
