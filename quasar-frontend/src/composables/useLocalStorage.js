import { LocalStorage } from "quasar";

export function useLocalStorage() {
  const get = (key, defaultValue = null) => {
    try {
      const savedValue = LocalStorage.getItem(key);
      return savedValue === null ? defaultValue : savedValue;
    } catch (error) {
      console.error("Error accessing localStorage:", error);
      return defaultValue;
    }
  };

  const set = (key, value) => {
    try {
      LocalStorage.setItem(key, value);
    } catch (error) {
      console.error("Error accessing localStorage:", error);
    }
  };

  const remove = (key) => {
    try {
      LocalStorage.removeItem(key);
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
    }
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
    }
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
    }
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
    }
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
    }
  };

  return {
    jwt,
    darkMode,
    alwaysOpenUploadDialog,
    showToolTips,
    searchDialogResultsPage,
  };
}
