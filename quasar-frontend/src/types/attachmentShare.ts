interface AttachmentBase {
  id: string;
  name: string;
  size: number;
}

interface DocumentBase {
  id: string;
  title: string;
}

interface AttachmentShare {
  id: string;
  createdAtTimestamp: number;
  expiresAtTimestamp: number;
  lastAccessTimestamp: number | null;
  accessLimit: number;
  accessCount: number;
  enabled: boolean;
  attachment: AttachmentBase;
  document: DocumentBase;
}

export { type AttachmentShare };
