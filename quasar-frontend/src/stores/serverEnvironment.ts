import { defineStore, acceptHMRUpdate } from "pinia";

import { type EnvironmentType } from "src/types/common";

interface State {
  allowSignUp: boolean;
  environment: EnvironmentType;
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
      environment: EnvironmentType = "production",
      maxUploadFileSize: number = 0,
    ): void {
      this.allowSignUp = !!allowSignUp;
      this.environment = environment;
      this.maxUploadFileSize = maxUploadFileSize > 0 ? maxUploadFileSize : 0;
    },
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useServerEnvironmentStore, import.meta.hot));
}
