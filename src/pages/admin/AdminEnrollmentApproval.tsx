import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { CheckCircle, XCircle, Clock, Users, BookOpen, AlertTriangle } from 'lucide-react';
import { adminEnrollmentApi, Enrollment, EnrollmentStatistics } from '@/lib/api/adminApi';
import { useToast } from '@/hooks/use-toast';

export default function AdminEnrollmentApproval() {
  const [enrollments, setEnrollments] = useState<Enrollment[]>([]);
  const [statistics, setStatistics] = useState<EnrollmentStatistics | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedEnrollment, setSelectedEnrollment] = useState<Enrollment | null>(null);
  const [showRejectDialog, setShowRejectDialog] = useState(false);
  const [showBulkDialog, setShowBulkDialog] = useState(false);
  const [rejectReason, setRejectReason] = useState('');
  const [selectedIds, setSelectedIds] = useState<number[]>([]);
  const [bulkAction, setBulkAction] = useState<'approve' | 'reject'>('approve');
  const { toast } = useToast();

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    try {
      await Promise.all([
        fetchEnrollments(),
        fetchStatistics(),
      ]);
    } finally {
      setLoading(false);
    }
  };

  const fetchEnrollments = async (status?: string) => {
    try {
      const response = await adminEnrollmentApi.getEnrollments(status);
      if (response.success) {
        // Handle both array and paginated response formats
        const data = Array.isArray(response.data) 
          ? response.data 
          : (response.data?.data || []);
        setEnrollments(data);
      }
    } catch (error: any) {
      console.error('Fetch enrollments error:', error);
      toast({
        title: 'Error',
        description: error.message || 'Failed to fetch enrollments',
        variant: 'destructive',
      });
      // Set empty array on error to prevent map error
      setEnrollments([]);
    }
  };

  const fetchStatistics = async () => {
    try {
      const response = await adminEnrollmentApi.getStatistics();
      if (response.success) {
        setStatistics(response.data);
      }
    } catch (error: any) {
      console.error('Failed to fetch statistics:', error);
    }
  };

  const handleApprove = async (id: number) => {
    try {
      const response = await adminEnrollmentApi.approveEnrollment(id);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Enrollment approved successfully',
        });
        fetchData();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to approve enrollment',
        variant: 'destructive',
      });
    }
  };

  const handleReject = async () => {
    if (!selectedEnrollment || !rejectReason.trim()) {
      toast({
        title: 'Error',
        description: 'Please provide a reason for rejection',
        variant: 'destructive',
      });
      return;
    }

    try {
      const response = await adminEnrollmentApi.rejectEnrollment(selectedEnrollment.id, rejectReason);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Enrollment rejected',
        });
        setShowRejectDialog(false);
        setRejectReason('');
        setSelectedEnrollment(null);
        fetchData();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to reject enrollment',
        variant: 'destructive',
      });
    }
  };

  const handleBulkAction = async () => {
    if (selectedIds.length === 0) {
      toast({
        title: 'Error',
        description: 'Please select at least one enrollment',
        variant: 'destructive',
      });
      return;
    }

    if (bulkAction === 'reject' && !rejectReason.trim()) {
      toast({
        title: 'Error',
        description: 'Please provide a reason for rejection',
        variant: 'destructive',
      });
      return;
    }

    try {
      let response;
      if (bulkAction === 'approve') {
        response = await adminEnrollmentApi.bulkApprove(selectedIds);
      } else {
        response = await adminEnrollmentApi.bulkReject(selectedIds, rejectReason);
      }

      if (response.success) {
        toast({
          title: 'Success',
          description: `${selectedIds.length} enrollment(s) ${bulkAction === 'approve' ? 'approved' : 'rejected'}`,
        });
        setShowBulkDialog(false);
        setSelectedIds([]);
        setRejectReason('');
        fetchData();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || `Failed to ${bulkAction} enrollments`,
        variant: 'destructive',
      });
    }
  };

  const toggleSelection = (id: number) => {
    setSelectedIds((prev) =>
      prev.includes(id) ? prev.filter((i) => i !== id) : [...prev, id]
    );
  };

  const toggleSelectAll = () => {
    if (selectedIds.length === enrollments.length) {
      setSelectedIds([]);
    } else {
      setSelectedIds(enrollments.map((e) => e.id));
    }
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'approved':
        return <Badge variant="default"><CheckCircle className="mr-1 h-3 w-3" />Approved</Badge>;
      case 'rejected':
        return <Badge variant="destructive"><XCircle className="mr-1 h-3 w-3" />Rejected</Badge>;
      default:
        return <Badge variant="secondary"><Clock className="mr-1 h-3 w-3" />Pending</Badge>;
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center p-8">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
          <p className="mt-4 text-muted-foreground">Loading enrollments...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6 p-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold">Enrollment Approval</h1>
          <p className="text-muted-foreground">Review and approve student course enrollments</p>
        </div>
        {selectedIds.length > 0 && (
          <div className="flex gap-2">
            <Button
              variant="default"
              onClick={() => {
                setBulkAction('approve');
                setShowBulkDialog(true);
              }}
            >
              <CheckCircle className="mr-2 h-4 w-4" />
              Bulk Approve ({selectedIds.length})
            </Button>
            <Button
              variant="destructive"
              onClick={() => {
                setBulkAction('reject');
                setShowBulkDialog(true);
              }}
            >
              <XCircle className="mr-2 h-4 w-4" />
              Bulk Reject ({selectedIds.length})
            </Button>
          </div>
        )}
      </div>

      {/* Statistics Cards */}
      {statistics && (
        <div className="grid gap-4 md:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Enrollments</CardTitle>
              <Users className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.total_enrollments}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Pending Approval</CardTitle>
              <Clock className="h-4 w-4 text-yellow-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.pending_approval}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Approved</CardTitle>
              <CheckCircle className="h-4 w-4 text-green-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.approved}</div>
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
      <Tabs defaultValue="pending" onValueChange={(value) => fetchEnrollments(value === 'all' ? undefined : value)}>
        <TabsList>
          <TabsTrigger value="all">All</TabsTrigger>
          <TabsTrigger value="pending">Pending</TabsTrigger>
          <TabsTrigger value="approved">Approved</TabsTrigger>
          <TabsTrigger value="rejected">Rejected</TabsTrigger>
        </TabsList>

        <TabsContent value="all" className="space-y-4">
          <EnrollmentTable
            enrollments={enrollments}
            selectedIds={selectedIds}
            onToggleSelection={toggleSelection}
            onToggleSelectAll={toggleSelectAll}
            onApprove={handleApprove}
            onReject={(enrollment) => {
              setSelectedEnrollment(enrollment);
              setShowRejectDialog(true);
            }}
            getStatusBadge={getStatusBadge}
          />
        </TabsContent>

        <TabsContent value="pending" className="space-y-4">
          <EnrollmentTable
            enrollments={enrollments}
            selectedIds={selectedIds}
            onToggleSelection={toggleSelection}
            onToggleSelectAll={toggleSelectAll}
            onApprove={handleApprove}
            onReject={(enrollment) => {
              setSelectedEnrollment(enrollment);
              setShowRejectDialog(true);
            }}
            getStatusBadge={getStatusBadge}
          />
        </TabsContent>

        <TabsContent value="approved" className="space-y-4">
          <EnrollmentTable
            enrollments={enrollments}
            selectedIds={selectedIds}
            onToggleSelection={toggleSelection}
            onToggleSelectAll={toggleSelectAll}
            onApprove={handleApprove}
            onReject={(enrollment) => {
              setSelectedEnrollment(enrollment);
              setShowRejectDialog(true);
            }}
            getStatusBadge={getStatusBadge}
          />
        </TabsContent>

        <TabsContent value="rejected" className="space-y-4">
          <EnrollmentTable
            enrollments={enrollments}
            selectedIds={selectedIds}
            onToggleSelection={toggleSelection}
            onToggleSelectAll={toggleSelectAll}
            onApprove={handleApprove}
            onReject={(enrollment) => {
              setSelectedEnrollment(enrollment);
              setShowRejectDialog(true);
            }}
            getStatusBadge={getStatusBadge}
          />
        </TabsContent>
      </Tabs>

      {/* Reject Dialog */}
      <Dialog open={showRejectDialog} onOpenChange={setShowRejectDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Reject Enrollment</DialogTitle>
            <DialogDescription>
              Provide a reason for rejecting this enrollment
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
                Reject Enrollment
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* Bulk Action Dialog */}
      <Dialog open={showBulkDialog} onOpenChange={setShowBulkDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>
              {bulkAction === 'approve' ? 'Bulk Approve Enrollments' : 'Bulk Reject Enrollments'}
            </DialogTitle>
            <DialogDescription>
              {bulkAction === 'approve'
                ? `Approve ${selectedIds.length} selected enrollment(s)`
                : `Reject ${selectedIds.length} selected enrollment(s)`}
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4">
            {bulkAction === 'reject' && (
              <div>
                <Label>Reason for Rejection *</Label>
                <Textarea
                  value={rejectReason}
                  onChange={(e) => setRejectReason(e.target.value)}
                  placeholder="Enter reason for bulk rejection..."
                  rows={4}
                />
              </div>
            )}
            <div className="flex justify-end gap-2">
              <Button variant="outline" onClick={() => setShowBulkDialog(false)}>
                Cancel
              </Button>
              <Button
                variant={bulkAction === 'approve' ? 'default' : 'destructive'}
                onClick={handleBulkAction}
              >
                {bulkAction === 'approve' ? 'Approve All' : 'Reject All'}
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
}

// Enrollment Table Component
function EnrollmentTable({
  enrollments,
  selectedIds,
  onToggleSelection,
  onToggleSelectAll,
  onApprove,
  onReject,
  getStatusBadge,
}: {
  enrollments: Enrollment[];
  selectedIds: number[];
  onToggleSelection: (id: number) => void;
  onToggleSelectAll: () => void;
  onApprove: (id: number) => void;
  onReject: (enrollment: Enrollment) => void;
  getStatusBadge: (status: string) => JSX.Element;
}) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Course Enrollments</CardTitle>
        <CardDescription>Review and manage student course enrollments</CardDescription>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead className="w-12">
                <Checkbox
                  checked={selectedIds.length === enrollments.length && enrollments.length > 0}
                  onCheckedChange={onToggleSelectAll}
                />
              </TableHead>
              <TableHead>Student</TableHead>
              <TableHead>Matric Number</TableHead>
              <TableHead>Semester</TableHead>
              <TableHead>Academic Year</TableHead>
              <TableHead>Courses</TableHead>
              <TableHead>Total Units</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Submitted</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {enrollments.length === 0 ? (
              <TableRow>
                <TableCell colSpan={10} className="text-center text-muted-foreground">
                  No enrollments found
                </TableCell>
              </TableRow>
            ) : (
              enrollments.map((enrollment) => (
                <TableRow key={enrollment.id}>
                  <TableCell>
                    <Checkbox
                      checked={selectedIds.includes(enrollment.id)}
                      onCheckedChange={() => onToggleSelection(enrollment.id)}
                    />
                  </TableCell>
                  <TableCell className="font-medium">{enrollment.student_name}</TableCell>
                  <TableCell>{enrollment.matric_number}</TableCell>
                  <TableCell>{enrollment.semester}</TableCell>
                  <TableCell>{enrollment.academic_year}</TableCell>
                  <TableCell>{enrollment.courses_count} courses</TableCell>
                  <TableCell>{enrollment.total_units} units</TableCell>
                  <TableCell>{getStatusBadge(enrollment.status)}</TableCell>
                  <TableCell>{new Date(enrollment.submitted_at).toLocaleDateString()}</TableCell>
                  <TableCell>
                    <div className="flex gap-2">
                      {enrollment.status === 'pending' && (
                        <>
                          <Button
                            size="sm"
                            variant="default"
                            onClick={() => onApprove(enrollment.id)}
                          >
                            <CheckCircle className="mr-1 h-3 w-3" />
                            Approve
                          </Button>
                          <Button
                            size="sm"
                            variant="destructive"
                            onClick={() => onReject(enrollment)}
                          >
                            <XCircle className="mr-1 h-3 w-3" />
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
    </Card>
  );
}
