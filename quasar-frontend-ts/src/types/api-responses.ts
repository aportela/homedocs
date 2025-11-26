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

export { type DefaultAxiosResponse, type LoginResponse, type RegisterResponse, type GetProfileResponse, type SetProfileResponse, type TagCloudResponse, type RecentDocumentsResponse, type RecentDocumentResponseItem };
