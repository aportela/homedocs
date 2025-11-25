import { type Date as DateInterface } from "./date";
import { dateFormat as localStorageDateFormat, dateTimeFormat as localStorageDateTimeFormat } from "src/composables/useLocalStorage";
import { dateHuman, fullDateTimeHuman, timeAgo } from "src/composables/useFormatDates";

export interface DateTime extends DateInterface {
  dateTime: string | null,
};

type Ti18NFunction = (key: string, values?: Record<string, string | number | boolean>) => string;

export class DateTimeClass implements DateTime {
  timestamp: number | null;
  // TODO: refactor to formattedDate
  date: string | null;
  // TODO: refactor to formattedDateTime
  dateTime: string | null;
  timeAgo: string | null;

  constructor(t: Ti18NFunction, timestamp: number | null) {
    if (timestamp) {
      this.timestamp = timestamp;
      this.date = dateHuman(timestamp, localStorageDateFormat.get());
      this.dateTime = fullDateTimeHuman(timestamp, localStorageDateTimeFormat.get());
      const returnedTimeAgo = timeAgo(timestamp);
      this.timeAgo = t(returnedTimeAgo.label, returnedTimeAgo.count != null ? { count: returnedTimeAgo.count } : {});
    } else {
      this.timestamp = null;
      this.date = null;
      this.dateTime = null;
      this.timeAgo = null;
    }
  }
}
