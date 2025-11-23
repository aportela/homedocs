import { uid } from "quasar";
import { useBus } from "src/composables/useBus";
import { useFormatDates } from "src/composables/useFormatDates";
import { useLocalStorage } from "./useLocalStorage";

const { bus } = useBus();
const { currentTimestamp, fullDateTimeHuman } = useFormatDates();
const { dateTimeFormat } = useLocalStorage();

export function useNotifications() {
  const on = (event, callback) => {
    bus.on(event, callback);
  };

  const once = (event, callback) => {
    bus.on(event, callback);
  };

  const emit = (event, message) => {
    const t = currentTimestamp();
    bus.emit(event, {
      id: message.id || uid(),
      timestamp: currentTimestamp(),
      date: fullDateTimeHuman(t, dateTimeFormat.get()),
      type: message.type,
      body: message.body,
      caption: message.caption || null,
    });
  };

  const off = (event, callback) => {
    bus.off(event, callback);
  };

  return { on, once, emit, off };
}
