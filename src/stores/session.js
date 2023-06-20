import { defineStore } from "pinia";
import { default as useBasil } from "basil.js";

export const useSessionStore = defineStore("session", {
  state: () => ({
    jwt: null,
  }),

  getters: {
    isLogged: (state) => state.jwt != null,
    getJWT: (state) => state.jwt,
  },
  actions: {
    load() {
      const basil = useBasil();
      const jwt = basil.get("jwt");
      if (jwt) {
        this.jwt = jwt;
      }
    },
    save(jwt) {
      const basil = useBasil();
      basil.set("jwt", jwt);
    },
    signIn(jwt) {
      this.jwt = jwt;
      this.save(jwt);
    },
    signOut() {
      this.jwt = null;
      this.save(null);
    },
  },
});
