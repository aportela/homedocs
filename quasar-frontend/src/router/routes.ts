import type { RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
  {
    path: "/auth",
    component: () => import("layouts/LoginRegisterLayout.vue"),
    children: [
      {
        name: "login",
        path: "login",
        component: () => import("pages/LoginPage.vue"),
      },
      {
        name: "register",
        path: "register",
        component: () => import("pages/RegisterPage.vue"),
      },
    ],
  },
  {
    path: "/",
    name: "root",
    redirect: "/index",
    component: () => import("layouts/MainLayout.vue"),
    children: [
      {
        name: "index",
        path: "index",
        component: () => import("pages/IndexPage.vue"),
      },
      {
        name: "profile",
        path: "profile",
        component: () => import("pages/ProfilePage.vue"),
      },
      {
        name: "newDocument",
        path: "new-document",
        component: () => import("pages/DocumentPage.vue"),
      },
      {
        name: "document",
        path: "document/:id",
        component: () => import("pages/DocumentPage.vue"),
      },
      {
        name: "advancedSearch",
        path: "advanced-search",
        component: () => import("pages/AdvancedSearchPage.vue"),
        meta: { conditionsFilterExpanded: true, autoLaunchSearch: false },
      },
      {
        name: "advancedSearchByTag",
        path: "advanced-search/tag/:tag",
        component: () => import("pages/AdvancedSearchPage.vue"),
        meta: { conditionsFilterExpanded: false, autoLaunchSearch: true },
      },
      {
        name: "advancedSearchByFixedCreationDate",
        path: "advanced-search/fixed-creation-date/:fixedCreationDate",
        component: () => import("pages/AdvancedSearchPage.vue"),
        meta: { conditionsFilterExpanded: false, autoLaunchSearch: true },
      },
      {
        name: "advancedSearchByFixedLastUpdate",
        path: "advanced-search/fixed-last-update/:fixedLastUpdate",

        component: () => import("pages/AdvancedSearchPage.vue"),
        meta: { conditionsFilterExpanded: false, autoLaunchSearch: true },
      },
      {
        name: "advancedSearchByFixedUpdatedOn",
        path: "advanced-search/fixed-updated-on/:fixedUpdatedOn",
        component: () => import("pages/AdvancedSearchPage.vue"),
        meta: { conditionsFilterExpanded: false, autoLaunchSearch: true },
      },
    ],
  },
  // Always leave this as last one,
  // but you can also remove it
  {
    path: "/:catchAll(.*)*",
    name: "notFound",
    component: () => import("layouts/ErrorNotFoundLayout.vue"),
  },
];

export default routes;
