import React, { useEffect, useMemo } from "react";
import { useLocation, useNavigate, useSearchParams } from "react-router-dom";
import { useSelector } from "react-redux";
import { Provider } from "@shopify/app-bridge-react";
import { AppProvider } from "@shopify/polaris";
import "@shopify/polaris/build/esm/styles.css";
import { useTranslation } from "react-i18next";
import "./services/i18n/config";
import "../css/style.scss";
import Tabs from "./components/layouts/Tabs";
import RoutePath from "./components/routes/RoutePath";
import enTranslations from "@shopify/polaris/locales/en.json";
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import { checkActivePlan, productUpdate } from "./data/features/plans/planAction";
import { checkMaintainceMode } from "./data/features/dashboard/dashboardAction";
import { useDispatch } from "react-redux";
import { getUserDetails } from "./data/features/dashboard/dashboardAction";
import { dashboardSlice } from "./data/features/dashboard/dashboardSlice";
import CryptoJS from 'crypto-js';
import MaintainceBanner from "./components/pages/Dashborad/partials/MaintainceBanner";


export default function App() {


    const navigate = useNavigate();
    const location = useLocation();
    const { i18n } = useTranslation();
    const dispatch = useDispatch();
    const activePlan$ = useSelector((state) => state?.plans?.activePlan);
    const inMaintenance$ = useSelector((state) => state?.dashboard?.inMaintenance);
    const lang = useSelector((state) => state.app.lang);
    const initData = window.initialData;
    const user = window.user;
    const getUser$ = useSelector((state) => state?.dashboard);

    const [searchParams] = useSearchParams();
    const paramValue = searchParams.get('secret');

    const secretKey = 'sunny';


    const getFromLocalStorage = (key) => {
        const encryptedData = localStorage.getItem(key);
        if (!encryptedData) return null;
        try {

            return decryptData(encryptedData);
        } catch (e) {
            console.log('Error decrypting data', e);
            return null;
        }
    }


    const decryptData = (ciphertext) => {
        const bytes = CryptoJS.AES.decrypt(ciphertext, secretKey);
        const decryptedData = bytes.toString(CryptoJS.enc.Utf8);
        return JSON.parse(decryptedData);
    };


    useEffect(() => {
        i18n.changeLanguage(lang);
        dispatch(checkActivePlan());
        dispatch(getUserDetails());
        dispatch(productUpdate());

        if (paramValue) {
            dispatch(checkMaintainceMode(paramValue));
        }else{
            const val = getFromLocalStorage('maintain_secret');
            dispatch(checkMaintainceMode(val));
        }
        if (initData) {
            navigate('/members/' + initData + '/edit')
        }

    }, [lang]);

    const config = {
        apiKey: __SHOPIFY_API_KEY,
        host: new URLSearchParams(location.search).get("host"),
        forceRedirect: true,
    };

    const history = useMemo(
        () => ({ replace: (path) => navigate(path, { replace: true }) }),
        [navigate]
    );

    const router = useMemo(
        () => ({
            location,
            history,
        }),
        [location, history]
    );


    const reloadFunc = () => {
        window.location.href = "/app-plan/" + activePlan$.userID;
    };

    //======================== Intercom =======================//
    var APP_ID = "a7xla5ct";
    (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/' + APP_ID;var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s, x);};if(document.readyState==='complete'){l();}else if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
    window.Intercom("boot", {
        app_id: APP_ID,
        user_id: getUser$?.getUser?.user_id,
        user_hash: getUser$?.getUser?.hmac,
        name: getUser$?.getUser?.name,
        email: getUser$?.getUser?.email,
        created_at: getUser$?.getUser?.created_at,
        "myshopify_domain": getUser$?.getUser?.myshopify_domain,
        "simplee_app": "Memberships"
    });

    return activePlan$.plan == 0 ? (
        reloadFunc()
    ) : inMaintenance$ ? <MaintainceBanner/> : (
        // <AppProvider theme={{ colorScheme: "light" }} i18n={enTranslations}>
        <AppProvider i18n={[]}  >
            <Provider config={config} router={router}>
                <RoutePath />
                <Tabs />
                <ToastContainer
                    position="bottom-right"
                    autoClose={3000}
                    hideProgressBar={false}
                    newestOnTop={false}
                    closeOnClick
                    rtl={false}
                    pauseOnFocusLoss
                    draggable
                    pauseOnHover
                    theme="light"
                />
            </Provider>
        </AppProvider>
    );
}
