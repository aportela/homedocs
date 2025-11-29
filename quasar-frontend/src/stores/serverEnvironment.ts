import { defineStore } from "pinia";

type environmentType = "development" | "production";

interface State {
  allowSignUp: boolean;
  environment: environmentType;
  maxUploadFileSize: number;
};

export const useServerEnvironmentStore = defineStore("serverEnvironment", {
  state: (): State => ({
    allowSignUp: true,
    environment: "production",
    maxUploadFileSize: 0,
  }),
  getters: {
    isSignUpAllowed(state): boolean {
      return state.allowSignUp
    },
    isCurrentEnvironmentDevelopment(state): boolean {
      return state.environment == "development"
    },
    currentEnvironmentMaxUploadFileSize(state): number {
      return state.maxUploadFileSize
    },
  },
  actions: {
    set(
      allowSignUp: boolean = false,
      environment: string = "production",
      maxUploadFileSize: number = 0,
    ): void {
      this.allowSignUp = !!allowSignUp;
      this.environment =
        environment === "development" ? "development" : "production";
      this.maxUploadFileSize = maxUploadFileSize > 0 ? maxUploadFileSize : 0;
    },
  },
});
