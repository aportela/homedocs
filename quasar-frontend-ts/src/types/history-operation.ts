interface HistoryOperation {
  operationType: number;
  label: string;
  icon: string;
  createdOnTimestamp: number;
  createdOn: string;
  createdOnTimeAgo: string;
};

export { type HistoryOperation };
