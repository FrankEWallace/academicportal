import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Plus, CheckCircle, XCircle, Eye } from 'lucide-react';
import { feeManagementApi, Payment } from '@/lib/api/feeManagementApi';
import { useToast } from '@/hooks/use-toast';

interface PaymentsTabProps {
  onUpdate?: () => void;
}

export default function PaymentsTab({ onUpdate }: PaymentsTabProps) {
  const [payments, setPayments] = useState<Payment[]>([]);
  const [loading, setLoading] = useState(false);
  const [showRecordDialog, setShowRecordDialog] = useState(false);
  const [showViewDialog, setShowViewDialog] = useState(false);
  const [selectedPayment, setSelectedPayment] = useState<Payment | null>(null);
  const [statusFilter, setStatusFilter] = useState<string>('all');

  const [formData, setFormData] = useState({
    invoice_id: '',
    student_id: '',
    amount: '',
    payment_method: 'cash',
    transaction_reference: '',
    payment_date: new Date().toISOString().split('T')[0],
    notes: ''
  });

  const { toast } = useToast();

  useEffect(() => {
    fetchPayments();
  }, [statusFilter]);

  const fetchPayments = async () => {
    setLoading(true);
    try {
      const params = statusFilter !== 'all' ? { status: statusFilter } : undefined;
      const response = await feeManagementApi.getPayments(params);
      if (response.success) {
        const data = Array.isArray(response.data) ? response.data : (response.data?.data || []);
        setPayments(data);
      }
    } catch (error: any) {
      console.error('Failed to fetch payments:', error);
      toast({
        title: 'Error',
        description: error.message || 'Failed to fetch payments',
        variant: 'destructive',
      });
      setPayments([]);
    } finally {
      setLoading(false);
    }
  };

  const handleRecordPayment = async () => {
    try {
      const data = {
        invoice_id: parseInt(formData.invoice_id),
        student_id: parseInt(formData.student_id),
        amount: parseFloat(formData.amount),
        payment_method: formData.payment_method,
        transaction_reference: formData.transaction_reference || undefined,
        payment_date: formData.payment_date,
        notes: formData.notes || undefined
      };

      const response = await feeManagementApi.recordPayment(data);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Payment recorded successfully',
        });
        setShowRecordDialog(false);
        resetForm();
        fetchPayments();
        onUpdate?.();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to record payment',
        variant: 'destructive',
      });
    }
  };

  const handleVerifyPayment = async (paymentId: number) => {
    try {
      const response = await feeManagementApi.verifyPayment(paymentId);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Payment verified successfully',
        });
        fetchPayments();
        onUpdate?.();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to verify payment',
        variant: 'destructive',
      });
    }
  };

  const handleRejectPayment = async (paymentId: number, reason: string) => {
    try {
      const response = await feeManagementApi.rejectPayment(paymentId, reason);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Payment rejected',
        });
        fetchPayments();
        onUpdate?.();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to reject payment',
        variant: 'destructive',
      });
    }
  };

  const resetForm = () => {
    setFormData({
      invoice_id: '',
      student_id: '',
      amount: '',
      payment_method: 'cash',
      transaction_reference: '',
      payment_date: new Date().toISOString().split('T')[0],
      notes: ''
    });
  };

  const getStatusBadge = (status: string) => {
    const statusConfig: Record<string, { variant: any; label: string }> = {
      pending: { variant: 'secondary', label: 'Pending' },
      verified: { variant: 'default', label: 'Verified' },
      rejected: { variant: 'destructive', label: 'Rejected' }
    };

    const config = statusConfig[status] || statusConfig.pending;
    return <Badge variant={config.variant}>{config.label}</Badge>;
  };

  const getPaymentMethodBadge = (method: string) => {
    return <Badge variant="outline" className="capitalize">{method}</Badge>;
  };

  return (
    <Card>
      <CardHeader>
        <div className="flex justify-between items-center">
          <div>
            <CardTitle>Payments</CardTitle>
            <CardDescription>Record and verify student payments</CardDescription>
          </div>
          <div className="flex gap-2">
            <Select value={statusFilter} onValueChange={setStatusFilter}>
              <SelectTrigger className="w-40">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Status</SelectItem>
                <SelectItem value="pending">Pending</SelectItem>
                <SelectItem value="verified">Verified</SelectItem>
                <SelectItem value="rejected">Rejected</SelectItem>
              </SelectContent>
            </Select>
            <Dialog open={showRecordDialog} onOpenChange={setShowRecordDialog}>
              <DialogTrigger asChild>
                <Button onClick={() => { resetForm(); setShowRecordDialog(true); }}>
                  <Plus className="mr-2 h-4 w-4" />
                  Record Payment
                </Button>
              </DialogTrigger>
              <DialogContent className="max-w-2xl">
                <DialogHeader>
                  <DialogTitle>Record Payment</DialogTitle>
                  <DialogDescription>Record a new payment transaction</DialogDescription>
                </DialogHeader>
                <PaymentForm
                  formData={formData}
                  setFormData={setFormData}
                  onSubmit={handleRecordPayment}
                  onCancel={() => setShowRecordDialog(false)}
                />
              </DialogContent>
            </Dialog>
          </div>
        </div>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Transaction Ref</TableHead>
              <TableHead>Invoice ID</TableHead>
              <TableHead>Amount</TableHead>
              <TableHead>Method</TableHead>
              <TableHead>Payment Date</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {loading ? (
              <TableRow>
                <TableCell colSpan={7} className="text-center py-8">
                  Loading payments...
                </TableCell>
              </TableRow>
            ) : payments.length === 0 ? (
              <TableRow>
                <TableCell colSpan={7} className="text-center text-muted-foreground">
                  No payments found
                </TableCell>
              </TableRow>
            ) : (
              payments.map((payment) => (
                <TableRow key={payment.id}>
                  <TableCell className="font-medium">
                    {payment.transaction_reference || 'N/A'}
                  </TableCell>
                  <TableCell>{payment.invoice_id}</TableCell>
                  <TableCell className="font-semibold">
                    ${payment.amount.toLocaleString()}
                  </TableCell>
                  <TableCell>{getPaymentMethodBadge(payment.payment_method)}</TableCell>
                  <TableCell>{new Date(payment.payment_date).toLocaleDateString()}</TableCell>
                  <TableCell>{getStatusBadge(payment.status)}</TableCell>
                  <TableCell>
                    <div className="flex gap-2">
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => {
                          setSelectedPayment(payment);
                          setShowViewDialog(true);
                        }}
                      >
                        <Eye className="h-3 w-3" />
                      </Button>
                      {payment.status === 'pending' && (
                        <>
                          <Button
                            size="sm"
                            variant="default"
                            onClick={() => handleVerifyPayment(payment.id)}
                          >
                            <CheckCircle className="h-3 w-3 mr-1" />
                            Verify
                          </Button>
                          <Button
                            size="sm"
                            variant="destructive"
                            onClick={() => {
                              const reason = prompt('Enter rejection reason:');
                              if (reason) handleRejectPayment(payment.id, reason);
                            }}
                          >
                            <XCircle className="h-3 w-3 mr-1" />
                            Reject
                          </Button>
                        </>
                      )}
                    </div>
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </CardContent>

      {/* View Payment Dialog */}
      <Dialog open={showViewDialog} onOpenChange={setShowViewDialog}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Payment Details</DialogTitle>
            <DialogDescription>View payment information</DialogDescription>
          </DialogHeader>
          {selectedPayment && (
            <div className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-muted-foreground">Transaction Reference</Label>
                  <p className="font-medium">
                    {selectedPayment.transaction_reference || 'N/A'}
                  </p>
                </div>
                <div>
                  <Label className="text-muted-foreground">Status</Label>
                  <div className="mt-1">{getStatusBadge(selectedPayment.status)}</div>
                </div>
                <div>
                  <Label className="text-muted-foreground">Invoice ID</Label>
                  <p className="font-medium">{selectedPayment.invoice_id}</p>
                </div>
                <div>
                  <Label className="text-muted-foreground">Amount</Label>
                  <p className="font-semibold text-lg">
                    ${selectedPayment.amount.toLocaleString()}
                  </p>
                </div>
                <div>
                  <Label className="text-muted-foreground">Payment Method</Label>
                  <div className="mt-1">{getPaymentMethodBadge(selectedPayment.payment_method)}</div>
                </div>
                <div>
                  <Label className="text-muted-foreground">Payment Date</Label>
                  <p className="font-medium">
                    {new Date(selectedPayment.payment_date).toLocaleDateString()}
                  </p>
                </div>
                {selectedPayment.verified_by && (
                  <div>
                    <Label className="text-muted-foreground">Verified By</Label>
                    <p className="font-medium">{selectedPayment.verified_by}</p>
                  </div>
                )}
              </div>
              {selectedPayment.notes && (
                <div>
                  <Label className="text-muted-foreground">Notes</Label>
                  <p className="text-sm mt-1">{selectedPayment.notes}</p>
                </div>
              )}
            </div>
          )}
        </DialogContent>
      </Dialog>
    </Card>
  );
}

// Payment Form Component
function PaymentForm({
  formData,
  setFormData,
  onSubmit,
  onCancel
}: {
  formData: any;
  setFormData: (data: any) => void;
  onSubmit: () => void;
  onCancel: () => void;
}) {
  return (
    <div className="space-y-4">
      <div className="grid grid-cols-2 gap-4">
        <div className="space-y-2">
          <Label>Invoice ID *</Label>
          <Input
            type="number"
            value={formData.invoice_id}
            onChange={(e) => setFormData({ ...formData, invoice_id: e.target.value })}
            placeholder="12345"
          />
        </div>
        <div className="space-y-2">
          <Label>Student ID *</Label>
          <Input
            type="number"
            value={formData.student_id}
            onChange={(e) => setFormData({ ...formData, student_id: e.target.value })}
            placeholder="67890"
          />
        </div>
      </div>

      <div className="grid grid-cols-2 gap-4">
        <div className="space-y-2">
          <Label>Amount ($) *</Label>
          <Input
            type="number"
            value={formData.amount}
            onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
            placeholder="1500.00"
          />
        </div>
        <div className="space-y-2">
          <Label>Payment Method *</Label>
          <Select
            value={formData.payment_method}
            onValueChange={(value) => setFormData({ ...formData, payment_method: value })}
          >
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="cash">Cash</SelectItem>
              <SelectItem value="check">Check</SelectItem>
              <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
              <SelectItem value="credit_card">Credit Card</SelectItem>
              <SelectItem value="debit_card">Debit Card</SelectItem>
              <SelectItem value="online">Online Payment</SelectItem>
              <SelectItem value="mobile_money">Mobile Money</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div className="space-y-2">
          <Label>Payment Date *</Label>
          <Input
            type="date"
            value={formData.payment_date}
            onChange={(e) => setFormData({ ...formData, payment_date: e.target.value })}
          />
        </div>
      </div>

      <div className="space-y-2">
        <Label>Transaction Reference</Label>
        <Input
          value={formData.transaction_reference}
          onChange={(e) => setFormData({ ...formData, transaction_reference: e.target.value })}
          placeholder="TXN123456789"
        />
      </div>

      <div className="space-y-2">
        <Label>Notes</Label>
        <Textarea
          value={formData.notes}
          onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
          placeholder="Additional payment notes..."
          rows={3}
        />
      </div>

      <div className="flex justify-end gap-2 pt-4">
        <Button variant="outline" onClick={onCancel}>
          Cancel
        </Button>
        <Button onClick={onSubmit}>
          Record Payment
        </Button>
      </div>
    </div>
  );
}
