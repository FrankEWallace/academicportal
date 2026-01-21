import { useState, useEffect } from 'react';
import DashboardLayout from '@/components/DashboardLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { apiClient } from '@/lib/api';
import { Download, Upload, RefreshCw, CheckCircle2, XCircle, Clock, AlertCircle } from 'lucide-react';
import { format } from 'date-fns';

interface ImportLog {
  id: number;
  type: string;
  filename: string;
  status: 'pending' | 'processing' | 'completed' | 'failed' | 'rolled_back';
  total_rows: number;
  success_count: number;
  error_count: number;
  errors: Record<string, string> | null;
  started_at: string | null;
  completed_at: string | null;
  created_at: string;
  user: {
    id: number;
    name: string;
  };
}

interface ImportStatistics {
  total_imports: number;
  completed: number;
  failed: number;
  processing: number;
  by_type: Array<{
    type: string;
    count: number;
    total_records: number;
  }>;
  recent_imports: ImportLog[];
}

export default function BulkImport() {
  const [importType, setImportType] = useState<string>('students');
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [uploading, setUploading] = useState(false);
  const [imports, setImports] = useState<ImportLog[]>([]);
  const [statistics, setStatistics] = useState<ImportStatistics | null>(null);
  const [loading, setLoading] = useState(false);
  const [uploadMessage, setUploadMessage] = useState<{ type: 'success' | 'error'; message: string } | null>(null);

  useEffect(() => {
    fetchImports();
    fetchStatistics();
    
    // Poll for updates every 5 seconds
    const interval = setInterval(() => {
      fetchImports();
      fetchStatistics();
    }, 5000);

    return () => clearInterval(interval);
  }, []);

  const fetchImports = async () => {
    try {
      const response = await apiClient.get('/admin/bulk-import/imports');
      setImports(response.data.data);
    } catch (error) {
      console.error('Failed to fetch imports:', error);
    }
  };

  const fetchStatistics = async () => {
    try {
      const response = await apiClient.get('/admin/bulk-import/statistics');
      setStatistics(response.data);
    } catch (error) {
      console.error('Failed to fetch statistics:', error);
    }
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setSelectedFile(e.target.files[0]);
      setUploadMessage(null);
    }
  };

  const handleUpload = async () => {
    if (!selectedFile) {
      setUploadMessage({ type: 'error', message: 'Please select a file' });
      return;
    }

    setUploading(true);
    setUploadMessage(null);

    const formData = new FormData();
    formData.append('file', selectedFile);
    formData.append('type', importType);

    try {
      const response = await apiClient.post('/admin/bulk-import/upload', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      setUploadMessage({ type: 'success', message: response.data.message });
      setSelectedFile(null);
      
      // Reset file input
      const fileInput = document.getElementById('file-upload') as HTMLInputElement;
      if (fileInput) fileInput.value = '';

      // Refresh imports list
      await fetchImports();
      await fetchStatistics();
    } catch (error: any) {
      setUploadMessage({
        type: 'error',
        message: error.response?.data?.message || 'Upload failed',
      });
    } finally {
      setUploading(false);
    }
  };

  const handleDownloadTemplate = async (type: string) => {
    try {
      const response = await apiClient.get(`/admin/bulk-import/templates/${type}`, {
        responseType: 'blob',
      });

      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `${type}_template.csv`);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (error) {
      console.error('Failed to download template:', error);
    }
  };

  const handleDeleteImport = async (id: number) => {
    if (!confirm('Are you sure you want to delete this import log?')) {
      return;
    }

    try {
      await apiClient.delete(`/admin/bulk-import/imports/${id}`);
      await fetchImports();
      await fetchStatistics();
    } catch (error: any) {
      alert(error.response?.data?.message || 'Failed to delete import');
    }
  };

  const getStatusBadge = (status: string) => {
    const variants: Record<string, { variant: any; icon: any }> = {
      pending: { variant: 'secondary', icon: Clock },
      processing: { variant: 'default', icon: RefreshCw },
      completed: { variant: 'default', icon: CheckCircle2 },
      failed: { variant: 'destructive', icon: XCircle },
      rolled_back: { variant: 'outline', icon: AlertCircle },
    };

    const config = variants[status] || variants.pending;
    const Icon = config.icon;

    return (
      <Badge variant={config.variant} className="flex items-center gap-1 w-fit">
        <Icon className="h-3 w-3" />
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </Badge>
    );
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold">Bulk Import</h1>
          <p className="text-muted-foreground">Import students, courses, grades, and invoices in bulk via CSV</p>
        </div>

        {/* Statistics */}
        {statistics && (
          <div className="grid gap-4 md:grid-cols-4">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Total Imports</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{statistics.total_imports}</div>
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Completed</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold text-green-600">{statistics.completed}</div>
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Failed</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold text-red-600">{statistics.failed}</div>
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Processing</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold text-blue-600">{statistics.processing}</div>
              </CardContent>
            </Card>
          </div>
        )}

        <Tabs defaultValue="upload" className="space-y-4">
          <TabsList>
            <TabsTrigger value="upload">Upload CSV</TabsTrigger>
            <TabsTrigger value="history">Import History</TabsTrigger>
            <TabsTrigger value="templates">Templates</TabsTrigger>
          </TabsList>

          <TabsContent value="upload" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Upload CSV File</CardTitle>
                <CardDescription>
                  Select the type of import and upload your CSV file. The file will be processed in the background.
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="import-type">Import Type</Label>
                  <Select value={importType} onValueChange={setImportType}>
                    <SelectTrigger id="import-type">
                      <SelectValue placeholder="Select import type" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="students">Students</SelectItem>
                      <SelectItem value="courses">Courses</SelectItem>
                      <SelectItem value="grades">Grades/Results</SelectItem>
                      <SelectItem value="invoices">Invoices (Coming Soon)</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="file-upload">CSV File</Label>
                  <Input
                    id="file-upload"
                    type="file"
                    accept=".csv"
                    onChange={handleFileChange}
                    disabled={uploading}
                  />
                  <p className="text-sm text-muted-foreground">
                    Maximum file size: 10MB. Download a template below to see the required format.
                  </p>
                </div>

                {uploadMessage && (
                  <Alert variant={uploadMessage.type === 'error' ? 'destructive' : 'default'}>
                    <AlertDescription>{uploadMessage.message}</AlertDescription>
                  </Alert>
                )}

                <Button onClick={handleUpload} disabled={uploading || !selectedFile}>
                  <Upload className="mr-2 h-4 w-4" />
                  {uploading ? 'Uploading...' : 'Upload and Process'}
                </Button>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="history" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Import History</CardTitle>
                <CardDescription>View all import logs and their status</CardDescription>
              </CardHeader>
              <CardContent>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Type</TableHead>
                      <TableHead>Filename</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Progress</TableHead>
                      <TableHead>Success</TableHead>
                      <TableHead>Errors</TableHead>
                      <TableHead>Date</TableHead>
                      <TableHead>Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {imports.map((importLog) => {
                      const progress = importLog.total_rows > 0
                        ? ((importLog.success_count + importLog.error_count) / importLog.total_rows) * 100
                        : 0;

                      return (
                        <TableRow key={importLog.id}>
                          <TableCell className="font-medium capitalize">{importLog.type}</TableCell>
                          <TableCell>{importLog.filename}</TableCell>
                          <TableCell>{getStatusBadge(importLog.status)}</TableCell>
                          <TableCell>
                            <div className="space-y-1">
                              <Progress value={progress} className="w-[100px]" />
                              <p className="text-xs text-muted-foreground">
                                {importLog.success_count + importLog.error_count} / {importLog.total_rows}
                              </p>
                            </div>
                          </TableCell>
                          <TableCell className="text-green-600">{importLog.success_count}</TableCell>
                          <TableCell className="text-red-600">{importLog.error_count}</TableCell>
                          <TableCell>{format(new Date(importLog.created_at), 'MMM dd, yyyy HH:mm')}</TableCell>
                          <TableCell>
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => handleDeleteImport(importLog.id)}
                              disabled={importLog.status === 'processing'}
                            >
                              Delete
                            </Button>
                          </TableCell>
                        </TableRow>
                      );
                    })}
                    {imports.length === 0 && (
                      <TableRow>
                        <TableCell colSpan={8} className="text-center text-muted-foreground">
                          No imports found
                        </TableCell>
                      </TableRow>
                    )}
                  </TableBody>
                </Table>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="templates" className="space-y-4">
            <div className="grid gap-4 md:grid-cols-2">
              <Card>
                <CardHeader>
                  <CardTitle>Student Import Template</CardTitle>
                  <CardDescription>
                    Download the CSV template for importing students
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <p className="text-sm mb-4">
                    Required columns: name, email, student_id, phone, date_of_birth, department_id, program_id, entry_year
                  </p>
                  <Button onClick={() => handleDownloadTemplate('students')}>
                    <Download className="mr-2 h-4 w-4" />
                    Download Template
                  </Button>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Course Import Template</CardTitle>
                  <CardDescription>
                    Download the CSV template for importing courses
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <p className="text-sm mb-4">
                    Required columns: course_code, course_name, credits, department_id, semester, level, description
                  </p>
                  <Button onClick={() => handleDownloadTemplate('courses')}>
                    <Download className="mr-2 h-4 w-4" />
                    Download Template
                  </Button>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Grades/Results Import Template</CardTitle>
                  <CardDescription>
                    Download the CSV template for importing student grades
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <p className="text-sm mb-4">
                    Required columns: student_id, course_code, exam_score, ca_score, total_score, grade, semester_id, academic_year
                  </p>
                  <Button onClick={() => handleDownloadTemplate('grades')}>
                    <Download className="mr-2 h-4 w-4" />
                    Download Template
                  </Button>
                </CardContent>
              </Card>
            </div>
          </TabsContent>
        </Tabs>
      </div>
    </DashboardLayout>
  );
}
