import { boot } from "quasar/wrappers";
import { useDarkModeStore } from "src/stores/darkMode";

export default boot(() => {
  useDarkModeStore();
});
