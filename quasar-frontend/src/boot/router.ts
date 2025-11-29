import { boot } from "quasar/wrappers";

import { useSessionStore } from "src/stores/session";

const session = useSessionStore();

export default boot(({ router }) => {
  router.beforeEach((to, _from, next) => {
    if (!to.matched.length) {
      next({ name: "notFound" });
      return;
    }
    if (session.isLogged) {
      if (to.name === "login" || to.name === "register") {
        next({ name: "index" });
      } else {
        next();
      }
    } else {
      if (to.name === "login" || to.name === "register") {
        next();
      } else {
        next({ name: "login" });
      }
    }
  });
});
