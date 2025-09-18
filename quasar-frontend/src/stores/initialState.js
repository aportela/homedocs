import { defineStore } from "pinia";
import { api } from "boot/axios";

export const useInitialStateStore = defineStore("initialState", {
  state: () => ({
    initialState: {
      environment: 'production',
      allowSignUp: false,
      maxUploadFileSize: 0,
      session: {
        id: null,
        email: null,
      }
    }
  }),
  getters: {
    isSignUpAllowed: (state) => state.initialState.allowSignUp,
    maxUploadFileSize: (state) => state.initialState.maxUploadFileSize,
    session: (state) => state.initialState.session,
    isDevEnvironment: (state) => state.initialState.environment == 'development',
  },
  actions: {
    async load() {
      try {
        const success = await api.common.initialState();
        this.initialState.allowSignUp = success.data.initialState.allowSignUp;
        this.initialState.maxUploadFileSize = success.data.initialState.maxUploadFileSize || 20;
        this.initialState.session.id = success.data.initialState.session.id;
        this.initialState.session.email = success.data.initialState.session.email;
        this.initialState.environment = success.data.initialState.environment == "production" ? 'production' : 'development';
      } catch (error) {
        console.error(error);
      }
    },
    set(initialState) {
      this.initialState.allowSignUp = initialState.allowSignUp;
      this.initialState.maxUploadFileSize = initialState.maxUploadFileSize;
      this.initialState.session.id = initialState.session.id;
      this.initialState.session.email = initialState.session.email;
      this.initialState.environment = initialState.environment == "production" ? 'production' : 'development';
    },
  },
});
