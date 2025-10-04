import { boot } from "quasar/wrappers";
import { useSessionStore } from "src/stores/session";

export default boot(({ app, router, store }) => {
  router.beforeEach((to, from, next) => {
    const session = useSessionStore();

    if (!to.matched.length) {
      next({ name: 'notFound' });
      return;
    }

    if (!session.isLoaded) {
      session.load();
    }

    const isLogged = session.isLogged;

    if (isLogged) {
      if (to.name === 'signIn' || to.name === 'signUp') {
        next({ name: 'index' });
      } else {
        next();
      }
    } else {
      if (to.name === 'signIn' || to.name === 'signUp') {
        next();
      } else {
        next({ name: 'signIn' });
      }
    }
  });
});
