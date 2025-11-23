import { boot } from "quasar/wrappers";

import { useAPI } from "src/composables/useAPI";

const { api } = useAPI();

export default boot(async (/* { app, router, ... } */) => {
  try {
    // on useAxios composable we declare a response interceptor that assign serverEnvironment (if found) for every response
    await api.common.getServerEnvironment();
  } catch (error) {
    console.error(error);
  }
});
