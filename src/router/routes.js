const routes = [
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
        name: "newDocument",
        path: "new_document",
        component: () => import("pages/NewDocument.vue"),
      },
      {
        name: "advancedSearch",
        path: "advanced_search",
        component: () => import("pages/AdvancedSearch.vue"),
      },
      {
        name: "signIn",
        path: "sign_in",
        component: () => import("pages/SignInPage.vue"),
      },
      {
        name: "signUp",
        path: "sign_up",
        component: () => import("pages/SignUpPage.vue"),
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
