import { defineStore } from "pinia";
import { default as useBasil } from "basil.js";
import { api } from "boot/axios";

const localStorageBasilOptions = {
  namespace: "homedocs",
  storages: ["local", "cookie", "session", "memory"],
  storage: "local",
  expireDays: 3650,
};

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
      /*
      const basil = useBasil(localStorageBasilOptions);
      const initialState = basil.get("initialState");
      if (initialState) {
        this.initialState = initialState;
      }
      */
    },
    save(initialState) {
      const basil = useBasil(localStorageBasilOptions);
      basil.set("initialState", initialState);
    },
  },
});
