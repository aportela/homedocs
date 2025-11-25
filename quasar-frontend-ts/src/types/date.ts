import { dateFormat as localStorageDateFormat } from "src/composables/useLocalStorage";
import { dateHuman, timeAgo } from "src/composables/useFormatDates";

export interface Date {
  timestamp: number | null,
  // TODO: refactor to formattedDate
  date: string | null,
  timeAgo: string | null,
};

type Ti18NFunction = (key: string, values?: Record<string, string | number | boolean>) => string;

export class DateClass implements Date {
  timestamp: number | null;
  date: string | null;
  timeAgo: string | null;

  constructor(t: Ti18NFunction, timestamp: number | null) {
    if (timestamp) {
      this.timestamp = timestamp;
      this.date = dateHuman(timestamp, localStorageDateFormat.get());
      const returnedTimeAgo = timeAgo(timestamp);
      this.timeAgo = t(returnedTimeAgo.label, returnedTimeAgo.count != null ? { count: returnedTimeAgo.count } : {});
    } else {
      this.timestamp = null;
      this.date = null;
      this.timeAgo = null;
    }
  }
}
