import { Button, ButtonGroup, Icon, LegacyCard, Page, Text } from '@shopify/polaris'
import React, { useState } from 'react'
import { EditIcon, DuplicateIcon, DeleteIcon, MobileIcon } from '@shopify/polaris-icons';
import product from '../../../../../images/member-product.png'
import ProductType from './ProductType';
import Options from './Options';
import { useNavigate } from 'react-router-dom';

export default function EditPlans() {

const navigate = useNavigate();

let productInputObj = {
    select_product_type: '',
    input_product_type: '',
    select_product_tag: '',
    input_product_tag: '',
}

const [productVal, setProductVal] = useState(productInputObj);

const handleChangeEvent = (value, name) => {
    setProductVal({
        ...productVal,
        [name]: value
    })
}

// selected type opetions
const typeOptions = [
    {label: 'T-Shirt', value: 't-shirt'},
];

// selected tag opetions
const tagOptions = [
    {label: 'T-Shirt-options', value: 't-shirt-options'},
];

  return (
      <Page>
          <div className='edit_program_wrap'>
              <div className='simplee_membership_container'>
                  <LegacyCard>

                      {/* title & Actions - Edit, Duplicate, Delete */}
                      <div className='edit_plan_header_block flex-space-between'>
                          <div className='back_to_last'>
                              <Button onClick={() => navigate('/create-program')}><Icon source={MobileIcon} color="base" /></Button>
                              <Text variant="headingLg" as="h5">This is a plan name</Text>
                          </div>

                          <div className='button_group_block'>
                              <ButtonGroup segmented>
                                  <Button><Icon source={EditIcon} color="base" /></Button>
                                  <Button><Icon source={DuplicateIcon} color="base" /></Button>
                                  <Button><Icon source={DeleteIcon} color="base" size="large"  /></Button>
                              </ButtonGroup>
                          </div>
                      </div>

                      <div className='edit_product_detail_block'>

                          {/* product image & name */}
                          <div className='product_picture_wrap'>
                              <div className='image_wrap'>
                                  <img src={product} alt='product image' />
                              </div>
                              <div className='name_wrap'>
                                  <div className="product_type_title">
                                      <Text variant="headingMd" as="h6">Animal T-Shirt</Text>
                                  </div>
                                  <Text variant="bodyLg" as="h6" fontWeight='regular' tone='subdued' >Elephant</Text>
                              </div>
                          </div>

                          {/* product type */}
                          <ProductType typeOptions={typeOptions} tagOptions={tagOptions} handleChangeEvent={handleChangeEvent} productVal={productVal}  />

                      </div>

                      {/* options */}
                      <Options />

                      {/* cancle & save */}
                      <div className='cancle_save_block'>
                          <ButtonGroup>
                              <Button>Cancel</Button>
                              <div className='save_button_wrap'>
                                  <Button  variant="primary"><Icon source={MobileIcon} color="base" />Save</Button>
                              </div>
                          </ButtonGroup>
                      </div>

                  </LegacyCard>
              </div>
          </div>
      </Page>
  );
}
