import { LocalStorage } from "quasar";

import { LOCAL_STORAGE_NAMESPACE } from "src/constants";

export function useLocalStorage() {
  const get = (key, defaultValue = null) => {
    try {
      const savedValue = LocalStorage.getItem(LOCAL_STORAGE_NAMESPACE + key);
      return savedValue === null ? defaultValue : savedValue;
    } catch (error) {
      console.error("Error accessing localStorage:", error);
      return defaultValue;
    }
  };

  const set = (key, value) => {
    try {
      LocalStorage.setItem(LOCAL_STORAGE_NAMESPACE + key, value);
    } catch (error) {
      console.error("Error accessing localStorage:", error);
    }
  };

  const remove = (key) => {
    try {
      LocalStorage.removeItem(LOCAL_STORAGE_NAMESPACE + key);
    } catch (error) {
      console.error("Error accessing localStorage:", error);
    }
  };

  const jwt = {
    get() {
      return get("jwt");
    },
    set(value) {
      set("jwt", value);
    },
    remove() {
      remove("jwt");
    },
  };

  const email = {
    get() {
      return get("email");
    },
    set(value) {
      set("email", value);
    },
    remove() {
      remove("email");
    },
  };

  const darkMode = {
    get() {
      return get("darkMode");
    },
    set(value) {
      set("darkMode", !!value);
    },
    remove() {
      remove("darkMode");
    },
  };

  const locale = {
    get() {
      return get("locale");
    },
    set(value) {
      set("locale", value);
    },
    remove() {
      remove("locale");
    },
  };

  const alwaysOpenUploadDialog = {
    get() {
      return get("alwaysOpenUploadDialog", true);
    },
    set(value) {
      set("alwaysOpenUploadDialog", !!value);
    },
    remove() {
      remove("alwaysOpenUploadDialog");
    },
  };

  const showToolTips = {
    get() {
      return get("showToolTips", false);
    },
    set(value) {
      set("showToolTips", !!value);
    },
    remove() {
      remove("showToolTips");
    },
  };

  const searchDialogResultsPage = {
    get() {
      return get("searchDialogResultsPage", 8);
    },
    set(value) {
      set("searchDialogResultsPage", parseInt(value || 8));
    },
    remove() {
      remove("searchDialogResultsPage");
    },
  };

  return {
    jwt,
    email,
    darkMode,
    locale,
    alwaysOpenUploadDialog,
    showToolTips,
    searchDialogResultsPage,
  };
}
