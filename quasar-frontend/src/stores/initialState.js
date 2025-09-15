import { defineStore } from "pinia";
import { api } from "boot/axios";

export const useInitialStateStore = defineStore("initialState", {
  state: () => ({
    initialState: {
      allowSignUp: false,
      maxUploadFileSize: 1,
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
  },
  actions: {
    async load() {
      try {
        const success = await api.common.initialState();
        this.initialState.allowSignUp = success.data.initialState.allowSignUp;
        this.initialState.maxUploadFileSize =
          success.data.initialState.maxUploadFileSize;
        this.session.id = success.data.initialState.session.id;
        this.session.email = success.data.initialState.session.email;
      } catch (error) {
        console.error(error.response);
      }
    },
    set(initialState) {
      console.log("setting ", initialState);
      this.initialState.allowSignUp = initialState.allowSignUp;
      this.initialState.maxUploadFileSize = initialState.maxUploadFileSize;
      this.session.id = initialState.session.id;
      this.session.email = initialState.session.email;
    },
  },
});
