import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

// Types
export interface FeedbackTicket {
  id: number;
  ticket_number: string;
  category: string;
  priority: string;
  subject: string;
  message: string;
  status: string;
  submitted_at: string;
  last_updated_at: string;
  resolved_at: string | null;
  has_new_response: boolean;
  attachments_count: number;
}

export interface FeedbackResponse {
  id: number;
  response_message: string;
  responded_by_name: string;
  responded_at: string;
}

export interface FeedbackDetails {
  ticket: FeedbackTicket;
  responses: FeedbackResponse[];
  attachments: Array<{
    id: number;
    file_name: string;
    file_size: number;
    uploaded_at: string;
  }>;
}

export interface FeedbackHistory {
  tickets: FeedbackTicket[];
  status_counts: {
    submitted: number;
    in_review: number;
    in_progress: number;
    resolved: number;
    closed: number;
  };
  total_count: number;
}

export interface FeedbackCategory {
  value: string;
  label: string;
  icon: string;
  description: string;
}

export interface FeedbackPriority {
  value: string;
  label: string;
  color: string;
}

// API Client
const getAuthHeader = () => {
  const token = localStorage.getItem('token');
  return {
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  };
};

export const feedbackApi = {
  // Submit feedback
  submitFeedback: async (data: FormData): Promise<{ ticket: FeedbackTicket }> => {
    const token = localStorage.getItem('token');
    const response = await axios.post(
      `${API_BASE_URL}/student/feedback/submit`,
      data,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data',
        },
      }
    );
    return response.data;
  },

  // Get feedback history
  getFeedbackHistory: async (): Promise<FeedbackHistory> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/feedback/history`,
      getAuthHeader()
    );
    return response.data;
  },

  // Get feedback details
  getFeedbackDetails: async (ticketId: number): Promise<FeedbackDetails> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/feedback/${ticketId}`,
      getAuthHeader()
    );
    return response.data;
  },

  // Upload attachment
  uploadAttachment: async (ticketId: number, file: File): Promise<any> => {
    const formData = new FormData();
    formData.append('attachment', file);

    const token = localStorage.getItem('token');
    const response = await axios.post(
      `${API_BASE_URL}/student/feedback/${ticketId}/attachment`,
      formData,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data',
        },
      }
    );
    return response.data;
  },

  // Get feedback categories
  getFeedbackCategories: async (): Promise<{
    categories: FeedbackCategory[];
    priorities: FeedbackPriority[];
  }> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/feedback/categories`,
      getAuthHeader()
    );
    return response.data;
  },

  // Mark as viewed
  markAsViewed: async (ticketId: number): Promise<void> => {
    await axios.put(
      `${API_BASE_URL}/student/feedback/${ticketId}/mark-viewed`,
      {},
      getAuthHeader()
    );
  },
};
