import { date } from 'quasar';

interface timeAgoUntilReturn {
  label: string;
  count: number | null;
}

const timeAgo = (timestamp: number): timeAgoUntilReturn => {
  const now = Date.now();
  const diff = now - new Date(timestamp).getTime();

  const seconds = Math.floor(diff / 1000);
  const minutes = Math.floor(seconds / 60);
  const hours = Math.floor(minutes / 60);
  const days = Math.floor(hours / 24);
  const months = Math.floor(days / 30); // WARNING: NOT EXACT (30 days / month)
  const years = Math.floor(days / 365); // WARNING: NOT EXACT (365 days / year)

  if (years > 0) {
    return { label: 'timeAgo.year', count: years };
  } else if (months > 0) {
    return { label: 'timeAgo.month', count: months };
  } else if (days > 0) {
    return { label: 'timeAgo.day', count: days };
  } else if (hours > 0) {
    return { label: 'timeAgo.hour', count: hours };
  } else if (minutes > 0) {
    return { label: 'timeAgo.minute', count: minutes };
  } else if (seconds > 0) {
    return { label: 'timeAgo.second', count: seconds };
  } else {
    return { label: 'timeAgo.now', count: null };
  }
};

const currentTimeAgo = (): string => {
  return 'timeAgo.now';
};

const timeUntil = (timestamp: number): timeAgoUntilReturn => {
  const now = Date.now();
  const diff = new Date(timestamp).getTime() - now;

  if (diff <= 0) return { label: 'timeUntil.now', count: null };

  const seconds = Math.floor(diff / 1000);
  const minutes = Math.floor(seconds / 60);
  const hours = Math.floor(minutes / 60);
  const days = Math.floor(hours / 24);
  const months = Math.floor(days / 30); // WARNING: NOT EXACT (30 days / month)
  const years = Math.floor(days / 365); // WARNING: NOT EXACT (365 days / year)

  if (years > 0) {
    return { label: 'timeUntil.year', count: years };
  } else if (months > 0) {
    return { label: 'timeUntil.month', count: months };
  } else if (days > 0) {
    return { label: 'timeUntil.day', count: days };
  } else if (hours > 0) {
    return { label: 'timeUntil.hour', count: hours };
  } else if (minutes > 0) {
    return { label: 'timeUntil.minute', count: minutes };
  } else {
    return { label: 'timeUntil.second', count: seconds };
  }
};

const timestamp = (dateObj: Date): number => {
  return Number(date.formatDate(dateObj, 'x'));
};

const currentTimestamp = (): number => {
  return Number(timestamp(new Date()));
};

const dateHuman = (timestamp: number, format: string = 'YYYY/MM/DD'): string => {
  return date.formatDate(timestamp, format);
};

const currentDateHuman = (format: string = 'YYYY/MM/DD'): string => {
  return dateHuman(currentTimestamp(), format);
};

const fullDateTimeHuman = (timestamp: number, format: string = 'YYYY/MM/DD HH:mm:ss'): string => {
  return date.formatDate(timestamp, format);
};

const currentFullDateTimeHuman = (format: string = 'YYYY/MM/DD HH:mm:ss'): string => {
  return fullDateTimeHuman(currentTimestamp(), format);
};

export {
  timeAgo,
  currentTimeAgo,
  timeUntil,
  timestamp,
  currentTimestamp,
  dateHuman,
  currentDateHuman,
  fullDateTimeHuman,
  currentFullDateTimeHuman,
};
