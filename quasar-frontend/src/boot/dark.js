import { Dark, LocalStorage } from "quasar";

export default () => {
  const savedMode = LocalStorage.getItem("darkMode");
  if (savedMode === true) {
    Dark.set(true);
  } else if (savedMode === false) {
    Dark.set(false);
  } else {
    Dark.set("auto");
  }
};
