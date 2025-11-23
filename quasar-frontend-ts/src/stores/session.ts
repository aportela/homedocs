import { defineStore } from "pinia";

import { useLocalStorage } from "src/composables/useLocalStorage";

const { authJWT } = useLocalStorage();

export const useSessionStore = defineStore("session", {
  state: () => ({
    _authJWT: authJWT.get() ?? null,
  }),
  getters: {
    isLogged: (state) => state._authJWT !== null,
    jwt: (state) => state._authJWT,
  },
  actions: {
    setJWT(jwt = null) {
      this._authJWT = jwt;
      if (this._authJWT !== null) {
        authJWT.set(this._authJWT);
      } else {
        authJWT.remove();
      }
    },
  },
});
