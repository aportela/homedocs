import { boot } from "quasar/wrappers";
import { useSessionStore } from "stores/session";

export default boot(({ app, router, store }) => {
  router.beforeEach((to, from, next) => {
    const session = useSessionStore(store);
    if (!session.isLoaded) {
      session.load();
    }
    const isLogged = session.isLogged;

    // TODO: not found page
    if (isLogged) {
      if (!to.name) {
        // no route => index
        next({
          name: "index",
        });
      } else {
        if (to.name !== "signIn" && to.name != "signUp") {
          // go to specified route
          next();
        } else {
          next({
            name: "index",
          });
        }
      }
    } else {
      if (!to.name) {
        // no route => signIn
        next({
          name: "signIn",
        });
      } else if (to.name !== "signIn" && to.name != "signUp") {
        // not allowed route  (! signin && !signup)
        next({
          name: "signIn",
        });
      } else {
        // allowed route (signin || signup)
        next();
      }
    }
  });
});
