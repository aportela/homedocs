import { boot } from "quasar/wrappers";

import { useAxios } from "src/composables/useAxios";

// "async" is optional;
// more info on params: https://v2.quasar.dev/quasar-cli/boot-files
export default boot(async (/* { app, router, ... } */) => {
  // init axios
  useAxios();
});
