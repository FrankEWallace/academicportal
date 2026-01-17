import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { 
  CheckCircle, 
  XCircle, 
  Clock, 
  FileText, 
  Upload,
  AlertTriangle,
  Eye,
  TrendingUp,
  Calendar
} from 'lucide-react';
import { useToast } from '@/hooks/use-toast';

interface PendingResult {
  id: number;
  course_code: string;
  course_title: string;
  lecturer_name: string;
  lecturer_id: number;
  result_type: 'ca' | 'exam';
  students_count: number;
  submitted_at: string;
  status: 'pending' | 'approved' | 'rejected' | 'published';
  semester: number;
  academic_year: string;
}

interface ResultStatistics {
  total_pending: number;
  ca_pending: number;
  exam_pending: number;
  approved_today: number;
  rejected_today: number;
  published_this_semester: number;
}

interface StudentResult {
  id: number;
  student_name: string;
  matric_number: string;
  ca_score?: number;
  exam_score?: number;
  total_score?: number;
  grade?: string;
}

export default function AdminResultsModeration() {
  const [results, setResults] = useState<PendingResult[]>([]);
  const [statistics, setStatistics] = useState<ResultStatistics | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedResult, setSelectedResult] = useState<PendingResult | null>(null);
  const [studentResults, setStudentResults] = useState<StudentResult[]>([]);
  const [showDetailDialog, setShowDetailDialog] = useState(false);
  const [showRejectDialog, setShowRejectDialog] = useState(false);
  const [showPublishDialog, setShowPublishDialog] = useState(false);
  const [rejectReason, setRejectReason] = useState('');
  const [publishDate, setPublishDate] = useState('');
  const { toast } = useToast();

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    try {
      // Simulated data - replace with actual API calls
      const mockResults: PendingResult[] = [
        {
          id: 1,
          course_code: 'CSC 301',
          course_title: 'Data Structures & Algorithms',
          lecturer_name: 'Dr. John Smith',
          lecturer_id: 1,
          result_type: 'ca',
          students_count: 45,
          submitted_at: '2026-01-15T14:30:00Z',
          status: 'pending',
          semester: 1,
          academic_year: '2025/2026',
        },
        {
          id: 2,
          course_code: 'MAT 201',
          course_title: 'Linear Algebra',
          lecturer_name: 'Prof. Jane Doe',
          lecturer_id: 2,
          result_type: 'exam',
          students_count: 38,
          submitted_at: '2026-01-14T10:15:00Z',
          status: 'pending',
          semester: 1,
          academic_year: '2025/2026',
        },
        {
          id: 3,
          course_code: 'PHY 102',
          course_title: 'Mechanics',
          lecturer_name: 'Dr. Michael Brown',
          lecturer_id: 3,
          result_type: 'ca',
          students_count: 52,
          submitted_at: '2026-01-16T09:00:00Z',
          status: 'approved',
          semester: 1,
          academic_year: '2025/2026',
        },
      ];

      const mockStats: ResultStatistics = {
        total_pending: 15,
        ca_pending: 8,
        exam_pending: 7,
        approved_today: 5,
        rejected_today: 1,
        published_this_semester: 23,
      };

      setResults(mockResults);
      setStatistics(mockStats);
    } catch (error: any) {
      toast({
        title: 'Error',
        description: 'Failed to load results data',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const fetchStudentResults = async (resultId: number) => {
    // Simulated student results data
    const mockStudentResults: StudentResult[] = [
      {
        id: 1,
        student_name: 'Alice Johnson',
        matric_number: 'STU/2024/001',
        ca_score: 28,
        exam_score: 65,
        total_score: 93,
        grade: 'A',
      },
      {
        id: 2,
        student_name: 'Bob Williams',
        matric_number: 'STU/2024/002',
        ca_score: 25,
        exam_score: 58,
        total_score: 83,
        grade: 'B+',
      },
      {
        id: 3,
        student_name: 'Carol Davis',
        matric_number: 'STU/2024/003',
        ca_score: 22,
        exam_score: 52,
        total_score: 74,
        grade: 'B',
      },
    ];
    setStudentResults(mockStudentResults);
  };

  const handleViewDetails = async (result: PendingResult) => {
    setSelectedResult(result);
    await fetchStudentResults(result.id);
    setShowDetailDialog(true);
  };

  const handleApprove = async (resultId: number) => {
    try {
      // API call: await adminResultsApi.approve(resultId)
      toast({
        title: 'Success',
        description: 'Results approved successfully',
      });
      fetchData();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to approve results',
        variant: 'destructive',
      });
    }
  };

  const handleReject = async () => {
    if (!selectedResult || !rejectReason.trim()) {
      toast({
        title: 'Error',
        description: 'Please provide a reason for rejection',
        variant: 'destructive',
      });
      return;
    }

    try {
      // API call: await adminResultsApi.reject(selectedResult.id, rejectReason)
      toast({
        title: 'Success',
        description: 'Results rejected and returned to lecturer',
      });
      setShowRejectDialog(false);
      setRejectReason('');
      setSelectedResult(null);
      fetchData();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to reject results',
        variant: 'destructive',
      });
    }
  };

  const handlePublish = async () => {
    if (!selectedResult) return;

    try {
      // API call: await adminResultsApi.publish(selectedResult.id, publishDate)
      toast({
        title: 'Success',
        description: 'Results published to students',
      });
      setShowPublishDialog(false);
      setPublishDate('');
      setSelectedResult(null);
      fetchData();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to publish results',
        variant: 'destructive',
      });
    }
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'approved':
        return <Badge variant="default"><CheckCircle className="mr-1 h-3 w-3" />Approved</Badge>;
      case 'rejected':
        return <Badge variant="destructive"><XCircle className="mr-1 h-3 w-3" />Rejected</Badge>;
      case 'published':
        return <Badge variant="default" className="bg-green-600"><Upload className="mr-1 h-3 w-3" />Published</Badge>;
      default:
        return <Badge variant="secondary"><Clock className="mr-1 h-3 w-3" />Pending Review</Badge>;
    }
  };

  const getResultTypeBadge = (type: string) => {
    return type === 'ca' ? (
      <Badge variant="outline" className="bg-blue-50">CA Scores</Badge>
    ) : (
      <Badge variant="outline" className="bg-purple-50">Exam Results</Badge>
    );
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center p-8">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
          <p className="mt-4 text-muted-foreground">Loading results...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6 p-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold">Results Moderation</h1>
          <p className="text-muted-foreground">Review, approve, and publish student results</p>
        </div>
        <Button variant="outline">
          <Calendar className="mr-2 h-4 w-4" />
          Publishing Schedule
        </Button>
      </div>

      {/* Statistics Cards */}
      {statistics && (
        <div className="grid gap-4 md:grid-cols-6">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Pending</CardTitle>
              <Clock className="h-4 w-4 text-yellow-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.total_pending}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">CA Pending</CardTitle>
              <FileText className="h-4 w-4 text-blue-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.ca_pending}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Exam Pending</CardTitle>
              <FileText className="h-4 w-4 text-purple-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.exam_pending}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Approved Today</CardTitle>
              <CheckCircle className="h-4 w-4 text-green-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.approved_today}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Rejected Today</CardTitle>
              <XCircle className="h-4 w-4 text-red-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.rejected_today}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Published</CardTitle>
              <Upload className="h-4 w-4 text-green-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.published_this_semester}</div>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Tabs for filtering */}
      <Tabs defaultValue="pending">
        <TabsList>
          <TabsTrigger value="pending">Pending Review</TabsTrigger>
          <TabsTrigger value="ca">CA Scores</TabsTrigger>
          <TabsTrigger value="exam">Exam Results</TabsTrigger>
          <TabsTrigger value="approved">Approved</TabsTrigger>
          <TabsTrigger value="published">Published</TabsTrigger>
        </TabsList>

        <TabsContent value="pending" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Pending Results Review</CardTitle>
              <CardDescription>Results awaiting your approval before publishing</CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Course</TableHead>
                    <TableHead>Lecturer</TableHead>
                    <TableHead>Type</TableHead>
                    <TableHead>Students</TableHead>
                    <TableHead>Academic Year</TableHead>
                    <TableHead>Submitted</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {results.filter(r => r.status === 'pending').length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={8} className="text-center text-muted-foreground">
                        No pending results
                      </TableCell>
                    </TableRow>
                  ) : (
                    results.filter(r => r.status === 'pending').map((result) => (
                      <TableRow key={result.id}>
                        <TableCell>
                          <div>
                            <div className="font-medium">{result.course_code}</div>
                            <div className="text-xs text-muted-foreground">{result.course_title}</div>
                          </div>
                        </TableCell>
                        <TableCell>{result.lecturer_name}</TableCell>
                        <TableCell>{getResultTypeBadge(result.result_type)}</TableCell>
                        <TableCell>{result.students_count} students</TableCell>
                        <TableCell>
                          <div className="text-sm">
                            <div>Sem {result.semester}</div>
                            <div className="text-xs text-muted-foreground">{result.academic_year}</div>
                          </div>
                        </TableCell>
                        <TableCell>
                          {new Date(result.submitted_at).toLocaleDateString()}
                        </TableCell>
                        <TableCell>{getStatusBadge(result.status)}</TableCell>
                        <TableCell>
                          <div className="flex gap-2">
                            <Button
                              size="sm"
                              variant="outline"
                              onClick={() => handleViewDetails(result)}
                            >
                              <Eye className="mr-1 h-3 w-3" />
                              Review
                            </Button>
                            <Button
                              size="sm"
                              variant="default"
                              onClick={() => handleApprove(result.id)}
                            >
                              <CheckCircle className="mr-1 h-3 w-3" />
                              Approve
                            </Button>
                            <Button
                              size="sm"
                              variant="destructive"
                              onClick={() => {
                                setSelectedResult(result);
                                setShowRejectDialog(true);
                              }}
                            >
                              <XCircle className="mr-1 h-3 w-3" />
                              Reject
                            </Button>
                          </div>
                        </TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="ca" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>CA Score Submissions</CardTitle>
              <CardDescription>Continuous assessment scores submitted by lecturers</CardDescription>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground">Showing CA score submissions only...</p>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="exam" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Exam Result Submissions</CardTitle>
              <CardDescription>Final exam results submitted by lecturers</CardDescription>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground">Showing exam result submissions only...</p>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="approved" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Approved Results</CardTitle>
              <CardDescription>Results approved and ready for publishing</CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Course</TableHead>
                    <TableHead>Lecturer</TableHead>
                    <TableHead>Type</TableHead>
                    <TableHead>Students</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {results.filter(r => r.status === 'approved').length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={6} className="text-center text-muted-foreground">
                        No approved results
                      </TableCell>
                    </TableRow>
                  ) : (
                    results.filter(r => r.status === 'approved').map((result) => (
                      <TableRow key={result.id}>
                        <TableCell>
                          <div>
                            <div className="font-medium">{result.course_code}</div>
                            <div className="text-xs text-muted-foreground">{result.course_title}</div>
                          </div>
                        </TableCell>
                        <TableCell>{result.lecturer_name}</TableCell>
                        <TableCell>{getResultTypeBadge(result.result_type)}</TableCell>
                        <TableCell>{result.students_count} students</TableCell>
                        <TableCell>{getStatusBadge(result.status)}</TableCell>
                        <TableCell>
                          <Button
                            size="sm"
                            variant="default"
                            onClick={() => {
                              setSelectedResult(result);
                              setShowPublishDialog(true);
                            }}
                          >
                            <Upload className="mr-1 h-3 w-3" />
                            Publish to Students
                          </Button>
                        </TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="published" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Published Results</CardTitle>
              <CardDescription>Results that have been published to students</CardDescription>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground">Showing published results...</p>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      {/* View Details Dialog */}
      <Dialog open={showDetailDialog} onOpenChange={setShowDetailDialog}>
        <DialogContent className="max-w-4xl">
          <DialogHeader>
            <DialogTitle>Results Review - {selectedResult?.course_code}</DialogTitle>
            <DialogDescription>
              {selectedResult?.course_title} • {selectedResult?.result_type === 'ca' ? 'CA Scores' : 'Exam Results'}
            </DialogDescription>
          </DialogHeader>
          {selectedResult && (
            <div className="space-y-4">
              <div className="grid grid-cols-3 gap-4 p-4 bg-muted rounded-lg">
                <div>
                  <Label className="text-xs text-muted-foreground">Lecturer</Label>
                  <p className="font-medium">{selectedResult.lecturer_name}</p>
                </div>
                <div>
                  <Label className="text-xs text-muted-foreground">Students</Label>
                  <p className="font-medium">{selectedResult.students_count}</p>
                </div>
                <div>
                  <Label className="text-xs text-muted-foreground">Submitted</Label>
                  <p className="font-medium">{new Date(selectedResult.submitted_at).toLocaleString()}</p>
                </div>
              </div>

              <Alert>
                <TrendingUp className="h-4 w-4" />
                <AlertDescription>
                  Review all scores carefully before approving. Once approved, results can be published to students.
                </AlertDescription>
              </Alert>

              <div className="max-h-96 overflow-y-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Matric Number</TableHead>
                      <TableHead>Student Name</TableHead>
                      {selectedResult.result_type === 'ca' && <TableHead>CA Score (30)</TableHead>}
                      {selectedResult.result_type === 'exam' && (
                        <>
                          <TableHead>CA Score (30)</TableHead>
                          <TableHead>Exam Score (70)</TableHead>
                          <TableHead>Total (100)</TableHead>
                          <TableHead>Grade</TableHead>
                        </>
                      )}
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {studentResults.map((student) => (
                      <TableRow key={student.id}>
                        <TableCell className="font-mono">{student.matric_number}</TableCell>
                        <TableCell>{student.student_name}</TableCell>
                        {selectedResult.result_type === 'ca' && (
                          <TableCell>
                            <Badge variant="outline">{student.ca_score}/30</Badge>
                          </TableCell>
                        )}
                        {selectedResult.result_type === 'exam' && (
                          <>
                            <TableCell>{student.ca_score}/30</TableCell>
                            <TableCell>{student.exam_score}/70</TableCell>
                            <TableCell className="font-bold">{student.total_score}/100</TableCell>
                            <TableCell>
                              <Badge variant="default">{student.grade}</Badge>
                            </TableCell>
                          </>
                        )}
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </div>

              <div className="flex justify-end gap-2 pt-4 border-t">
                <Button variant="outline" onClick={() => setShowDetailDialog(false)}>
                  Close
                </Button>
                <Button
                  variant="destructive"
                  onClick={() => {
                    setShowDetailDialog(false);
                    setShowRejectDialog(true);
                  }}
                >
                  <XCircle className="mr-2 h-4 w-4" />
                  Reject Results
                </Button>
                <Button
                  variant="default"
                  onClick={() => {
                    handleApprove(selectedResult.id);
                    setShowDetailDialog(false);
                  }}
                >
                  <CheckCircle className="mr-2 h-4 w-4" />
                  Approve Results
                </Button>
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>

      {/* Reject Dialog */}
      <Dialog open={showRejectDialog} onOpenChange={setShowRejectDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Reject Results</DialogTitle>
            <DialogDescription>
              Provide feedback for the lecturer to make corrections
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4">
            <Alert variant="destructive">
              <AlertTriangle className="h-4 w-4" />
              <AlertDescription>
                Results will be returned to the lecturer for corrections
              </AlertDescription>
            </Alert>
            <div>
              <Label>Reason for Rejection *</Label>
              <Textarea
                value={rejectReason}
                onChange={(e) => setRejectReason(e.target.value)}
                placeholder="Provide detailed feedback (e.g., score discrepancies, missing students, etc.)"
                rows={4}
              />
            </div>
            <div className="flex justify-end gap-2">
              <Button variant="outline" onClick={() => setShowRejectDialog(false)}>
                Cancel
              </Button>
              <Button variant="destructive" onClick={handleReject}>
                Reject & Return to Lecturer
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* Publish Dialog */}
      <Dialog open={showPublishDialog} onOpenChange={setShowPublishDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Publish Results to Students</DialogTitle>
            <DialogDescription>
              Make results visible to students
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4">
            <Alert>
              <Upload className="h-4 w-4" />
              <AlertDescription>
                Results will be immediately visible to students after publishing
              </AlertDescription>
            </Alert>
            {selectedResult && (
              <div className="p-4 bg-muted rounded-lg space-y-2">
                <div className="flex justify-between">
                  <span className="text-sm text-muted-foreground">Course:</span>
                  <span className="font-medium">{selectedResult.course_code}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-muted-foreground">Type:</span>
                  <span>{selectedResult.result_type === 'ca' ? 'CA Scores' : 'Exam Results'}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-muted-foreground">Students:</span>
                  <span>{selectedResult.students_count}</span>
                </div>
              </div>
            )}
            <div className="flex justify-end gap-2">
              <Button variant="outline" onClick={() => setShowPublishDialog(false)}>
                Cancel
              </Button>
              <Button variant="default" onClick={handlePublish}>
                <Upload className="mr-2 h-4 w-4" />
                Publish Now
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
}
