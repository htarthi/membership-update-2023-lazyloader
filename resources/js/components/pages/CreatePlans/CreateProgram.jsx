import { Button, Icon, IndexTable, LegacyCard, Page, Text } from '@shopify/polaris'
import React from 'react'
import profile from '../../../../images/customer-profile.png';
import { useNavigate } from 'react-router-dom';
import { MobileIcon } from '@shopify/polaris-icons';


export default function CreateProgram() {

    const navigate = useNavigate();

    const programTableData = [
        {
            id: "688436216117",
            membership_length: '1 Year',
            price: 'CA$1000',
            members: '0'
        },
        {
            id: "688436216118",
            membership_length: '1 Week',
            price: 'CA$1000',
            members: '0'
        },
        {
            id: "688436216119",
            membership_length: '1 Month',
            price: 'CA$1000',
            members: '0'
        },
    ];
    const resourceName = {
    singular: 'program',
    plural: 'programs',
    };

    const rowMarkup = programTableData?.map(
    (
        {id, membership_length, price, members},
        index,
    ) => (
        <IndexTable.Row id={id} key={id} position={index}>
        <IndexTable.Cell>
            <div className='membership_length_block ms-margin-bottom-five'>
                <Text variant="bodyMd" fontWeight="medium" as="span">{membership_length}</Text>
            </div>
            <Text variant="bodyMd" fontWeight="medium" as="span" tone='subdued' >ID: {id}</Text>
        </IndexTable.Cell>
        <IndexTable.Cell><Text variant="bodyMd" fontWeight="medium" as="span">{price}</Text></IndexTable.Cell>
        <IndexTable.Cell><Text variant="bodyMd" fontWeight="medium" as="span">{members}</Text></IndexTable.Cell>
        </IndexTable.Row>
    ),
    );


  return (
      <Page>
          <div className='create_program_wrap'>
              <div className='simplee_membership_container'>
                  <LegacyCard>

                      {/* create program header */}
                      <div className='create_program_header flex-space-between'>
                          <div className='back_to_last'>
                              <Button onClick={() => navigate('/plans')}><Icon source={MobileIcon} color="base" /></Button>
                              <Text variant="headingLg" as="h5" tone="success">Programs</Text>
                          </div>

                          <Button  variant="primary">Create Program</Button>
                      </div>

                      {/* program list */}
                      <div className='program_list_row'>

                          <div className='program_list_col'>

                              {/* edit & program title */}
                              <div className='edit_block flex-space-between'>
                                  <Text variant="headingMd" as="h6">Mem - (Dev)</Text>
                                  <Button  onClick={() => navigate(`/edit-program`)} variant="primary">Edit</Button>
                              </div>

                              <div className='edit_profile_tag_block'>
                                  {/* profile & program name */}
                                  <div className='edit_profile_col edit_col'>
                                      <div className='profile_block'>
                                          <img src={profile} alt='profile' />
                                      </div>

                                      <Text variant="bodyLg" as="h6">Apple HomePod Mini</Text>
                                  </div>

                                  {/* customer & order tag */}
                                  <div className='edit_tag_col edit_col'>
                                      <div className='tag_col'>
                                          <Text variant="bodyLg" as="h6" fontWeight='medium'>Customer Tag</Text>
                                          <Text variant="bodyLg" as="h6" fontWeight='medium'>customer_tag_1</Text>
                                      </div>
                                      <div className='tag_col'>
                                          <Text variant="bodyLg" as="h6" fontWeight='medium'>Order Tag</Text>
                                          <Text variant="bodyLg" as="h6" fontWeight='medium'>order_tag_1</Text>
                                      </div>
                                  </div>
                              </div>


                              {/* table */}
                              <div className='program_membership_table ms-margin-top'>
                                  <IndexTable
                                      resourceName={resourceName}
                                      itemCount={programTableData.length}
                                      headings={[
                                      {title: 'Membership length'},
                                      {title: 'Price'},
                                      {title: 'Members'}
                                      ]}
                                      selectable={false}
                                  >
                                  {rowMarkup}
                                  </IndexTable>
                              </div>
                          </div>

                      </div>

                  </LegacyCard>

              </div>
          </div>
      </Page>
  );
}
