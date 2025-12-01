import { boot } from "quasar/wrappers";

import { api } from "src/composables/api";
import { useServerEnvironmentStore } from "src/stores/serverEnvironment";
import { type getServerEnvironmentResponseData } from "src/types/api-responses";

const serverEnvironment = useServerEnvironmentStore();

export default boot(async () => {
  try {
    // on useAxios composable we declare a response interceptor that assign serverEnvironment (if found) for every response
    const response: getServerEnvironmentResponseData = await api.common.getServerEnvironment();
    serverEnvironment.set(
      response.data.serverEnvironment.allowSignUp,
      response.data.serverEnvironment.environment,
      response.data.serverEnvironment.maxUploadFileSize
    );
  } catch (error) {
    console.error(error);
  }
});
