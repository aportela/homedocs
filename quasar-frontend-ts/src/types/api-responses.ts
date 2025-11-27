import { type AxiosResponse } from "axios";
import { type HistoryOperationType } from "./history-operation";
import { type SearchDocumentItemMatchedFragment as SearchDocumentItemMatchedFragmentInterface } from "./search-document-item";

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

interface DocumentNoteResponseItem {
  id: string;
  body: string;
  createdAtTimestamp: number;
};

interface DocumentNoteResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    notes: DocumentNoteResponseItem[];
  }
};

interface DocumentHistoryOperationResponseItem {
  createdAtTimestamp: number;
  operationType: HistoryOperationType;
};

interface DocumentHistoryOperationResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    historyOperations: DocumentHistoryOperationResponseItem[];
  }
};

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
};

interface SearchDocumentResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    results: {
      pagination: PaginationResponse,
      documents: SearchDocumentResponseItem[];
    }
  }
}

interface GetTotalDocumentsStatsResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    count: number;
  }
};

interface GetTotalAttachmentsStatsResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    count: number;
  }
};

interface GetDiskUsageStatsResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    count: number;
  }
};

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
    }
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
};
