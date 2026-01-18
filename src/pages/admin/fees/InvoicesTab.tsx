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
import { Plus, Eye, XCircle, Bell } from 'lucide-react';
import { feeManagementApi, Invoice } from '@/lib/api/feeManagementApi';
import { useToast } from '@/hooks/use-toast';

interface InvoicesTabProps {
  onUpdate?: () => void;
}

export default function InvoicesTab({ onUpdate }: InvoicesTabProps) {
  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [loading, setLoading] = useState(false);
  const [showCreateDialog, setShowCreateDialog] = useState(false);
  const [showViewDialog, setShowViewDialog] = useState(false);
  const [showCancelDialog, setShowCancelDialog] = useState(false);
  const [selectedInvoice, setSelectedInvoice] = useState<Invoice | null>(null);
  const [statusFilter, setStatusFilter] = useState<string>('all');

  const [formData, setFormData] = useState({
    student_id: '',
    fee_structure_id: '',
    amount_due: '',
    due_date: '',
    notes: ''
  });

  const [cancelReason, setCancelReason] = useState('');

  const { toast } = useToast();

  useEffect(() => {
    fetchInvoices();
  }, [statusFilter]);

  const fetchInvoices = async () => {
    setLoading(true);
    try {
      const params = statusFilter !== 'all' ? { status: statusFilter } : undefined;
      const response = await feeManagementApi.getInvoices(params);
      if (response.success) {
        const data = Array.isArray(response.data) ? response.data : (response.data?.data || []);
        setInvoices(data);
      }
    } catch (error: any) {
      console.error('Failed to fetch invoices:', error);
      toast({
        title: 'Error',
        description: error.message || 'Failed to fetch invoices',
        variant: 'destructive',
      });
      setInvoices([]);
    } finally {
      setLoading(false);
    }
  };

  const handleCreate = async () => {
    try {
      const data = {
        student_id: parseInt(formData.student_id),
        fee_structure_id: parseInt(formData.fee_structure_id),
        amount_due: parseFloat(formData.amount_due),
        due_date: formData.due_date,
        notes: formData.notes || undefined
      };

      const response = await feeManagementApi.createInvoice(data);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Invoice created successfully',
        });
        setShowCreateDialog(false);
        resetForm();
        fetchInvoices();
        onUpdate?.();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to create invoice',
        variant: 'destructive',
      });
    }
  };

  const handleCancel = async () => {
    if (!selectedInvoice) return;

    try {
      const response = await feeManagementApi.cancelInvoice(selectedInvoice.id, cancelReason);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Invoice cancelled successfully',
        });
        setShowCancelDialog(false);
        setSelectedInvoice(null);
        setCancelReason('');
        fetchInvoices();
        onUpdate?.();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to cancel invoice',
        variant: 'destructive',
      });
    }
  };

  const resetForm = () => {
    setFormData({
      student_id: '',
      fee_structure_id: '',
      amount_due: '',
      due_date: '',
      notes: ''
    });
  };

  const getStatusBadge = (status: string) => {
    const statusConfig: Record<string, { variant: any; label: string }> = {
      pending: { variant: 'secondary', label: 'Pending' },
      partial: { variant: 'default', label: 'Partial' },
      paid: { variant: 'default', label: 'Paid' },
      overdue: { variant: 'destructive', label: 'Overdue' },
      cancelled: { variant: 'outline', label: 'Cancelled' }
    };

    const config = statusConfig[status] || statusConfig.pending;
    return <Badge variant={config.variant}>{config.label}</Badge>;
  };

  const isOverdue = (invoice: Invoice) => {
    return invoice.status === 'overdue' || 
           (invoice.status === 'pending' && new Date(invoice.due_date) < new Date());
  };

  return (
    <Card>
      <CardHeader>
        <div className="flex justify-between items-center">
          <div>
            <CardTitle>Invoices</CardTitle>
            <CardDescription>Manage student invoices and track payments</CardDescription>
          </div>
          <div className="flex gap-2">
            <Select value={statusFilter} onValueChange={setStatusFilter}>
              <SelectTrigger className="w-40">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Status</SelectItem>
                <SelectItem value="pending">Pending</SelectItem>
                <SelectItem value="partial">Partial</SelectItem>
                <SelectItem value="paid">Paid</SelectItem>
                <SelectItem value="overdue">Overdue</SelectItem>
                <SelectItem value="cancelled">Cancelled</SelectItem>
              </SelectContent>
            </Select>
            <Dialog open={showCreateDialog} onOpenChange={setShowCreateDialog}>
              <DialogTrigger asChild>
                <Button onClick={() => { resetForm(); setShowCreateDialog(true); }}>
                  <Plus className="mr-2 h-4 w-4" />
                  Create Invoice
                </Button>
              </DialogTrigger>
              <DialogContent className="max-w-2xl">
                <DialogHeader>
                  <DialogTitle>Create Invoice</DialogTitle>
                  <DialogDescription>Generate a new invoice for a student</DialogDescription>
                </DialogHeader>
                <InvoiceForm
                  formData={formData}
                  setFormData={setFormData}
                  onSubmit={handleCreate}
                  onCancel={() => setShowCreateDialog(false)}
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
              <TableHead>Invoice #</TableHead>
              <TableHead>Student ID</TableHead>
              <TableHead>Amount Due</TableHead>
              <TableHead>Amount Paid</TableHead>
              <TableHead>Balance</TableHead>
              <TableHead>Due Date</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {loading ? (
              <TableRow>
                <TableCell colSpan={8} className="text-center py-8">
                  Loading invoices...
                </TableCell>
              </TableRow>
            ) : invoices.length === 0 ? (
              <TableRow>
                <TableCell colSpan={8} className="text-center text-muted-foreground">
                  No invoices found
                </TableCell>
              </TableRow>
            ) : (
              invoices.map((invoice) => (
                <TableRow key={invoice.id} className={isOverdue(invoice) ? 'bg-red-50' : ''}>
                  <TableCell className="font-medium">{invoice.invoice_number}</TableCell>
                  <TableCell>{invoice.student_id}</TableCell>
                  <TableCell className="font-semibold">${invoice.amount_due.toLocaleString()}</TableCell>
                  <TableCell>${invoice.amount_paid.toLocaleString()}</TableCell>
                  <TableCell className="font-semibold text-orange-600">
                    ${invoice.balance.toLocaleString()}
                  </TableCell>
                  <TableCell>{new Date(invoice.due_date).toLocaleDateString()}</TableCell>
                  <TableCell>{getStatusBadge(invoice.status)}</TableCell>
                  <TableCell>
                    <div className="flex gap-2">
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => {
                          setSelectedInvoice(invoice);
                          setShowViewDialog(true);
                        }}
                      >
                        <Eye className="h-3 w-3" />
                      </Button>
                      {invoice.status !== 'cancelled' && invoice.status !== 'paid' && (
                        <>
                          <Button
                            size="sm"
                            variant="destructive"
                            onClick={() => {
                              setSelectedInvoice(invoice);
                              setShowCancelDialog(true);
                            }}
                          >
                            <XCircle className="h-3 w-3" />
                          </Button>
                          <Button
                            size="sm"
                            variant="secondary"
                            onClick={() => {
                              toast({
                                title: 'Reminder Sent',
                                description: `Payment reminder sent for invoice ${invoice.invoice_number}`,
                              });
                            }}
                          >
                            <Bell className="h-3 w-3" />
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

      {/* View Invoice Dialog */}
      <Dialog open={showViewDialog} onOpenChange={setShowViewDialog}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Invoice Details</DialogTitle>
            <DialogDescription>View invoice information</DialogDescription>
          </DialogHeader>
          {selectedInvoice && (
            <div className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-muted-foreground">Invoice Number</Label>
                  <p className="font-medium">{selectedInvoice.invoice_number}</p>
                </div>
                <div>
                  <Label className="text-muted-foreground">Status</Label>
                  <div className="mt-1">{getStatusBadge(selectedInvoice.status)}</div>
                </div>
                <div>
                  <Label className="text-muted-foreground">Student ID</Label>
                  <p className="font-medium">{selectedInvoice.student_id}</p>
                </div>
                <div>
                  <Label className="text-muted-foreground">Fee Structure ID</Label>
                  <p className="font-medium">{selectedInvoice.fee_structure_id}</p>
                </div>
                <div>
                  <Label className="text-muted-foreground">Amount Due</Label>
                  <p className="font-semibold text-lg">${selectedInvoice.amount_due.toLocaleString()}</p>
                </div>
                <div>
                  <Label className="text-muted-foreground">Amount Paid</Label>
                  <p className="font-semibold text-lg text-green-600">
                    ${selectedInvoice.amount_paid.toLocaleString()}
                  </p>
                </div>
                <div>
                  <Label className="text-muted-foreground">Balance</Label>
                  <p className="font-semibold text-lg text-orange-600">
                    ${selectedInvoice.balance.toLocaleString()}
                  </p>
                </div>
                <div>
                  <Label className="text-muted-foreground">Due Date</Label>
                  <p className="font-medium">
                    {new Date(selectedInvoice.due_date).toLocaleDateString()}
                  </p>
                </div>
                <div>
                  <Label className="text-muted-foreground">Issued Date</Label>
                  <p className="font-medium">
                    {new Date(selectedInvoice.issued_date).toLocaleDateString()}
                  </p>
                </div>
              </div>
              {selectedInvoice.description && (
                <div>
                  <Label className="text-muted-foreground">Description</Label>
                  <p className="text-sm mt-1">{selectedInvoice.description}</p>
                </div>
              )}
              {selectedInvoice.notes && (
                <div>
                  <Label className="text-muted-foreground">Notes</Label>
                  <p className="text-sm mt-1">{selectedInvoice.notes}</p>
                </div>
              )}
            </div>
          )}
        </DialogContent>
      </Dialog>

      {/* Cancel Invoice Dialog */}
      <Dialog open={showCancelDialog} onOpenChange={setShowCancelDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Cancel Invoice</DialogTitle>
            <DialogDescription>
              Are you sure you want to cancel this invoice? This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4">
            <div className="space-y-2">
              <Label>Cancellation Reason *</Label>
              <Textarea
                value={cancelReason}
                onChange={(e) => setCancelReason(e.target.value)}
                placeholder="Enter reason for cancellation..."
                rows={3}
              />
            </div>
            <div className="flex justify-end gap-2">
              <Button variant="outline" onClick={() => setShowCancelDialog(false)}>
                Close
              </Button>
              <Button variant="destructive" onClick={handleCancel} disabled={!cancelReason.trim()}>
                Cancel Invoice
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </Card>
  );
}

// Invoice Form Component
function InvoiceForm({
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
          <Label>Student ID *</Label>
          <Input
            type="number"
            value={formData.student_id}
            onChange={(e) => setFormData({ ...formData, student_id: e.target.value })}
            placeholder="12345"
          />
        </div>
        <div className="space-y-2">
          <Label>Fee Structure ID *</Label>
          <Input
            type="number"
            value={formData.fee_structure_id}
            onChange={(e) => setFormData({ ...formData, fee_structure_id: e.target.value })}
            placeholder="1"
          />
        </div>
      </div>

      <div className="grid grid-cols-2 gap-4">
        <div className="space-y-2">
          <Label>Amount Due ($) *</Label>
          <Input
            type="number"
            value={formData.amount_due}
            onChange={(e) => setFormData({ ...formData, amount_due: e.target.value })}
            placeholder="1500.00"
          />
        </div>
        <div className="space-y-2">
          <Label>Due Date *</Label>
          <Input
            type="date"
            value={formData.due_date}
            onChange={(e) => setFormData({ ...formData, due_date: e.target.value })}
          />
        </div>
      </div>

      <div className="space-y-2">
        <Label>Notes</Label>
        <Textarea
          value={formData.notes}
          onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
          placeholder="Additional notes..."
          rows={3}
        />
      </div>

      <div className="flex justify-end gap-2 pt-4">
        <Button variant="outline" onClick={onCancel}>
          Cancel
        </Button>
        <Button onClick={onSubmit}>
          Create Invoice
        </Button>
      </div>
    </div>
  );
}
