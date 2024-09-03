import axios from "axios";
import { getSessionToken } from "@shopify/app-bridge/utilities/session-token/session-token";
import { createApp } from "@shopify/app-bridge";

const instance = axios.create({
}
);
const shopifConfig = {
    apiKey: __SHOPIFY_API_KEY,
    host: new URLSearchParams(location.search).get('host'),
    forceRedirect: true
}
const app = createApp(shopifConfig);

instance.interceptors.request.use(function (config) {
    return getSessionToken(app) 
        .then((token) => {
            config.headers.Authorization = `Bearer ${token}`
            return config
        })
})

export default instance;
