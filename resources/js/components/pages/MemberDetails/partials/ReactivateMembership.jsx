import { Card, DatePicker, Icon, Modal, Popover, TextField, Text} from "@shopify/polaris";
import React, { useEffect, useState, useCallback } from "react";
import { CalendarIcon } from "@shopify/polaris-icons";
import { useSelector } from "react-redux";

export default function ReactivateMembership({active, setReactivateModal, reactivateMethod}) {

    const contract$ = useSelector((state) => state.memberDetails?.data?.contract);
    const todayDate = new Date();
    let nextDay = new Date();
    const newDate = new Date(nextDay.setDate(nextDay.getDate() + 1));
    console.log(newDate);
    // ------- Renewal / Biling date ------

    // selected date state
    const [selectedDate, setSelectedDate] = useState(contract$?.next_processing_date ? new Date(contract$?.next_processing_date) : new Date());

    // popver state
    const [visible, setVisible] = useState(false);

    // set month and year
    const [{ month, year }, setDate] = useState({
        month: selectedDate?.getMonth(),
        year: selectedDate?.getFullYear(),
    });

    //  Renewal / Biling date input onChange event
    const handleInputValueChange = useCallback((e) => {
        e.stopPropagation();
        setVisible(true)
    }, [visible])

    // month change
    function handleMonthChange(month, year) {
        setDate({ month, year });
    }

    // select date
    function handleDateSelection({ end: newSelectedDate }) {
        setSelectedDate(newSelectedDate);
        setVisible(false);
    }

    useEffect(() => {
        if (selectedDate) {
            if (selectedDate < todayDate) {
                setSelectedDate(newDate);
            } else if (selectedDate > todayDate) {
                setSelectedDate(selectedDate);
            } else {
                setSelectedDate(newDate);
            }
            setDate({
                month: selectedDate?.getMonth(),
                year: selectedDate?.getFullYear(),
            });
        }
    }, [selectedDate]);

    return (
        <Modal
            size="small"
            open={active}
            onClose={() => setReactivateModal(false)}
            title={<Text>Reactivate Membership</Text>}
            primaryAction={{
                content: "Reactivate",
                // tone : 'success',
                onAction: () => reactivateMethod(selectedDate?.toLocaleDateString(
                    "en-IN"
                )),
            }}
            secondaryActions={[
                {
                    content: "Cancel",
                    onAction: () => setReactivateModal(false),
                },
            ]}
        >
            <Modal.Section>
                <div className="textfield_wrap">
                    {/* Renewal / Biling date */}
                    <div className="input_fields_wrap">
                        <Popover
                            active={visible}
                            preferredAlignment="left"
                            fullWidth
                            preferInputActivator={false}
                            preferredPosition="below"
                            preventCloseOnChildOverlayClick
                            onClose={(e) => {e.stopPropagation(); setVisible(false)}}
                            activator={
                                <div className="input_fields_wrap">
                                    <TextField
                                        role="combobox"
                                        label={"Choose this membership's next order date:"}
                                        suffix={<Icon source={CalendarIcon} />}
                                        value={selectedDate?.toLocaleDateString(
                                            "en-IN"
                                        )}
                                        onFocus={(e) => handleInputValueChange(e)}
                                        onChange={''}
                                        autoComplete="off"
                                    />
                                </div>
                            }
                        >
                            <Card>
                                <DatePicker
                                    month={month}
                                    year={year}
                                    selected={selectedDate}
                                    onMonthChange={handleMonthChange}
                                    onChange={handleDateSelection}
                                    disableDatesBefore={new Date()}
                                />
                            </Card>
                        </Popover>
                    </div>
                </div>
            </Modal.Section>
        </Modal>
    );
}
