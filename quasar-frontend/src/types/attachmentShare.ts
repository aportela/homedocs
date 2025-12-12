interface AttachmentShare {
  id: string;
  createdAtTimestamp: number;
  expiresAtTimestamp: number;
  lastAccessTimestamp: number | null;
  accessLimit: number;
  accessCount: number;
  enabled: boolean;
}

export { type AttachmentShare };
