import { useState } from 'react';
import { DashboardLayout } from '@/components/DashboardLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { useToast } from '@/hooks/use-toast';
import {
  FileText,
  GraduationCap,
  Receipt,
  Calendar,
  CreditCard,
  Printer,
  Download,
  Loader2,
  BookOpen,
  Building,
  ClipboardList,
} from 'lucide-react';
import {
  printAdmissionLetter,
  downloadTranscript,
  generatePaymentReceipt,
  generateInvoiceReceipt,
  printCourseRegistration,
  downloadTimetable,
  printIDCard,
  downloadAllocationLetter,
} from '@/services/printService';
import { useCurrentUser, useStudentCourses } from '@/hooks/useApi';
import { Alert, AlertDescription } from '@/components/ui/alert';

interface DocumentCardProps {
  title: string;
  description: string;
  icon: React.ReactNode;
  onPrint: () => Promise<void>;
  onDownload: () => Promise<void>;
  available: boolean;
  badge?: string;
  badgeVariant?: 'default' | 'secondary' | 'success' | 'destructive' | 'outline';
}

const DocumentCard = ({
  title,
  description,
  icon,
  onPrint,
  onDownload,
  available,
  badge,
  badgeVariant = 'default',
}: DocumentCardProps) => {
  const [isPrinting, setIsPrinting] = useState(false);
  const [isDownloading, setIsDownloading] = useState(false);
  const { toast } = useToast();

  const handlePrint = async () => {
    setIsPrinting(true);
    try {
      await onPrint();
      toast({
        title: 'Print initiated',
        description: 'Your document has been sent to the printer.',
      });
    } catch (error) {
      toast({
        title: 'Print failed',
        description: 'Failed to print document. Please try again.',
        variant: 'destructive',
      });
    } finally {
      setIsPrinting(false);
    }
  };

  const handleDownload = async () => {
    setIsDownloading(true);
    try {
      await onDownload();
      toast({
        title: 'Download started',
        description: 'Your document is being downloaded.',
      });
    } catch (error) {
      toast({
        title: 'Download failed',
        description: 'Failed to download document. Please try again.',
        variant: 'destructive',
      });
    } finally {
      setIsDownloading(false);
    }
  };

  return (
    <Card className={!available ? 'opacity-60' : ''}>
      <CardHeader>
        <div className="flex items-start justify-between">
          <div className="flex items-center gap-3">
            <div className="p-2 bg-primary/10 rounded-lg">{icon}</div>
            <div>
              <CardTitle className="text-lg">{title}</CardTitle>
              <CardDescription className="mt-1">{description}</CardDescription>
            </div>
          </div>
          {badge && (
            <Badge variant={badgeVariant as any} className="ml-2">
              {badge}
            </Badge>
          )}
        </div>
      </CardHeader>
      <CardContent>
        <div className="flex gap-2">
          <Button
            onClick={handlePrint}
            disabled={!available || isPrinting}
            className="flex-1"
            variant="default"
          >
            {isPrinting ? (
              <>
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                Printing...
              </>
            ) : (
              <>
                <Printer className="mr-2 h-4 w-4" />
                Print
              </>
            )}
          </Button>
          <Button
            onClick={handleDownload}
            disabled={!available || isDownloading}
            className="flex-1"
            variant="outline"
          >
            {isDownloading ? (
              <>
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                Downloading...
              </>
            ) : (
              <>
                <Download className="mr-2 h-4 w-4" />
                Download
              </>
            )}
          </Button>
        </div>
      </CardContent>
    </Card>
  );
};

const PrintForms = () => {
  const { data: currentUser, isLoading } = useCurrentUser();
  const { data: enrollments } = useStudentCourses();

  const hasActiveEnrollment = enrollments?.some((e) => e.status === 'enrolled') || false;
  const studentData = currentUser?.data?.user;

  if (isLoading) {
    return (
      <DashboardLayout>
        <div className="flex items-center justify-center h-64">
          <Loader2 className="h-8 w-8 animate-spin" />
          <span className="ml-2">Loading documents...</span>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="space-y-6">
        {/* Header */}
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Print & Download Forms</h1>
          <p className="text-muted-foreground mt-2">
            Access and download your academic documents and forms
          </p>
        </div>

        {/* Info Alert */}
        <Alert>
          <FileText className="h-4 w-4" />
          <AlertDescription>
            All documents are generated in PDF format. Make sure you have a PDF reader installed to
            view them.
          </AlertDescription>
        </Alert>

        {/* Academic Documents */}
        <div className="space-y-4">
          <div className="flex items-center gap-2">
            <GraduationCap className="h-5 w-5 text-primary" />
            <h2 className="text-2xl font-semibold">Academic Documents</h2>
          </div>

          <div className="grid gap-4 md:grid-cols-2">
            <DocumentCard
              title="Admission Letter"
              description="Official admission letter to the university"
              icon={<FileText className="h-5 w-5 text-primary" />}
              onPrint={() => printAdmissionLetter({ autoPrint: true })}
              onDownload={() => printAdmissionLetter({ autoPrint: false })}
              available={!!studentData}
              badge="Official"
              badgeVariant="default"
            />

            <DocumentCard
              title="Academic Transcript"
              description="Complete record of your academic performance"
              icon={<GraduationCap className="h-5 w-5 text-success" />}
              onPrint={() => downloadTranscript({ autoPrint: true })}
              onDownload={() => downloadTranscript({ autoPrint: false })}
              available={!!studentData}
              badge="Confidential"
              badgeVariant="destructive"
            />

            <DocumentCard
              title="Course Registration Form"
              description="List of courses registered for current semester"
              icon={<ClipboardList className="h-5 w-5 text-info" />}
              onPrint={() => printCourseRegistration(undefined, { autoPrint: true })}
              onDownload={() => printCourseRegistration(undefined, { autoPrint: false })}
              available={hasActiveEnrollment}
              badge={hasActiveEnrollment ? 'Current' : 'No enrollment'}
              badgeVariant={hasActiveEnrollment ? 'success' : 'secondary'}
            />

            <DocumentCard
              title="Class Timetable"
              description="Your personalized class schedule"
              icon={<Calendar className="h-5 w-5 text-warning" />}
              onPrint={() => downloadTimetable({ autoPrint: true })}
              onDownload={() => downloadTimetable({ autoPrint: false })}
              available={hasActiveEnrollment}
              badge={hasActiveEnrollment ? 'Available' : 'No schedule'}
              badgeVariant={hasActiveEnrollment ? 'default' : 'secondary'}
            />
          </div>
        </div>

        <Separator />

        {/* Financial Documents */}
        <div className="space-y-4">
          <div className="flex items-center gap-2">
            <CreditCard className="h-5 w-5 text-primary" />
            <h2 className="text-2xl font-semibold">Financial Documents</h2>
          </div>

          <div className="grid gap-4 md:grid-cols-2">
            <DocumentCard
              title="Payment Receipts"
              description="Official receipts for all payments made"
              icon={<Receipt className="h-5 w-5 text-success" />}
              onPrint={async () => {
                // This will be enhanced with payment selection
                throw new Error('Please select a specific payment from Payment History');
              }}
              onDownload={async () => {
                throw new Error('Please select a specific payment from Payment History');
              }}
              available={false}
              badge="Select from history"
              badgeVariant="secondary"
            />

            <DocumentCard
              title="Fee Invoices"
              description="Detailed breakdown of fees and charges"
              icon={<FileText className="h-5 w-5 text-warning" />}
              onPrint={async () => {
                throw new Error('Please select a specific invoice from Registration page');
              }}
              onDownload={async () => {
                throw new Error('Please select a specific invoice from Registration page');
              }}
              available={false}
              badge="Select from registration"
              badgeVariant="secondary"
            />
          </div>
        </div>

        <Separator />

        {/* Personal Documents */}
        <div className="space-y-4">
          <div className="flex items-center gap-2">
            <CreditCard className="h-5 w-5 text-primary" />
            <h2 className="text-2xl font-semibold">Personal Documents</h2>
          </div>

          <div className="grid gap-4 md:grid-cols-2">
            <DocumentCard
              title="Student ID Card"
              description="Official student identification card"
              icon={<CreditCard className="h-5 w-5 text-primary" />}
              onPrint={() => printIDCard({ autoPrint: true })}
              onDownload={() => printIDCard({ autoPrint: false })}
              available={!!studentData}
              badge="Official"
              badgeVariant="default"
            />

            <DocumentCard
              title="Accommodation Letter"
              description="Hostel/accommodation allocation letter"
              icon={<Building className="h-5 w-5 text-info" />}
              onPrint={() => downloadAllocationLetter({ autoPrint: true })}
              onDownload={() => downloadAllocationLetter({ autoPrint: false })}
              available={!!studentData}
              badge="If applicable"
              badgeVariant="secondary"
            />
          </div>
        </div>

        {/* Help Section */}
        <Card className="bg-muted/50">
          <CardHeader>
            <CardTitle className="text-lg flex items-center gap-2">
              <BookOpen className="h-5 w-5" />
              Need Help?
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-2 text-sm text-muted-foreground">
            <p>• Documents marked as "unavailable" are not yet ready for download</p>
            <p>• Payment receipts and invoices can be accessed from their respective pages</p>
            <p>• All official documents are digitally signed and verifiable</p>
            <p>
              • For issues with document generation, contact the registrar's office at
              registrar@academicnexus.edu
            </p>
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
};

export default PrintForms;
