import { boot } from "quasar/wrappers";
import { useSessionStore } from "stores/session";

export default boot(({ app, router, store }) => {
  router.beforeEach((to, from, next) => {
    const isLogged = useSessionStore(store).isLogged;
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
      /*
      if (!to.name || to.name == "signIn" || to.name == "signUp") {
        next({ name: "index" });
      } else {
        console.log(to.name);
        next();
      }
      */
      next();
    }
  });
});
