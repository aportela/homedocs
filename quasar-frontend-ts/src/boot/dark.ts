import { boot } from "quasar/wrappers";

import { useDarkMode } from "src/composables/useDarkMode";

export default boot((/* { app, router, ... } */) => {
  useDarkMode();
});
