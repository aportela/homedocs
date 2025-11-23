import { defineStore } from "pinia";

import { useLocalStorage } from "src/composables/useLocalStorage";

const { jwt } = useLocalStorage();

export const useSessionStore = defineStore("session", {
  state: () => ({
    currentJWT: jwt.get() ?? null,
  }),
  getters: {
    isLogged: (state) => state.currentJWT !== null,
    getJWT: (state) => state.currentJWT,
  },
  actions: {
    setJWT(newJWT = null) {
      this.currentJWT = newJWT;
      if (this.currentJWT !== null) {
        jwt.set(this.currentJWT);
      } else {
        jwt.remove();
      }
    },
  },
});
