import React from 'react'
import TooltipComp from '../../Plans/partials/TooltipComp'
import { Button, ButtonGroup, Icon, IndexTable, Text } from '@shopify/polaris'
import { CashDollarIcon, ConnectIcon, EditIcon, DuplicateIcon, DeleteIcon } from '@shopify/polaris-icons';

export default function Options() {


    const optionsTableData = [
        {
            option_name: "T-Shirt Colors",
            type: 'Dropdown Menu',
            values: 'Red, Gold, Blue, Green, Red, Orange ',
        }
    ];
    const resourceName = {
        singular: 'option',
        plural: 'options',
    };

    const rowMarkup = optionsTableData?.map(
    ( {option_name, type, values}, index, ) => (
        <IndexTable.Row id={index} key={index} position={index}>
        <IndexTable.Cell></IndexTable.Cell>
        <IndexTable.Cell><Text variant="bodyMd" fontWeight="medium" as="span">{option_name}</Text></IndexTable.Cell>
        <IndexTable.Cell><Text variant="bodyMd" fontWeight="medium" as="span">{type}</Text></IndexTable.Cell>
        <IndexTable.Cell><Text variant="bodyMd" fontWeight="medium" as="span">{values}</Text></IndexTable.Cell>
        <IndexTable.Cell>
            <div className='options_status'>
                <button className='button_style'><Icon source={CashDollarIcon} color="primary" /></button>
                <button className='button_style'><Icon source={ConnectIcon} color="primary" /></button>
            </div>
        </IndexTable.Cell>
        <IndexTable.Cell>
            <ButtonGroup segmented>
                <Button><Icon source={EditIcon} color="base" /></Button>
                <Button><Icon source={DuplicateIcon} color="base" /></Button>
                <Button><Icon source={DeleteIcon} color="base" size="large" /></Button>
            </ButtonGroup>
        </IndexTable.Cell>
        </IndexTable.Row>
    ),
    );

  return (
      <div className='option_block'>
          {/* tooltip */}
          <TooltipComp title={"Options"} content={"This order has shipping labels."} />

          {/* Create Option & Add a Saved Option */}
          <div className='create_saved_option ms-margin-top-bottom'>
              <ButtonGroup>
                  <Button  variant="primary">Create Option</Button>
                  <Button>Add a Saved Option</Button>
              </ButtonGroup>
          </div>

          {/* options table */}
          <div className='options_table ms-margin-top'>
              <IndexTable
                  resourceName={resourceName}
                  itemCount={optionsTableData.length}
                  headings={[
                  {title: ''},
                  {title: 'Option Name'},
                  {title: 'Type'},
                  {title: 'Values'},
                  {title: 'Status'},
                  {title: 'Actions'}
                  ]}
                  selectable={false}
              >
              {rowMarkup}
              </IndexTable>
          </div>

      </div>
  );
}
