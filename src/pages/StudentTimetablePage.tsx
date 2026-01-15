import { DashboardLayout } from "@/components/DashboardLayout";
import TimetableView from "@/pages/TimetableView";
import { useCurrentUser } from "@/hooks/useApi";

const StudentTimetablePage = () => {
  const { data: currentUser } = useCurrentUser();
  // @ts-ignore - We'll handle type definitions later
  const studentId = currentUser?.data?.student?.id || currentUser?.data?.user?.id;

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">My Timetable</h1>
          <p className="text-muted-foreground">
            View your weekly class schedule
          </p>
        </div>
        
        <TimetableView viewMode="student" studentId={studentId} />
      </div>
    </DashboardLayout>
  );
};

export default StudentTimetablePage;
