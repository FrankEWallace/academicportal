import { DashboardLayout } from "@/components/DashboardLayout";
import DegreeProgressTracker from "@/pages/DegreeProgressTracker";
import { useCurrentUser } from "@/hooks/useApi";

const StudentProgressPage = () => {
  const { data: currentUser } = useCurrentUser();
  // @ts-ignore - We'll handle type definitions later
  const studentId = currentUser?.data?.student?.id || currentUser?.data?.user?.id;

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Degree Progress</h1>
          <p className="text-muted-foreground">
            Track your progress towards graduation
          </p>
        </div>
        
        <DegreeProgressTracker studentId={studentId} />
      </div>
    </DashboardLayout>
  );
};

export default StudentProgressPage;
