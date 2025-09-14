const routes = [
  {
    path: "/auth",
    component: () => import("layouts/AuthRegisterLayout.vue"),
    children: [
      {
        name: "signIn",
        path: "sign-in",
        component: () => import("pages/SignInPage.vue"),
      },
      {
        name: "signUp",
        path: "sign-up",
        component: () => import("pages/SignUpPage.vue"),
      },
    ],
  },
  {
    path: "/",
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
      },
      {
        name: "advancedSearchByTag",
        path: "advanced-search/tag/:tag",
        component: () => import("pages/AdvancedSearchPage.vue"),
      },
      {
        name: "advancedSearchByFixedDate",
        path: "advanced-search/fixed-date/:fixedDate",
        component: () => import("pages/AdvancedSearchPage.vue"),
      },
    ],
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: "/:catchAll(.*)*",
    component: () => import("pages/ErrorNotFound.vue"),
  },
];

export default routes;
