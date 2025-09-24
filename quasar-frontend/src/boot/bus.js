import { boot } from "quasar/wrappers";
import { EventBus } from "quasar";

const bus = new EventBus();

export default boot(({ app }) => {
  // for use inside Vue files (Options API) through this.$bus
  app.config.globalProperties.$bus = bus;
  // ^ ^ ^ this will allow you to use this.$bus (for Vue Options API form)
  //       so you won't necessarily have to import bus in each vue file

});

export { bus };
