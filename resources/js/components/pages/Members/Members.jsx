import { Page } from '@shopify/polaris'
import React, { useEffect,useState,lazy } from 'react'
import instance from '../../shopify/instance'
import NewMemberList from './Partials/NewMemberList'
import SubHeader from '../../GlobalPartials/SubHeader/SubHeader'

function Members() {



    return (
        <>
            <SubHeader title={"Memberships"} needHelp={false} secondButtonState={false} exportButtonState={true} />

            <Page fullWidth >
                <NewMemberList />
            </Page>
        </>
    )
}

export default Members
