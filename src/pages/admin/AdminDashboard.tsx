import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AdminRegistrationControl from './AdminRegistrationControl';
import AdminAccommodationManagement from './AdminAccommodationManagement';
import AdminInsuranceVerification from './AdminInsuranceVerification';
import AdminEnrollmentApproval from './AdminEnrollmentApproval';
import AdminResultsModeration from './AdminResultsModeration';
import AdminFeedbackManagement from './AdminFeedbackManagement';
import { ScrollArea } from '@/components/ui/scroll-area';

export default function AdminDashboard() {
  return (
    <div className="min-h-screen bg-background">
      <div className="border-b">
        <div className="container mx-auto px-4 py-4">
          <h1 className="text-2xl font-bold">Administrator Dashboard</h1>
          <p className="text-muted-foreground">
            Manage registrations, enrollments, insurance, accommodations, and student feedback
          </p>
        </div>
      </div>

      <div className="container mx-auto px-4 py-6">
        <Tabs defaultValue="registrations" className="space-y-4">
          <ScrollArea className="w-full">
            <TabsList className="inline-flex w-full min-w-max">
              <TabsTrigger value="registrations">Registrations</TabsTrigger>
              <TabsTrigger value="insurance">Insurance</TabsTrigger>
              <TabsTrigger value="enrollments">Enrollments</TabsTrigger>
              <TabsTrigger value="results">Results Moderation</TabsTrigger>
              <TabsTrigger value="accommodations">Accommodations</TabsTrigger>
              <TabsTrigger value="feedback">Feedback</TabsTrigger>
            </TabsList>
          </ScrollArea>

          <TabsContent value="registrations">
            <AdminRegistrationControl />
          </TabsContent>

          <TabsContent value="insurance">
            <AdminInsuranceVerification />
          </TabsContent>

          <TabsContent value="enrollments">
            <AdminEnrollmentApproval />
          </TabsContent>

          <TabsContent value="results">
            <AdminResultsModeration />
          </TabsContent>

          <TabsContent value="accommodations">
            <AdminAccommodationManagement />
          </TabsContent>

          <TabsContent value="feedback">
            <AdminFeedbackManagement />
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
}
