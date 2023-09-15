import { defineStore } from "pinia";
import { default as useBasil } from "basil.js";

const localStorageBasilOptions = {
  namespace: "homedocs",
  storages: ["local", "cookie", "session", "memory"],
  storage: "local",
  expireDays: 3650,
};

export const useSessionStore = defineStore("session", {
  state: () => ({
    loaded: false,
    jwt: null,
    locale: null,
  }),

  getters: {
    isLoaded: (state) => state.loaded,
    isLogged: (state) => state.jwt != null,
    getJWT: (state) => state.jwt,
    getLocale: (state) => state.locale,
  },
  actions: {
    load() {
      const basil = useBasil(localStorageBasilOptions);
      const jwt = basil.get("jwt");
      if (jwt) {
        this.jwt = jwt;
      }
      const locale = basil.get("locale");
      if (locale) {
        this.locale = locale;
      }
      this.loaded = true;
    },
    saveLocale(locale) {
      this.locale = locale;
      const basil = useBasil(localStorageBasilOptions);
      basil.set("locale", locale);
    },
    save(jwt) {
      const basil = useBasil(localStorageBasilOptions);
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
