import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AdminRegistrationControl from './AdminRegistrationControl';
import AdminAccommodationManagement from './AdminAccommodationManagement';
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
            <div className="p-6">
              <h2 className="text-xl font-bold mb-4">Insurance Verification</h2>
              <p className="text-muted-foreground">
                Insurance verification component - Coming soon
              </p>
            </div>
          </TabsContent>

          <TabsContent value="enrollments">
            <div className="p-6">
              <h2 className="text-xl font-bold mb-4">Enrollment Approval</h2>
              <p className="text-muted-foreground">
                Enrollment approval component - Coming soon
              </p>
            </div>
          </TabsContent>

          <TabsContent value="results">
            <div className="p-6">
              <h2 className="text-xl font-bold mb-4">Results Moderation</h2>
              <p className="text-muted-foreground">
                Results moderation component - Coming soon
              </p>
            </div>
          </TabsContent>

          <TabsContent value="accommodations">
            <AdminAccommodationManagement />
          </TabsContent>

          <TabsContent value="feedback">
            <div className="p-6">
              <h2 className="text-xl font-bold mb-4">Feedback Management</h2>
              <p className="text-muted-foreground">
                Feedback management component - Coming soon
              </p>
            </div>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
}
