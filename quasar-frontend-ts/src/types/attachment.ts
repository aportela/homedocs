export interface Attachment {
  id: string;
  name: string;
  size: number | null;
  hash: string | null;
  humanSize: string | null;
  createdOnTimestamp: number;
  createdOn: string;
  createdOnTimeAgo: string;
  orphaned: boolean;
};
