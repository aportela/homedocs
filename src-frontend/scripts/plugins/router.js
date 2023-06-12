import { createWebHashHistory, createRouter } from "vue-router";

import { default as signIn } from "../pages/sign-in.js";
import { default as signUp } from "../pages/sign-up.js";
import { default as appContainer } from "../layouts/main.js";
import { default as appDashBoard } from "../pages/dashboard.js";
import { default as appAdvancedSearch } from "../pages/advanced-search.js";
import { default as document } from "../pages/document.js";

/**
 * vue-router route definitions
 */
const routes = [
  { path: "/sign-in", name: "signIn", component: signIn },
  { path: "/sign-up", name: "signUp", component: signUp },
  {
    path: "/app",
    name: "app",
    component: appContainer,
    children: [
      {
        path: "/dashboard",
        name: "appDashBoard",
        component: appDashBoard,
      },
      {
        path: "/advanced-search",
        name: "appAdvancedSearch",
        component: appAdvancedSearch,
      },
      {
        path: "/advanced-search/tag/:tag",
        name: "appAdvancedSearchByTag",
        component: appAdvancedSearch,
      },
      {
        path: "/add-document",
        name: "appAddDocument",
        component: document,
      },
      {
        path: "/document/:id",
        name: "appOpenDocument",
        component: document,
      },
    ],
  },
];

/**
 * main vue-router component inicialization
 */
const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // always scroll to top
    return { top: 0 };
  },
});

router.beforeEach(async (to, from) => {
  if (!initialState.session.logged) {
    if (to.name == "signUp" && initialState.allowSignUp) {
    } else if (to.name != "signIn") {
      return { name: "signIn" };
    }
  } else {
    if (!to.name) {
      return { name: "appDashBoard" };
    }
  }
});

export default router;
