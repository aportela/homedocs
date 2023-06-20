import { boot } from "quasar/wrappers";
import { useSessionStore } from "stores/session";

export default boot(({ app, router, store }) => {
  router.beforeEach((to, from, next) => {
    const session = useSessionStore(store);
    session.load();

    const isLogged = session.isLogged;
    if (
      // make sure the user is authenticated
      !isLogged &&
      // ❗️ Avoid an infinite redirect
      to.name !== "signIn" &&
      to.name != "signUp"
    ) {
      next({
        name: "signIn",
      });
    } else {
      next();
    }
  });
});
