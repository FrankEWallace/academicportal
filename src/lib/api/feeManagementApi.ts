import apiClient from './apiClient';

export interface FeeStructure {
  id: number;
  program: string;
  semester: number;
  amount: number;
  due_date: string;
  fee_type: string;
  description?: string;
  status: 'active' | 'inactive';
  created_at: string;
  updated_at: string;
}

export interface Invoice {
  id: number;
  invoice_number: string;
  student_id: number;
  student_name?: string;
  matric_number?: string;
  fee_structure_id: number;
  amount_due: number;
  amount_paid: number;
  balance: number;
  due_date: string;
  issued_date: string;
  status: 'pending' | 'partial' | 'paid' | 'overdue' | 'cancelled';
  description?: string;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface Payment {
  id: number;
  invoice_id: number;
  student_id: number;
  amount: number;
  payment_method: string;
  transaction_reference: string;
  payment_date: string;
  verified_by?: number;
  verification_date?: string;
  status: 'pending' | 'verified' | 'rejected';
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface FeeStatistics {
  total_revenue: number;
  pending_payments: number;
  overdue_invoices: number;
  total_students: number;
  collection_rate: number;
  overdue_amount: number;
  paid_invoices: number;
  partial_invoices: number;
}

export const feeManagementApi = {
  // Fee Structures
  async getFeeStructures(params?: {
    program?: string;
    semester?: number;
    status?: 'active' | 'inactive';
    fee_type?: string;
  }) {
    const response = await apiClient.get('/admin/fee-structures', { params });
    return response.data;
  },

  async getFeeStructure(id: number) {
    const response = await apiClient.get(`/admin/fee-structures/${id}`);
    return response.data;
  },

  async createFeeStructure(data: {
    program: string;
    semester: number;
    amount: number;
    due_date: string;
    fee_type: string;
    description?: string;
    status?: 'active' | 'inactive';
  }) {
    const response = await apiClient.post('/admin/fee-structures', data);
    return response.data;
  },

  async updateFeeStructure(id: number, data: Partial<FeeStructure>) {
    const response = await apiClient.put(`/admin/fee-structures/${id}`, data);
    return response.data;
  },

  async deleteFeeStructure(id: number) {
    const response = await apiClient.delete(`/admin/fee-structures/${id}`);
    return response.data;
  },

  // Invoices
  async getInvoices(params?: {
    student_id?: number;
    status?: string;
    program?: string;
    semester?: number;
  }) {
    const response = await apiClient.get('/admin/invoices', { params });
    return response.data;
  },

  async getInvoice(id: number) {
    const response = await apiClient.get(`/admin/invoices/${id}`);
    return response.data;
  },

  async createInvoice(data: {
    student_id: number;
    fee_structure_id: number;
    amount_due: number;
    due_date: string;
    description?: string;
  }) {
    const response = await apiClient.post('/admin/invoices', data);
    return response.data;
  },

  async updateInvoice(id: number, data: Partial<Invoice>) {
    const response = await apiClient.put(`/admin/invoices/${id}`, data);
    return response.data;
  },

  async cancelInvoice(id: number, reason: string) {
    const response = await apiClient.post(`/admin/invoices/${id}/cancel`, { reason });
    return response.data;
  },

  async getOverdueInvoices() {
    const response = await apiClient.get('/admin/invoices/overdue');
    return response.data;
  },

  // Payments
  async getPayments(params?: {
    invoice_id?: number;
    student_id?: number;
    status?: string;
  }) {
    const response = await apiClient.get('/admin/payments', { params });
    return response.data;
  },

  async getPayment(id: number) {
    const response = await apiClient.get(`/admin/payments/${id}`);
    return response.data;
  },

  async recordPayment(data: {
    invoice_id: number;
    student_id: number;
    amount: number;
    payment_method: string;
    transaction_reference: string;
    payment_date: string;
    notes?: string;
  }) {
    const response = await apiClient.post('/admin/payments', data);
    return response.data;
  },

  async verifyPayment(id: number) {
    const response = await apiClient.post(`/admin/payments/${id}/verify`);
    return response.data;
  },

  async rejectPayment(id: number, reason: string) {
    const response = await apiClient.post(`/admin/payments/${id}/reject`, { reason });
    return response.data;
  },

  // Statistics
  async getStatistics() {
    const response = await apiClient.get('/admin/fees/statistics');
    return response.data;
  },

  async getRevenueReport(params?: {
    start_date?: string;
    end_date?: string;
    program?: string;
  }) {
    const response = await apiClient.get('/admin/fees/revenue-report', { params });
    return response.data;
  },

  async getDefaultersList() {
    const response = await apiClient.get('/admin/fees/defaulters');
    return response.data;
  },

  // Bulk Operations
  async generateInvoicesForSemester(data: {
    program: string;
    semester: number;
    academic_year: string;
  }) {
    const response = await apiClient.post('/admin/invoices/generate-bulk', data);
    return response.data;
  },

  async sendPaymentReminders(invoice_ids: number[]) {
    const response = await apiClient.post('/admin/invoices/send-reminders', { invoice_ids });
    return response.data;
  }
};
