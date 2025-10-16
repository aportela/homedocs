import { defineStore } from "pinia";

export const useInitialStateStore = defineStore("initialState", {
  state: () => ({
    initialState: {
      environment: "production",
      allowSignUp: false,
      maxUploadFileSize: 0,
      session: {
        id: null,
        email: null,
      },
    },
  }),
  getters: {
    isSignUpAllowed: (state) => state.initialState.allowSignUp,
    maxUploadFileSize: (state) => state.initialState.maxUploadFileSize,
    session: (state) => state.initialState.session,
    isDevEnvironment: (state) =>
      state.initialState.environment == "development",
  },
  actions: {
    set(initialState) {
      this.initialState.allowSignUp = initialState.allowSignUp;
      this.initialState.maxUploadFileSize = initialState.maxUploadFileSize;
      this.initialState.session.id = initialState.session.id;
      this.initialState.session.email = initialState.session.email;
      this.initialState.environment =
        initialState.environment == "production" ? "production" : "development";
    },
  },
});
