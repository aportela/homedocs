import { LocalStorage } from "quasar";
import { LOCAL_STORAGE_NAMESPACE } from "src/constants";

export type StorageValue = string | number | boolean | null | object;

function createStorageEntry<T extends StorageValue>(
  key: string,
  defaultValue: T
) {
  return {
    get(): T {
      try {
        const stored = LocalStorage.getItem(LOCAL_STORAGE_NAMESPACE + key);
        return stored === null ? defaultValue : (stored as T);
      } catch (error) {
        console.error("Error accessing localStorage:", error);
        return defaultValue;
      }
    },
    set(value: T) {
      try {
        LocalStorage.set(
          LOCAL_STORAGE_NAMESPACE + key,
          value as unknown as StorageValue
        );
      } catch (error) {
        console.error("Error accessing localStorage:", error);
      }
    },
    remove() {
      try {
        LocalStorage.remove(LOCAL_STORAGE_NAMESPACE + key);
      } catch (error) {
        console.error("Error accessing localStorage:", error);
      }
    },
  };
}

export function useLocalStorage() {
  const authJWT = createStorageEntry<string | null>("authJWT", null);
  const email = createStorageEntry<string | null>("email", null);
  const darkMode = createStorageEntry<boolean>("darkMode", false);
  const locale = createStorageEntry<string | null>("locale", null);

  const alwaysOpenUploadDialog = createStorageEntry<boolean>(
    "alwaysOpenUploadDialog",
    true
  );

  const showToolTips = createStorageEntry<boolean>("showToolTips", false);

  const searchDialogResultsPage = createStorageEntry<number>(
    "searchDialogResultsPage",
    8
  );

  const dateFormat = createStorageEntry<string>(
    "dateFormat",
    "YYYY/MM/DD"
  );

  const dateTimeFormat = createStorageEntry<string>(
    "dateTimeFormat",
    "YYYY/MM/DD HH:mm:ss"
  );

  return {
    authJWT,
    email,
    darkMode,
    locale,
    alwaysOpenUploadDialog,
    showToolTips,
    searchDialogResultsPage,
    dateFormat,
    dateTimeFormat,
  };
}
