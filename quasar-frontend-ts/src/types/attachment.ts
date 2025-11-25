export interface Attachment {
  id: string;
  name: string;
  size: number;
  hash: string;
  humanSize: string;
  createdOnTimestamp: number;
  createdOn: string;
  createdOnTimeAgo: string;
  orphaned: boolean;
};
