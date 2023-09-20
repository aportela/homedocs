import { defineStore } from "pinia";
import { api } from "boot/axios";

const hashedSite = Array.from(window.location.host).reduce(
  (hash, char) => 0 | (31 * hash + char.charCodeAt(0)),
  0
);

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
    load() {
      api.common
        .initialState()
        .then((success) => {
          this.initialState.allowSignUp = success.data.initialState.allowSignUp;
          this.initialState.maxUploadFileSize =
            success.data.initialState.maxUploadFileSize;
        })
        .catch((error) => {
          console.error(error.response);
        });
    },
  },
});
