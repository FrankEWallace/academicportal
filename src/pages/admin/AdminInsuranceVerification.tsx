import { useState, useEffect } from 'react';
import { DashboardLayout } from '@/components/DashboardLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { CheckCircle, XCircle, AlertTriangle, FileText, DollarSign, Shield, Clock } from 'lucide-react';
import { adminInsuranceApi, InsuranceSubmission, InsuranceStatistics } from '@/lib/api/adminApi';
import { useToast } from '@/hooks/use-toast';

export default function AdminInsuranceVerification() {
  const [submissions, setSubmissions] = useState<InsuranceSubmission[]>([]);
  const [statistics, setStatistics] = useState<InsuranceStatistics | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedSubmission, setSelectedSubmission] = useState<InsuranceSubmission | null>(null);
  const [showRejectDialog, setShowRejectDialog] = useState(false);
  const [showResubmitDialog, setShowResubmitDialog] = useState(false);
  const [rejectReason, setRejectReason] = useState('');
  const [resubmitFeedback, setResubmitFeedback] = useState('');
  const { toast } = useToast();

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    try {
      await Promise.all([
        fetchSubmissions(),
        fetchStatistics(),
      ]);
    } finally {
      setLoading(false);
    }
  };

  const fetchSubmissions = async (status?: string) => {
    try {
      const response = await adminInsuranceApi.getSubmissions(status);
      if (response.success) {
        // Handle both array and paginated response formats
        const data = Array.isArray(response.data) 
          ? response.data 
          : (response.data?.data || []);
        setSubmissions(data);
      }
    } catch (error: any) {
      console.error('Fetch submissions error:', error);
      toast({
        title: 'Error',
        description: error.message || 'Failed to fetch submissions',
        variant: 'destructive',
      });
      // Set empty array on error to prevent map error
      setSubmissions([]);
    }
  };

  const fetchStatistics = async () => {
    try {
      const response = await adminInsuranceApi.getStatistics();
      if (response.success) {
        setStatistics(response.data);
      }
    } catch (error: any) {
      console.error('Failed to fetch statistics:', error);
    }
  };

  const handleVerify = async (id: number) => {
    try {
      const response = await adminInsuranceApi.verifyInsurance(id);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Insurance verified successfully',
        });
        fetchData();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to verify insurance',
        variant: 'destructive',
      });
    }
  };

  const handleReject = async () => {
    if (!selectedSubmission || !rejectReason.trim()) {
      toast({
        title: 'Error',
        description: 'Please provide a reason for rejection',
        variant: 'destructive',
      });
      return;
    }

    try {
      const response = await adminInsuranceApi.rejectInsurance(selectedSubmission.id, rejectReason);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Insurance rejected',
        });
        setShowRejectDialog(false);
        setRejectReason('');
        setSelectedSubmission(null);
        fetchData();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to reject insurance',
        variant: 'destructive',
      });
    }
  };

  const handleRequestResubmission = async () => {
    if (!selectedSubmission || !resubmitFeedback.trim()) {
      toast({
        title: 'Error',
        description: 'Please provide feedback for resubmission',
        variant: 'destructive',
      });
      return;
    }

    try {
      const response = await adminInsuranceApi.requestResubmission(selectedSubmission.id, resubmitFeedback);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Resubmission requested',
        });
        setShowResubmitDialog(false);
        setResubmitFeedback('');
        setSelectedSubmission(null);
        fetchData();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to request resubmission',
        variant: 'destructive',
      });
    }
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'verified':
        return <Badge variant="default"><CheckCircle className="mr-1 h-3 w-3" />Verified</Badge>;
      case 'rejected':
        return <Badge variant="destructive"><XCircle className="mr-1 h-3 w-3" />Rejected</Badge>;
      default:
        return <Badge variant="secondary"><Clock className="mr-1 h-3 w-3" />Pending</Badge>;
    }
  };

  if (loading) {
    return (
      <DashboardLayout title="Insurance Verification">
        <div className="flex items-center justify-center p-8">
          <div className="text-center">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
            <p className="mt-4 text-muted-foreground">Loading insurance submissions...</p>
          </div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout title="Insurance Verification">
      <div className="space-y-6">{/* Statistics Cards */}
      {statistics && (
        <div className="grid gap-4 md:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Submissions</CardTitle>
              <FileText className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.total_submissions}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Pending Verification</CardTitle>
              <Clock className="h-4 w-4 text-yellow-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.pending_verification}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Verified</CardTitle>
              <CheckCircle className="h-4 w-4 text-green-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.verified}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Rejected</CardTitle>
              <XCircle className="h-4 w-4 text-red-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.rejected}</div>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Tabs for filtering */}
      <Tabs defaultValue="pending" onValueChange={(value) => fetchSubmissions(value === 'all' ? undefined : value)}>
        <TabsList>
          <TabsTrigger value="all">All</TabsTrigger>
          <TabsTrigger value="pending">Pending</TabsTrigger>
          <TabsTrigger value="verified">Verified</TabsTrigger>
          <TabsTrigger value="rejected">Rejected</TabsTrigger>
        </TabsList>

        <TabsContent value="all" className="space-y-4">
          <SubmissionsTable
            submissions={submissions}
            loading={loading}
            onVerify={handleVerify}
            onReject={(submission) => {
              setSelectedSubmission(submission);
              setShowRejectDialog(true);
            }}
            onRequestResubmission={(submission) => {
              setSelectedSubmission(submission);
              setShowResubmitDialog(true);
            }}
            getStatusBadge={getStatusBadge}
          />
        </TabsContent>

        <TabsContent value="pending" className="space-y-4">
          <SubmissionsTable
            submissions={submissions}
            loading={loading}
            onVerify={handleVerify}
            onReject={(submission) => {
              setSelectedSubmission(submission);
              setShowRejectDialog(true);
            }}
            onRequestResubmission={(submission) => {
              setSelectedSubmission(submission);
              setShowResubmitDialog(true);
            }}
            getStatusBadge={getStatusBadge}
          />
        </TabsContent>

        <TabsContent value="verified" className="space-y-4">
          <SubmissionsTable
            submissions={submissions}
            loading={loading}
            onVerify={handleVerify}
            onReject={(submission) => {
              setSelectedSubmission(submission);
              setShowRejectDialog(true);
            }}
            onRequestResubmission={(submission) => {
              setSelectedSubmission(submission);
              setShowResubmitDialog(true);
            }}
            getStatusBadge={getStatusBadge}
          />
        </TabsContent>

        <TabsContent value="rejected" className="space-y-4">
          <SubmissionsTable
            submissions={submissions}
            loading={loading}
            onVerify={handleVerify}
            onReject={(submission) => {
              setSelectedSubmission(submission);
              setShowRejectDialog(true);
            }}
            onRequestResubmission={(submission) => {
              setSelectedSubmission(submission);
              setShowResubmitDialog(true);
            }}
            getStatusBadge={getStatusBadge}
          />
        </TabsContent>
      </Tabs>

      {/* Reject Dialog */}
      <Dialog open={showRejectDialog} onOpenChange={setShowRejectDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Reject Insurance</DialogTitle>
            <DialogDescription>
              Provide a reason for rejecting this insurance submission
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4">
            <div>
              <Label>Reason for Rejection *</Label>
              <Textarea
                value={rejectReason}
                onChange={(e) => setRejectReason(e.target.value)}
                placeholder="Enter detailed reason for rejection..."
                rows={4}
              />
            </div>
            <div className="flex justify-end gap-2">
              <Button variant="outline" onClick={() => setShowRejectDialog(false)}>
                Cancel
              </Button>
              <Button variant="destructive" onClick={handleReject}>
                Reject Insurance
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* Request Resubmission Dialog */}
      <Dialog open={showResubmitDialog} onOpenChange={setShowResubmitDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Request Resubmission</DialogTitle>
            <DialogDescription>
              Provide feedback for the student to resubmit their insurance
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4">
            <div>
              <Label>Feedback *</Label>
              <Textarea
                value={resubmitFeedback}
                onChange={(e) => setResubmitFeedback(e.target.value)}
                placeholder="Enter feedback for the student..."
                rows={4}
              />
            </div>
            <div className="flex justify-end gap-2">
              <Button variant="outline" onClick={() => setShowResubmitDialog(false)}>
                Cancel
              </Button>
              <Button onClick={handleRequestResubmission}>
                Request Resubmission
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
      </div>
    </DashboardLayout>
  );
}

// Submissions Table Component
function SubmissionsTable({
  submissions,
  loading,
  onVerify,
  onReject,
  onRequestResubmission,
  getStatusBadge,
}: {
  submissions: InsuranceSubmission[];
  loading: boolean;
  onVerify: (id: number) => void;
  onReject: (submission: InsuranceSubmission) => void;
  onRequestResubmission: (submission: InsuranceSubmission) => void;
  getStatusBadge: (status: string) => JSX.Element;
}) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Insurance Submissions</CardTitle>
        <CardDescription>Review and manage student insurance documents</CardDescription>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Student</TableHead>
              <TableHead>Matric Number</TableHead>
              <TableHead>Policy Number</TableHead>
              <TableHead>Provider</TableHead>
              <TableHead>Coverage</TableHead>
              <TableHead>Premium</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Submitted</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {loading ? (
              <TableRow>
                <TableCell colSpan={9} className="text-center py-8">
                  Loading submissions...
                </TableCell>
              </TableRow>
            ) : !Array.isArray(submissions) || submissions.length === 0 ? (
              <TableRow>
                <TableCell colSpan={9} className="text-center text-muted-foreground">
                  No submissions found
                </TableCell>
              </TableRow>
            ) : (
              submissions.map((submission) => (
                <TableRow key={submission.id}>
                  <TableCell className="font-medium">{submission.student_name}</TableCell>
                  <TableCell>{submission.matric_number}</TableCell>
                  <TableCell>{submission.policy_number}</TableCell>
                  <TableCell>{submission.provider}</TableCell>
                  <TableCell>${submission.coverage_amount.toLocaleString()}</TableCell>
                  <TableCell>${submission.premium_amount.toLocaleString()}</TableCell>
                  <TableCell>{getStatusBadge(submission.status)}</TableCell>
                  <TableCell>{new Date(submission.submitted_at).toLocaleDateString()}</TableCell>
                  <TableCell>
                    <div className="flex gap-2">
                      {submission.status === 'pending' && (
                        <>
                          <Button
                            size="sm"
                            variant="default"
                            onClick={() => onVerify(submission.id)}
                          >
                            <CheckCircle className="mr-1 h-3 w-3" />
                            Verify
                          </Button>
                          <Button
                            size="sm"
                            variant="destructive"
                            onClick={() => onReject(submission)}
                          >
                            <XCircle className="mr-1 h-3 w-3" />
                            Reject
                          </Button>
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => onRequestResubmission(submission)}
                          >
                            <AlertTriangle className="mr-1 h-3 w-3" />
                            Request Resubmit
                          </Button>
                        </>
                      )}
                      {submission.status === 'rejected' && submission.rejected_reason && (
                        <div className="text-xs text-muted-foreground">
                          Reason: {submission.rejected_reason}
                        </div>
                      )}
                    </div>
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  );
}
