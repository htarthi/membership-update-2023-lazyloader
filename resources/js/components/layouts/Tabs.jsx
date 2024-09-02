
import React from 'react'
import { NavigationMenu } from '@shopify/app-bridge-react';

function Tabs() {

    return (
        <>
            <NavigationMenu
                navigationLinks={[
                    // {
                    //     label: 'Dashboard',
                    //     destination: '/',
                    // },
                    {
                        label: 'Memberships',
                        destination: '/members',
                    },
                    {
                        label: 'Plans',
                        destination: '/plans',
                    },
                    {
                        label: 'Reports',
                        destination: '/reports',
                    },
                    {
                        label: 'Installation',
                        destination: '/installation',
                    },
                    {
                        label: 'Settings',
                        destination: '/settings',
                    },
                ]}
            />
        </>
    )
}

export default Tabs
