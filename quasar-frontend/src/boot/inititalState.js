import { boot } from 'quasar/wrappers'
import { useInitialStateStore } from "src/stores/initialState";

// "async" is optional;
// more info on params: https://v2.quasar.dev/quasar-cli/boot-files
export default boot(async (/* { app, router, ... } */) => {
  const initialState = useInitialStateStore();
  initialState.load();
})
