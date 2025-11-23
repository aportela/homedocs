export interface APIErrorDetails {
  method: string;
  url: string;
  httpCode: number | string;
  httpStatus: string;
  request?: {
    params?: {
      query?: any | null;
      data?: any | null;
    };
  };
}
