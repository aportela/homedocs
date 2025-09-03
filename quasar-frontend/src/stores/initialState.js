import { defineStore } from "pinia";
import { api } from "boot/axios";

export const useInitialStateStore = defineStore("initialState", {
  state: () => ({
    initialState: {
      allowSignUp: false,
      maxUploadFileSize: 1,
    },
  }),
  getters: {
    isSignUpAllowed: (state) => state.initialState.allowSignUp,
    maxUploadFileSize: (state) => state.initialState.maxUploadFileSize,
  },
  actions: {
    async load() {
      try {
        const success = await api.common.initialState();
        this.initialState.allowSignUp = success.data.initialState.allowSignUp;
        this.initialState.maxUploadFileSize =
          success.data.initialState.maxUploadFileSize;
      } catch (error) {
        console.error(error.response);
      }
    },
  },
});
