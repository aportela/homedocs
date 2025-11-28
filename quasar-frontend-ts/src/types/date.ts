import { dateFormat as localStorageDateFormat } from "src/composables/localStorage";
import { dateHuman, timeAgo } from "src/composables/useFormatDates";
import { type Ti18NFunction } from "./i18n";

interface Date {
  timestamp: number | null,
  // TODO: refactor to formattedDate
  date: string | null,
  timeAgo: string | null,
};


class DateClass implements Date {
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
};

export { type Date, DateClass };
