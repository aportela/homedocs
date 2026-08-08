import { defineBoot } from "#q-app";
import { api } from "@/composables/api";
import { useServerEnvironmentStore } from "@/stores/serverEnvironment";
import { type getServerEnvironmentResponseData } from "@/types/apiResponses";

const serverEnvironment = useServerEnvironmentStore();

export default defineBoot(async () => {
  try {
    const response: getServerEnvironmentResponseData =
      await api.common.getServerEnvironment();
    serverEnvironment.set(
      response.data.serverEnvironment.allowSignUp,
      response.data.serverEnvironment.environment,
      response.data.serverEnvironment.maxUploadFileSize,
    );
  } catch (error) {
    console.error(error);
  }
});
