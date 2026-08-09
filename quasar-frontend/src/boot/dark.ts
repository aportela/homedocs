import { defineBoot } from "#q-app";
import { useDarkModeStore } from "@/stores/darkMode";

export default defineBoot(() => {
  useDarkModeStore();
});
