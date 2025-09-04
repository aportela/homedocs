import { useInitialStateStore } from "stores/initialState";

export default () => {
  const initialState = useInitialStateStore();
  initialState.load();
};
