import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import LecturerCAManagement from './LecturerCAManagement';
import LecturerResultsManagement from './LecturerResultsManagement';

export default function LecturerDashboard() {
  return (
    <div className="min-h-screen bg-background">
      <div className="border-b">
        <div className="container mx-auto px-4 py-4">
          <h1 className="text-2xl font-bold">Lecturer Dashboard</h1>
          <p className="text-muted-foreground">Manage course assessments and results</p>
        </div>
      </div>

      <div className="container mx-auto px-4 py-6">
        <Tabs defaultValue="ca" className="space-y-4">
          <TabsList className="grid w-full max-w-md grid-cols-2">
            <TabsTrigger value="ca">CA Management</TabsTrigger>
            <TabsTrigger value="results">Results Management</TabsTrigger>
          </TabsList>

          <TabsContent value="ca">
            <LecturerCAManagement />
          </TabsContent>

          <TabsContent value="results">
            <LecturerResultsManagement />
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
}
