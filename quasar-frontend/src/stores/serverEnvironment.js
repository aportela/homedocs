import { defineStore } from "pinia";

export const useServerEnvironmentStore = defineStore("serverEnvironment", {
  state: () => ({
    allowSignUp: true,
    environment: "production",
    maxUploadFileSize: 0,
  }),
  getters: {
    isSignUpAllowed: (state) => state.allowSignUp,
    isCurrentEnvironmentDevelopment: (state) =>
      state.environment == "development",
    currentEnvironmentMaxUploadFileSize: (state) =>
      state.maxUploadFileSize,
  },
  actions: {
    set(
      allowSignUp = false,
      environment = "production",
      maxUploadFileSize = 0,
    ) {
      this.allowSignUp = !!allowSignUp;
      this.environment =
        environment === "development" ? "development" : "production";
      this.maxUploadFileSize = !isNaN(maxUploadFileSize)
        ? maxUploadFileSize
        : 0;
    },
  },
});
