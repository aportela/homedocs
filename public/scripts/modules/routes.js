import { default as signIn } from './section-sign-in.js';
import { default as signUp } from './section-sign-up.js';
import { default as appContainer } from './section-app-container.js';
import { default as appDashBoard } from './section-app-dashboard.js';
import { default as document } from './document.js';

export const routes = [
    { path: '/sign-in', name: 'signIn', component: signIn },
    { path: '/sign-up', name: 'signUp', component: signUp },
    {
        path: '/app',
        name: 'app',
        component: appContainer ,
        children: [
            {
                path: '/dashboard',
                name: 'appDashBoard',
                component: appDashBoard
            },
            {
                path: '/document/:id',
                name: 'openDocument',
                component: document
            }
        ]
    },

]
