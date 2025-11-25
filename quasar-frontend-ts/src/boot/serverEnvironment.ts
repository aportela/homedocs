import { boot } from "quasar/wrappers";

import { api } from "src/composables/useAPI";

export default boot(async () => {
  try {
    // on useAxios composable we declare a response interceptor that assign serverEnvironment (if found) for every response
    await api.common.getServerEnvironment();
  } catch (error) {
    console.error(error);
  }
});
