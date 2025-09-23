import { useInitialStateStore } from "src/stores/initialState";

export default () => {
  const initialState = useInitialStateStore();
  initialState.load();
};
