import { defineStore } from "pinia";

import { useLocalStorage } from "src/composables/useLocalStorage";

const { authJWT: localStorageJWT } = useLocalStorage();

export const useSessionStore = defineStore("session", {
  state: () => ({
    currentJWT: localStorageJWT.get() ?? null,
  }),
  getters: {
    isLogged: (state) => state.currentJWT !== null,
    jwt: (state) => state.currentJWT,
  },
  actions: {
    setJWT(jwt = null) {
      this.currentJWT = jwt;
      if (this.currentJWT !== null) {
        localStorageJWT.set(this.currentJWT);
      } else {
        localStorageJWT.remove();
      }
    },
  },
});
