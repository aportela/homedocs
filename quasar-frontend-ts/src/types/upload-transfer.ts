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

class UploadTransferClass implements UploadTransfer {
  id: string;
  filename: string;
  filesize: number;
  start: number;
  end: number | null;
  uploading: boolean;
  done: boolean;
  error: boolean;
  errorHTTPCode: number | null;
  errorMessage: string | null;
  processed: boolean;

  constructor(id: string, filename: string, filesize: number, start: number, end: number | null, uploading: boolean, done: boolean, error: boolean, errorHTTPCode: number | null, errorMessage: string | null, processed: boolean) {
    this.id = id;
    this.filename = filename;
    this.filesize = filesize;
    this.start = start;
    this.end = end;
    this.uploading = uploading;
    this.done = done;
    this.error = error;
    this.errorHTTPCode = errorHTTPCode;
    this.errorMessage = errorMessage;
    this.processed = processed;
  }
};

export { type UploadTransfer, UploadTransferClass };
