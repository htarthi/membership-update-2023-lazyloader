import { Modal, TextField ,Text } from '@shopify/polaris'
import React, {useCallback, useState, useEffect} from 'react'
import { useDispatch } from 'react-redux';
import { useSelector } from 'react-redux';
import { contractReducer, customerReducer } from '../../../../data/features/memberDetails/memberDetailsSlice';
import { shippingUpdate } from '../../../../data/features/memberDetails/membersDetailsAction';
import { useParams } from 'react-router-dom';

export default function EditMembersCustomer({ modalOpen, setModalOpen }) {

    const customer$ = useSelector((state) => state.memberDetails?.data?.contract?.customer);
    const contract$ = useSelector((state) => state.memberDetails?.data?.contract);

    const dispatch = useDispatch();
    const {id} = useParams();

    const [isValid, setIsValid] = useState(true);

    // customer data
    let data ={
        first_name: customer$?.first_name,
        last_name: customer$?.last_name,
        email: customer$?.email,
        ship_phone: contract$?.ship_phone,
        ship_firstName: contract$?.ship_firstName,
        ship_lastName: contract$?.ship_lastName,
        ship_address1: contract$?.ship_address1,
        ship_city: contract$?.ship_city,
        ship_country: contract$?.ship_country,
        ship_zip: contract$?.ship_zip,
        ship_province: contract$?.ship_province,
    }

    const [editFieldVal, setEditFieldVal] = useState(data);
    useEffect(() => {
        setEditFieldVal(data)
    }, [customer$, contract$])

    // edit input handle change
    const editInputHandleChange = useCallback((value, name) => {
        setEditFieldVal({
            ...editFieldVal,
            [name]: value,
        });
        name === "phone" && setIsValid(validatePhoneNumber(value))
    }, [editFieldVal, isValid]);

    // update customer
    const updateCustomer = useCallback(() => {
        dispatch(customerReducer({
            email: editFieldVal?.email,
        }))
        dispatch(contractReducer({
            ship_phone: editFieldVal?.ship_phone,
            ship_firstName: editFieldVal?.ship_firstName,
            ship_lastName: editFieldVal?.ship_lastName,
            ship_address1: editFieldVal?.ship_address1,
            ship_city: editFieldVal?.ship_city,
            ship_country: editFieldVal?.ship_country,
            ship_zip: editFieldVal?.ship_zip,
            ship_province: editFieldVal?.ship_province
        }))
        // setEditFieldVal(data);
        setModalOpen(false);


        const payload = {
            ship_firstName: editFieldVal?.ship_firstName,
            ship_lastName: editFieldVal?.ship_lastName,
            ship_company: '',
            ship_address1: editFieldVal?.ship_address1,
            ship_address2: '',
            ship_city: editFieldVal?.ship_city,
            ship_province: editFieldVal?.ship_province,
            ship_provinceCode: contract$?.ship_provinceCode,
            ship_zip: editFieldVal?.ship_zip,
            ship_country: editFieldVal?.ship_country,
            ship_phone: editFieldVal?.ship_phone,
        }
        dispatch(shippingUpdate({id, payload}))

    }, [customer$, contract$, editFieldVal])

    // cancle modal
    const cancleHandleChange = useCallback(() => {
        setModalOpen(!modalOpen);
    }, [modalOpen]);



    const validatePhoneNumber = (phoneNumber) => {
        const phoneRegex = /^\d+$/;
        return phoneRegex.test(phoneNumber);
    };



  return (
    <Modal
            open={modalOpen}
            onClose={cancleHandleChange}
            title={<Text>Shipping Address</Text>} 
            primaryAction={{
                content: `Update`,
                onAction: updateCustomer,
                // tone : 'success',
            }}
            secondaryActions={[
                {
                    content: `Cancel`,
                    onAction: cancleHandleChange,
                },
            ]}
        >
            <Modal.Section>
            
                <div className='customers_edit_modal membership_detail_edit_wrap'>

                    <div className="input_fields_wrap">
                        <TextField
                            label="Customer Name"
                            type="text"
                            value={editFieldVal?.first_name + " " + editFieldVal?.last_name}
                            onChange={(value) => editInputHandleChange(value, 'ship_firstName')}
                            autoComplete="off"
                            readOnly
                        />
                    </div>

                    {/* Ship First Name & Ship Last Name */}
                    <div className="input_two_fields_wrap">
                        <div className="input_fields_wrap">
                            <TextField
                                label="First Name"
                                type="text"
                                value={editFieldVal?.ship_firstName}
                                onChange={(value) => editInputHandleChange(value, 'ship_firstName')}
                                autoComplete="off"
                            />
                        </div>
                        <div className="input_fields_wrap">
                            <TextField
                                label="Last Name"
                                type="text"
                                value={editFieldVal?.ship_lastName}
                                onChange={(value) => editInputHandleChange(value, 'ship_lastName')}
                                autoComplete="off"
                            />
                        </div>
                    </div>

                    {/* Contact Information */}
                    <div className="input_fields_wrap">
                        <TextField
                            label="Phone Number"
                            type="text"
                            value={editFieldVal?.ship_phone}
                            error={editFieldVal?.ship_phone !== '' ? !isValid ? 'Invalid phone number format' : '' : ''}
                            onChange={(value) => editInputHandleChange(value, 'ship_phone')}
                            autoComplete="off"
                        />
                    </div>
                    {/* Address */}
                    <div className="input_fields_wrap">
                        <TextField
                            label="Address"
                            type="text"
                            value={editFieldVal?.ship_address1}
                            onChange={(value) => editInputHandleChange(value, 'ship_address1')}
                            autoComplete="off"
                        />
                    </div>

                    <div className="input_two_fields_wrap">
                        <div className="input_fields_wrap">
                            <TextField
                                label="City"
                                type="text"
                                value={editFieldVal?.ship_city}
                                onChange={(value) => editInputHandleChange(value, 'ship_city')}
                                autoComplete="off"
                            />
                        </div>
                        <div className="input_fields_wrap">
                            <TextField
                                label="Zip "
                                type="text"
                                value={editFieldVal?.ship_zip}
                                onChange={(value) => editInputHandleChange(value, 'ship_zip')}
                                autoComplete="off"
                            />
                        </div>
                    </div>

                    {/* City & Country */}
                    <div className="input_two_fields_wrap">
                      

                        <div className="input_fields_wrap">
                            <TextField
                                label="State/Province"
                                type="text"
                                value={editFieldVal?.ship_province}
                                onChange={(value) => editInputHandleChange(value, 'ship_province')}
                                autoComplete="off"
                            />
                        </div>
                        
                        <div className="input_fields_wrap">
                            <TextField
                                label="Country"
                                type="text"
                                value={editFieldVal?.ship_country}
                                onChange={(value) => editInputHandleChange(value, 'ship_country')}
                                autoComplete="off"
                            />
                        </div>
                    </div>

                    


                    {/* Zip */}
                    

                </div>

            </Modal.Section>
        </Modal>
  )
}
