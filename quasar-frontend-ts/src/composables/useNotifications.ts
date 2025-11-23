import { uid } from "quasar";
import { useBus } from "src/composables/useBus";
import { useFormatDates } from "src/composables/useFormatDates";
import { useLocalStorage } from "./useLocalStorage";

export interface NotificationMessage {
  id?: string;
  type: string;
  body: string;
  caption?: string | null;
}

// Tipado para listeners
export type EventCallback<T = any> = (payload: T) => void;

const { bus } = useBus();
const { currentTimestamp, fullDateTimeHuman } = useFormatDates();
const { dateTimeFormat } = useLocalStorage();

export function useNotifications() {
  const on = <T = any>(event: string, callback: EventCallback<T>): void => {
    bus.on(event, callback);
  };

  const once = <T = any>(event: string, callback: EventCallback<T>): void => {
    bus.once(event, callback);
  };

  const emit = (event: string, message: NotificationMessage): void => {
    const t = currentTimestamp();
    bus.emit(event, {
      id: message.id || uid(),
      timestamp: t,
      date: fullDateTimeHuman(t, dateTimeFormat.get()),
      type: message.type,
      body: message.body,
      caption: message.caption ?? null,
    });
  };

  const off = <T = any>(event: string, callback: EventCallback<T>): void => {
    bus.off(event, callback);
  };

  return { on, once, emit, off };
}
