// console.clear();

import ReactDOM from "react-dom/client";
import { BrowserRouter } from 'react-router-dom';
import { Provider as ReduxProvider } from 'react-redux'
import '@shopify/polaris-viz/build/esm/styles.css';

import { store } from './data/store'
import App from './App.jsx';

if (document.getElementById('app')) {

    const root = ReactDOM.createRoot(document.getElementById("app"));

    root.render(
        <BrowserRouter>
            <ReduxProvider store={store}>
                <App />
            </ReduxProvider>
        </BrowserRouter>);
}
