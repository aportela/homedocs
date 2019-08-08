import { default as signIn } from './section-sign-in.js';
import { default as signUp } from './section-sign-up.js';
export const routes = [
    { path: '/sign-in', name: 'signIn', component: signIn },
    { path: '/sign-up', name: 'signUp', component: signUp }
]
