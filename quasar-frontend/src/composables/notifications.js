import { uid, date, EventBus } from "quasar";

const bus = new EventBus();

export function useNotifications() {
  const on = (event, callback) => {
    bus.on(event, callback);
  };

  const once = (event, callback) => {
    bus.on(event, callback);
  };

  const emit = (event, message) => {
    const d = new Date();
    bus.emit(event, {
      id: message.id || uid(),
      timestamp: date.formatDate(d, "x"),
      date: date.formatDate(d, "YYYY-MM-DD HH:mm:ss"),
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
