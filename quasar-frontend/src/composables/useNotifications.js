import { uid } from "quasar";
import { useBus } from "src/composables/useBus";
import { useFormatDates } from "src/composables/useFormatDates";

const { bus } = new useBus();
const { currentTimestamp, fullDateTimeHuman } = new useFormatDates();

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
      date: fullDateTimeHuman(t),
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
