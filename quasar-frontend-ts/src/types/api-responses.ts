import { type AxiosResponse } from "axios";

interface DefaultAxiosResponse {
  data: AxiosResponse<any, any, {}>;
};

interface LoginResponse extends DefaultAxiosResponse {
};

interface RegisterResponse extends DefaultAxiosResponse {
};

interface UserProfileResponseData {
  id: string | null;
  email: string | null;
};

interface GetProfileResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    user: UserProfileResponseData;
  }
};

interface SetProfileResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    user: UserProfileResponseData;
  }
};

interface TagCloudResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    tags: string[];
  }
};

interface RecentDocumentResponseItem {
  id: string;
  updatedAtTimestamp: number;
  title: string;
  description: string | null;
  tags: string[];
  attachmentCount: number;
  noteCount: number;
};

interface RecentDocumentsResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    documents: RecentDocumentResponseItem[];
  }
};

interface DocumentAttachmentResponseItem {
  id: string;
  name: string;
  size: number;
  hash: string;
  createdAtTimestamp: number;
};

interface DocumentAttachmentsResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    attachments: DocumentAttachmentResponseItem[];
  }
};

export {
  type DefaultAxiosResponse,
  type LoginResponse,
  type RegisterResponse,
  type GetProfileResponse,
  type SetProfileResponse,
  type TagCloudResponse,
  type RecentDocumentResponseItem,
  type RecentDocumentsResponse,
  type DocumentAttachmentResponseItem,
  type DocumentAttachmentsResponse,
};
