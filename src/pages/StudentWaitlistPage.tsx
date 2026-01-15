import { DashboardLayout } from "@/components/DashboardLayout";
import WaitlistManagement from "@/pages/WaitlistManagement";
import { useCurrentUser } from "@/hooks/useApi";

const StudentWaitlistPage = () => {
  const { data: currentUser } = useCurrentUser();
  // @ts-ignore - We'll handle type definitions later
  const studentId = currentUser?.data?.student?.id || currentUser?.data?.user?.id;

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Course Waitlist</h1>
          <p className="text-muted-foreground">
            Manage your course waitlist positions
          </p>
        </div>
        
        <WaitlistManagement viewMode="student" studentId={studentId} />
      </div>
    </DashboardLayout>
  );
};

export default StudentWaitlistPage;
