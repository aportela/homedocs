import { type AxiosResponse } from 'axios';
import { type HistoryOperationType as HistoryOperationTypeInterface } from './historyOperation';
import { type AttachmentShare as AttachmentShareInterface } from './attachmentShare';
import { type SearchDocumentItemMatchedFragment as SearchDocumentItemMatchedFragmentInterface } from './searchDocumentItem';
import { type EnvironmentType, type ValidAuthTypes } from './common';

interface DefaultAxiosResponse<T = unknown> {
  data: AxiosResponse<T>;
}

interface LoginResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    accessToken: {
      token: string;
      expiresAtTimestamp: number;
    };
    refreshToken: {
      token: string;
      expiresAtTimestamp: number;
    };
    tokenType: ValidAuthTypes;
  };
}

interface GetNewAccessTokenResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    accessToken: {
      token: string;
      expiresAtTimestamp: number;
    };
    tokenType: ValidAuthTypes;
  };
}

interface RegisterResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: undefined;
}

interface UserProfileResponseData {
  id: string | null;
  email: string;
}

interface GetProfileResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    user: UserProfileResponseData;
  };
}

interface SetProfileResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    user: UserProfileResponseData;
  };
}

interface TagCloudResponseItem {
  total: number;
  tag: string;
}

interface TagCloudResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    tags: TagCloudResponseItem[];
  };
}

interface RecentDocumentResponseItem {
  id: string;
  updatedAtTimestamp: number;
  title: string;
  description: string | null;
  tags: string[];
  attachmentCount: number;
  noteCount: number;
}

interface RecentDocumentsResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    documents: RecentDocumentResponseItem[];
  };
}

interface DocumentAttachmentResponseItem {
  id: string;
  name: string;
  size: number;
  hash: string;
  createdAtTimestamp: number;
  shared: boolean;
}

interface DocumentAttachmentsResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    attachments: DocumentAttachmentResponseItem[];
  };
}

interface DocumentNoteResponseItem {
  id: string;
  body: string;
  createdAtTimestamp: number;
}

interface DocumentNoteResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    notes: DocumentNoteResponseItem[];
  };
}

interface DocumentHistoryOperationResponseItem {
  createdAtTimestamp: number;
  operationType: HistoryOperationTypeInterface;
}

interface DocumentHistoryOperationResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    historyOperations: DocumentHistoryOperationResponseItem[];
  };
}

interface PaginationResponse {
  currentPage: number;
  resultsPage: number;
  totalResults: number;
  totalPages: number;
}

interface SearchDocumentResponseItem {
  id: string;
  createdAtTimestamp: number;
  updatedAtTimestamp: number | null;
  title: string;
  description: string | null;
  tags: string[];
  attachmentCount: number;
  noteCount: number;
  matchedFragments: SearchDocumentItemMatchedFragmentInterface[];
}

interface SearchDocumentResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    results: {
      pagination: PaginationResponse;
      documents: SearchDocumentResponseItem[];
    };
  };
}

interface GetTotalDocumentsStatsResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    count: number;
  };
}

interface GetTotalAttachmentsStatsResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    count: number;
  };
}

interface GetDiskUsageStatsResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    size: number;
  };
}

interface GetDocumentResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    document: {
      id: string;
      title: string;
      description: string | null;
      createdAtTimestamp: number;
      updatedAtTimestamp: number | null;
      tags: string[];
      attachments: DocumentAttachmentResponseItem[];
      notes: DocumentNoteResponseItem[];
      historyOperations: DocumentHistoryOperationResponseItem[];
    };
  };
}

interface GetTagsResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    tags: string[];
  };
}

interface GetActivityHeatMapDataResponseItem {
  date: string;
  count: number;
}

interface GetActivityHeatMapDataResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    heatmap: GetActivityHeatMapDataResponseItem[];
  };
}

interface CreateUpdateGetAttachmentShareResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    attachmentShare: AttachmentShareInterface;
  };
}

interface SearchAttachmentShareResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    pager?: PaginationResponse | null;
    sharedAttachments: AttachmentShareInterface[];
  };
}

interface getServerEnvironmentResponseData {
  data: {
    serverEnvironment: {
      allowSignUp: boolean;
      environment: EnvironmentType;
      maxUploadFileSize: number;
    };
  };
}

export {
  type DefaultAxiosResponse,
  type LoginResponse,
  type GetNewAccessTokenResponse,
  type RegisterResponse,
  type GetProfileResponse,
  type SetProfileResponse,
  type TagCloudResponseItem,
  type TagCloudResponse,
  type RecentDocumentResponseItem,
  type RecentDocumentsResponse,
  type DocumentAttachmentResponseItem,
  type DocumentAttachmentsResponse,
  type DocumentNoteResponseItem,
  type DocumentNoteResponse,
  type DocumentHistoryOperationResponseItem,
  type DocumentHistoryOperationResponse,
  type PaginationResponse,
  type SearchDocumentResponseItem,
  type SearchDocumentResponse,
  type GetTotalDocumentsStatsResponse,
  type GetTotalAttachmentsStatsResponse,
  type GetDiskUsageStatsResponse,
  type GetDocumentResponse,
  type GetTagsResponse,
  type GetActivityHeatMapDataResponseItem,
  type GetActivityHeatMapDataResponse,
  type CreateUpdateGetAttachmentShareResponse,
  type SearchAttachmentShareResponse,
  type getServerEnvironmentResponseData,
};
