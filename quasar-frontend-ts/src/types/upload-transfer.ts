interface UploadTransfer {
  id: string;
  filename: string;
  filesize: number;
  start: number;
  end: number | null,
  uploading: boolean,
  done: boolean,
  error: boolean,
  errorHTTPCode: number | null,
  errorMessage: string | null,
  processed: boolean,
};

export { type UploadTransfer };
