import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { 
  GraduationCap, 
  TrendingUp, 
  Award, 
  BookOpen, 
  CheckCircle2, 
  XCircle,
  Download,
  FileText
} from 'lucide-react';
import { degreeProgressApi, type DegreeProgress, type Transcript } from '@/lib/academicApi';
import { useToast } from '@/hooks/use-toast';
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from '@/components/ui/tabs';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

interface DegreeProgressTrackerProps {
  studentId: number;
}

const REQUIREMENT_TYPE_LABELS: Record<string, string> = {
  core: 'Core Courses',
  major: 'Major Courses',
  minor: 'Minor Courses',
  elective: 'Electives',
  general_education: 'General Education',
};

const GRADE_COLORS: Record<string, string> = {
  'A+': 'text-green-600',
  'A': 'text-green-600',
  'A-': 'text-green-500',
  'B+': 'text-blue-600',
  'B': 'text-blue-500',
  'B-': 'text-blue-400',
  'C+': 'text-yellow-600',
  'C': 'text-yellow-500',
  'C-': 'text-yellow-400',
  'D': 'text-orange-500',
  'F': 'text-red-600',
};

export default function DegreeProgressTracker({ studentId }: DegreeProgressTrackerProps) {
  const [progress, setProgress] = useState<DegreeProgress | null>(null);
  const [transcript, setTranscript] = useState<Transcript | null>(null);
  const [remaining, setRemaining] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const { toast } = useToast();

  useEffect(() => {
    fetchProgress();
    fetchTranscript();
    fetchRemaining();
  }, [studentId]);

  const fetchProgress = async () => {
    try {
      setLoading(true);
      const response = await degreeProgressApi.getProgress(studentId);
      if (response.success) {
        setProgress(response.data);
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to fetch degree progress',
        variant: 'destructive'
      });
    } finally {
      setLoading(false);
    }
  };

  const fetchTranscript = async () => {
    try {
      const response = await degreeProgressApi.getTranscript(studentId);
      if (response.success) {
        setTranscript(response.data);
      }
    } catch (error) {
      console.error('Failed to fetch transcript:', error);
    }
  };

  const fetchRemaining = async () => {
    try {
      const response = await degreeProgressApi.getRemainingRequirements(studentId);
      if (response.success) {
        setRemaining(response.data);
      }
    } catch (error) {
      console.error('Failed to fetch remaining requirements:', error);
    }
  };

  const downloadTranscript = () => {
    if (!transcript) return;
    
    const content = `
OFFICIAL TRANSCRIPT
${transcript.student.name}
Student ID: ${transcript.student.student_id}
Email: ${transcript.student.email}

Generated: ${new Date(transcript.generated_at).toLocaleDateString()}

===================================
COURSES
===================================

${transcript.transcript.map(course => `
${course.course_code} - ${course.course_name}
Credits: ${course.credits} | Grade: ${course.letter_grade} (${course.grade_point}) | Quality Points: ${course.quality_points}
`).join('\n')}

===================================
SUMMARY
===================================
Total Credits: ${transcript.summary.total_credits}
Total Quality Points: ${transcript.summary.total_quality_points}
CGPA: ${transcript.summary.cgpa}
Total Courses: ${transcript.summary.total_courses}
`;

    const blob = new Blob([content], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `transcript_${transcript.student.student_id}.txt`;
    a.click();
  };

  if (loading || !progress) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">Degree Progress</h2>
          <p className="text-muted-foreground">
            {progress.program.program_name} - {progress.program.program_code}
          </p>
        </div>
        <Button onClick={downloadTranscript} variant="outline">
          <Download className="h-4 w-4 mr-2" />
          Download Transcript
        </Button>
      </div>

      {/* Overall Progress Cards */}
      <div className="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">CGPA</CardTitle>
            <TrendingUp className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{progress.progress.cgpa.toFixed(2)}</div>
            <p className="text-xs text-muted-foreground">
              Min required: {progress.progress.minimum_cgpa || 'N/A'}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Credits Earned</CardTitle>
            <BookOpen className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {progress.progress.credits_earned}/{progress.progress.credits_required}
            </div>
            <Progress value={progress.progress.credits_percentage} className="mt-2" />
            <p className="text-xs text-muted-foreground mt-1">
              {progress.progress.credits_percentage.toFixed(1)}% complete
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Completed Courses</CardTitle>
            <CheckCircle2 className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{progress.completed_courses.length}</div>
            <p className="text-xs text-muted-foreground">Courses completed</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Graduation Status</CardTitle>
            <GraduationCap className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            {progress.graduation_eligibility.eligible ? (
              <>
                <div className="text-2xl font-bold text-green-600">Eligible</div>
                <p className="text-xs text-muted-foreground">Ready to graduate!</p>
              </>
            ) : (
              <>
                <div className="text-2xl font-bold text-orange-600">Not Eligible</div>
                <p className="text-xs text-muted-foreground">
                  {progress.graduation_eligibility.reasons.length} requirement(s) pending
                </p>
              </>
            )}
          </CardContent>
        </Card>
      </div>

      {/* Graduation Eligibility */}
      {!progress.graduation_eligibility.eligible && (
        <Card className="border-orange-200 bg-orange-50">
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-orange-800">
              <XCircle className="h-5 w-5" />
              Graduation Requirements Not Met
            </CardTitle>
          </CardHeader>
          <CardContent>
            <ul className="list-disc list-inside space-y-1 text-sm text-orange-700">
              {progress.graduation_eligibility.reasons.map((reason, idx) => (
                <li key={idx}>{reason}</li>
              ))}
            </ul>
          </CardContent>
        </Card>
      )}

      {/* Tabs for different views */}
      <Tabs defaultValue="progress" className="space-y-4">
        <TabsList>
          <TabsTrigger value="progress">Progress by Type</TabsTrigger>
          <TabsTrigger value="transcript">Transcript</TabsTrigger>
          <TabsTrigger value="remaining">Remaining Requirements</TabsTrigger>
        </TabsList>

        {/* Progress by Requirement Type */}
        <TabsContent value="progress" className="space-y-4">
          {Object.entries(progress.progress.requirement_completion).map(([type, completion]) => (
            <Card key={type}>
              <CardHeader>
                <CardTitle className="flex items-center justify-between">
                  <span>{REQUIREMENT_TYPE_LABELS[type] || type}</span>
                  <Badge variant={completion.percentage === 100 ? 'default' : 'secondary'}>
                    {completion.completed}/{completion.total} courses
                  </Badge>
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <div className="flex justify-between text-sm mb-2">
                    <span>Overall Progress</span>
                    <span className="font-medium">{completion.percentage.toFixed(1)}%</span>
                  </div>
                  <Progress value={completion.percentage} />
                </div>
                {completion.mandatory > 0 && (
                  <div>
                    <div className="flex justify-between text-sm mb-2">
                      <span>Mandatory Courses</span>
                      <span className="font-medium">
                        {completion.mandatory_completed}/{completion.mandatory} ({completion.mandatory_percentage.toFixed(1)}%)
                      </span>
                    </div>
                    <Progress value={completion.mandatory_percentage} className="bg-red-100" />
                  </div>
                )}
              </CardContent>
            </Card>
          ))}
        </TabsContent>

        {/* Transcript Tab */}
        <TabsContent value="transcript">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <FileText className="h-5 w-5" />
                Academic Transcript
              </CardTitle>
              <CardDescription>Complete record of all completed courses</CardDescription>
            </CardHeader>
            <CardContent>
              {transcript && (
                <>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Course Code</TableHead>
                        <TableHead>Course Name</TableHead>
                        <TableHead className="text-center">Credits</TableHead>
                        <TableHead className="text-center">Grade</TableHead>
                        <TableHead className="text-center">Grade Point</TableHead>
                        <TableHead className="text-right">Quality Points</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {transcript.transcript.map((course, idx) => (
                        <TableRow key={idx}>
                          <TableCell className="font-medium">{course.course_code}</TableCell>
                          <TableCell>{course.course_name}</TableCell>
                          <TableCell className="text-center">{course.credits}</TableCell>
                          <TableCell className="text-center">
                            <span className={`font-semibold ${GRADE_COLORS[course.letter_grade]}`}>
                              {course.letter_grade}
                            </span>
                          </TableCell>
                          <TableCell className="text-center">{course.grade_point.toFixed(2)}</TableCell>
                          <TableCell className="text-right">{course.quality_points.toFixed(2)}</TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                  
                  <div className="mt-6 p-4 bg-muted rounded-lg">
                    <h3 className="font-semibold mb-3">Summary</h3>
                    <div className="grid gap-2 md:grid-cols-4">
                      <div>
                        <div className="text-sm text-muted-foreground">Total Credits</div>
                        <div className="text-2xl font-bold">{transcript.summary.total_credits}</div>
                      </div>
                      <div>
                        <div className="text-sm text-muted-foreground">Quality Points</div>
                        <div className="text-2xl font-bold">{transcript.summary.total_quality_points.toFixed(2)}</div>
                      </div>
                      <div>
                        <div className="text-sm text-muted-foreground">CGPA</div>
                        <div className="text-2xl font-bold text-primary">{transcript.summary.cgpa.toFixed(2)}</div>
                      </div>
                      <div>
                        <div className="text-sm text-muted-foreground">Total Courses</div>
                        <div className="text-2xl font-bold">{transcript.summary.total_courses}</div>
                      </div>
                    </div>
                  </div>
                </>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        {/* Remaining Requirements Tab */}
        <TabsContent value="remaining">
          <Card>
            <CardHeader>
              <CardTitle>Remaining Requirements</CardTitle>
              <CardDescription>
                Courses you still need to complete for graduation
              </CardDescription>
            </CardHeader>
            <CardContent>
              {remaining && (
                <div className="space-y-6">
                  {Object.entries(remaining.remaining_requirements).map(([type, courses]: [string, any]) => (
                    <div key={type}>
                      <h3 className="font-semibold text-lg mb-3 flex items-center justify-between">
                        <span>{REQUIREMENT_TYPE_LABELS[type] || type}</span>
                        <Badge>{courses.length} remaining</Badge>
                      </h3>
                      <div className="space-y-2">
                        {courses.map((req: any) => (
                          <div key={req.id} className="border rounded-lg p-3 hover:shadow-md transition-shadow">
                            <div className="flex justify-between items-start">
                              <div>
                                <div className="font-semibold">{req.course?.name}</div>
                                <div className="text-sm text-muted-foreground">
                                  {req.course?.course_code} • {req.course?.credits} credits
                                </div>
                                {req.semester_recommended && (
                                  <div className="text-xs text-muted-foreground mt-1">
                                    Recommended: Semester {req.semester_recommended}
                                  </div>
                                )}
                              </div>
                              {req.is_mandatory && (
                                <Badge variant="destructive">Mandatory</Badge>
                              )}
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}
