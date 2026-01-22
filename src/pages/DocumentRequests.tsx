import { useState } from 'react';
import { DashboardLayout } from '@/components/DashboardLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/hooks/use-toast';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import {
  FileText,
  Send,
  Loader2,
  Clock,
  CheckCircle,
  XCircle,
  AlertCircle,
  Plus,
  Eye,
  Download,
} from 'lucide-react';
import { useCurrentUser } from '@/hooks/useApi';
import { Alert, AlertDescription } from '@/components/ui/alert';

interface DocumentRequest {
  id: number;
  document_type: string;
  reason: string;
  additional_info?: string;
  status: 'pending' | 'processing' | 'approved' | 'rejected' | 'completed';
  requested_at: string;
  processed_at?: string;
  completed_at?: string;
  rejection_reason?: string;
  download_url?: string;
  notes?: string;
}

const DOCUMENT_TYPES = [
  { value: 'transcript', label: 'Official Transcript', description: 'Certified academic transcript' },
  { value: 'certificate', label: 'Certificate of Enrollment', description: 'Proof of current enrollment' },
  { value: 'conduct', label: 'Certificate of Good Conduct', description: 'Character reference letter' },
  { value: 'recommendation', label: 'Recommendation Letter', description: 'From academic advisor' },
  { value: 'completion', label: 'Completion Letter', description: 'Proof of program completion' },
  { value: 'clearance', label: 'Clearance Certificate', description: 'No outstanding obligations' },
  { value: 'transfer', label: 'Transfer Letter', description: 'For transferring to another institution' },
  { value: 'other', label: 'Other Document', description: 'Specify in additional information' },
];

const STATUS_CONFIG = {
  pending: {
    icon: Clock,
    color: 'text-yellow-600',
    bgColor: 'bg-yellow-100',
    label: 'Pending',
    variant: 'secondary' as const,
  },
  processing: {
    icon: Loader2,
    color: 'text-blue-600',
    bgColor: 'bg-blue-100',
    label: 'Processing',
    variant: 'default' as const,
  },
  approved: {
    icon: CheckCircle,
    color: 'text-green-600',
    bgColor: 'bg-green-100',
    label: 'Approved',
    variant: 'success' as const,
  },
  rejected: {
    icon: XCircle,
    color: 'text-red-600',
    bgColor: 'bg-red-100',
    label: 'Rejected',
    variant: 'destructive' as const,
  },
  completed: {
    icon: CheckCircle,
    color: 'text-green-600',
    bgColor: 'bg-green-100',
    label: 'Completed',
    variant: 'success' as const,
  },
};

const DocumentRequests = () => {
  const { data: currentUser, isLoading: userLoading } = useCurrentUser();
  const { toast } = useToast();

  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [selectedRequest, setSelectedRequest] = useState<DocumentRequest | null>(null);

  // Form state
  const [documentType, setDocumentType] = useState('');
  const [reason, setReason] = useState('');
  const [additionalInfo, setAdditionalInfo] = useState('');

  // Mock data - replace with actual API call
  const [requests, setRequests] = useState<DocumentRequest[]>([
    {
      id: 1,
      document_type: 'transcript',
      reason: 'Application for graduate school',
      status: 'completed',
      requested_at: '2026-01-15T10:30:00Z',
      processed_at: '2026-01-16T14:20:00Z',
      completed_at: '2026-01-17T09:00:00Z',
      download_url: '/documents/transcript-1.pdf',
    },
    {
      id: 2,
      document_type: 'certificate',
      reason: 'Scholarship application',
      status: 'processing',
      requested_at: '2026-01-20T11:00:00Z',
      processed_at: '2026-01-21T10:00:00Z',
    },
    {
      id: 3,
      document_type: 'recommendation',
      reason: 'Job application',
      additional_info: 'Please address to: ABC Corporation HR Department',
      status: 'pending',
      requested_at: '2026-01-22T09:15:00Z',
    },
  ]);

  const handleSubmitRequest = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!documentType || !reason.trim()) {
      toast({
        title: 'Validation Error',
        description: 'Please fill in all required fields.',
        variant: 'destructive',
      });
      return;
    }

    setIsSubmitting(true);

    try {
      // TODO: Replace with actual API call
      await new Promise((resolve) => setTimeout(resolve, 1000));

      const newRequest: DocumentRequest = {
        id: Date.now(),
        document_type: documentType,
        reason: reason.trim(),
        additional_info: additionalInfo.trim() || undefined,
        status: 'pending',
        requested_at: new Date().toISOString(),
      };

      setRequests([newRequest, ...requests]);

      toast({
        title: 'Request Submitted',
        description: 'Your document request has been submitted successfully.',
      });

      // Reset form
      setDocumentType('');
      setReason('');
      setAdditionalInfo('');
      setIsDialogOpen(false);
    } catch (error) {
      toast({
        title: 'Submission Failed',
        description: 'Failed to submit request. Please try again.',
        variant: 'destructive',
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const getDocumentLabel = (type: string) => {
    return DOCUMENT_TYPES.find((dt) => dt.value === type)?.label || type;
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  if (userLoading) {
    return (
      <DashboardLayout>
        <div className="flex items-center justify-center h-64">
          <Loader2 className="h-8 w-8 animate-spin" />
          <span className="ml-2">Loading...</span>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Document Requests</h1>
            <p className="text-muted-foreground mt-2">
              Request official academic documents and track their status
            </p>
          </div>
          <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
            <DialogTrigger asChild>
              <Button>
                <Plus className="mr-2 h-4 w-4" />
                New Request
              </Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-[525px]">
              <form onSubmit={handleSubmitRequest}>
                <DialogHeader>
                  <DialogTitle>Request Official Document</DialogTitle>
                  <DialogDescription>
                    Fill in the details below to request an official document. Processing typically
                    takes 3-5 business days.
                  </DialogDescription>
                </DialogHeader>
                <div className="grid gap-4 py-4">
                  <div className="space-y-2">
                    <Label htmlFor="documentType">
                      Document Type <span className="text-red-500">*</span>
                    </Label>
                    <Select value={documentType} onValueChange={setDocumentType}>
                      <SelectTrigger id="documentType">
                        <SelectValue placeholder="Select document type" />
                      </SelectTrigger>
                      <SelectContent>
                        {DOCUMENT_TYPES.map((type) => (
                          <SelectItem key={type.value} value={type.value}>
                            <div>
                              <div className="font-medium">{type.label}</div>
                              <div className="text-xs text-muted-foreground">
                                {type.description}
                              </div>
                            </div>
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="reason">
                      Purpose/Reason <span className="text-red-500">*</span>
                    </Label>
                    <Textarea
                      id="reason"
                      placeholder="e.g., Application for graduate school, Job application, etc."
                      value={reason}
                      onChange={(e) => setReason(e.target.value)}
                      rows={3}
                      required
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="additionalInfo">Additional Information (Optional)</Label>
                    <Textarea
                      id="additionalInfo"
                      placeholder="Any specific instructions or recipient information"
                      value={additionalInfo}
                      onChange={(e) => setAdditionalInfo(e.target.value)}
                      rows={3}
                    />
                  </div>
                </div>
                <DialogFooter>
                  <Button
                    type="button"
                    variant="outline"
                    onClick={() => setIsDialogOpen(false)}
                    disabled={isSubmitting}
                  >
                    Cancel
                  </Button>
                  <Button type="submit" disabled={isSubmitting}>
                    {isSubmitting ? (
                      <>
                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                        Submitting...
                      </>
                    ) : (
                      <>
                        <Send className="mr-2 h-4 w-4" />
                        Submit Request
                      </>
                    )}
                  </Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>
        </div>

        {/* Info Alert */}
        <Alert>
          <AlertCircle className="h-4 w-4" />
          <AlertDescription>
            Processing time: 3-5 business days. You will be notified via email when your document is
            ready. A processing fee may apply for certain documents.
          </AlertDescription>
        </Alert>

        {/* Request List */}
        <div className="space-y-4">
          <div className="flex items-center gap-2">
            <FileText className="h-5 w-5 text-primary" />
            <h2 className="text-2xl font-semibold">My Requests</h2>
            <Badge variant="secondary">{requests.length}</Badge>
          </div>

          {requests.length === 0 ? (
            <Card>
              <CardContent className="py-12 text-center">
                <FileText className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                <p className="text-lg font-medium">No document requests yet</p>
                <p className="text-muted-foreground mt-2">
                  Click "New Request" to submit your first document request
                </p>
              </CardContent>
            </Card>
          ) : (
            <div className="grid gap-4">
              {requests.map((request) => {
                const statusConfig = STATUS_CONFIG[request.status];
                const StatusIcon = statusConfig.icon;

                return (
                  <Card key={request.id}>
                    <CardHeader>
                      <div className="flex items-start justify-between">
                        <div className="space-y-1 flex-1">
                          <div className="flex items-center gap-2">
                            <CardTitle className="text-lg">
                              {getDocumentLabel(request.document_type)}
                            </CardTitle>
                            <Badge variant={statusConfig.variant as any}>
                              <StatusIcon
                                className={`mr-1 h-3 w-3 ${
                                  request.status === 'processing' ? 'animate-spin' : ''
                                }`}
                              />
                              {statusConfig.label}
                            </Badge>
                          </div>
                          <CardDescription>
                            Requested: {formatDate(request.requested_at)}
                          </CardDescription>
                        </div>
                      </div>
                    </CardHeader>
                    <CardContent className="space-y-4">
                      <div>
                        <Label className="text-sm font-medium">Purpose:</Label>
                        <p className="text-sm text-muted-foreground mt-1">{request.reason}</p>
                      </div>
                      {request.additional_info && (
                        <div>
                          <Label className="text-sm font-medium">Additional Information:</Label>
                          <p className="text-sm text-muted-foreground mt-1">
                            {request.additional_info}
                          </p>
                        </div>
                      )}
                      {request.rejection_reason && (
                        <Alert variant="destructive">
                          <XCircle className="h-4 w-4" />
                          <AlertDescription>{request.rejection_reason}</AlertDescription>
                        </Alert>
                      )}
                      {request.notes && (
                        <div>
                          <Label className="text-sm font-medium">Notes:</Label>
                          <p className="text-sm text-muted-foreground mt-1">{request.notes}</p>
                        </div>
                      )}
                      <div className="flex gap-2 pt-2">
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => setSelectedRequest(request)}
                        >
                          <Eye className="mr-2 h-4 w-4" />
                          View Details
                        </Button>
                        {request.status === 'completed' && request.download_url && (
                          <Button size="sm">
                            <Download className="mr-2 h-4 w-4" />
                            Download
                          </Button>
                        )}
                      </div>
                    </CardContent>
                  </Card>
                );
              })}
            </div>
          )}
        </div>

        {/* Help Section */}
        <Card className="bg-muted/50">
          <CardHeader>
            <CardTitle className="text-lg flex items-center gap-2">
              <AlertCircle className="h-5 w-5" />
              Important Information
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-2 text-sm text-muted-foreground">
            <p>
              • <strong>Processing Time:</strong> Most requests are processed within 3-5 business
              days
            </p>
            <p>
              • <strong>Fees:</strong> Some documents may require a processing fee. You will be
              notified before processing begins
            </p>
            <p>
              • <strong>Collection:</strong> Documents can be collected from the Registrar's Office
              or downloaded when ready
            </p>
            <p>
              • <strong>Validity:</strong> Official documents are valid for 6 months from issue date
            </p>
            <p>
              • <strong>Contact:</strong> For urgent requests, contact the Registrar's Office at
              registrar@academicnexus.edu or +1 (555) 123-4567
            </p>
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
};

export default DocumentRequests;
