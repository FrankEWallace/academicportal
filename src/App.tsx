import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { createQueryClientWithErrorHandling } from "@/lib/queryClient";
import ErrorBoundary from "@/components/ErrorBoundary";
import Login from "./pages/Login";
import AdminDashboard from "./pages/AdminDashboard";
import StudentDashboard from "./pages/StudentDashboard";
import TeacherDashboard from "./pages/TeacherDashboard";
import CourseDetail from "./pages/CourseDetail";
import StudentDetail from "./pages/StudentDetail";
import CoursesManagement from "./pages/CoursesManagement";
import StudentsManagement from "./pages/StudentsManagement";
import TeachersManagement from "./pages/TeachersManagement";
import ComingSoonPage from "./pages/ComingSoonPage";
import NotFound from "./pages/NotFound";
import { Building2, ClipboardCheck, FileText, CreditCard, Megaphone } from "lucide-react";

const queryClient = createQueryClientWithErrorHandling();

const App = () => (
  <ErrorBoundary>
    <QueryClientProvider client={queryClient}>
      <TooltipProvider>
        <Toaster />
        <Sonner />
        <BrowserRouter>
          <Routes>
            <Route path="/" element={<Login />} />
            <Route path="/admin" element={<AdminDashboard />} />
            <Route path="/admin/courses" element={<CoursesManagement />} />
            <Route path="/admin/students" element={<StudentsManagement />} />
            <Route path="/admin/teachers" element={<TeachersManagement />} />
            <Route path="/admin/departments" element={
              <ComingSoonPage 
                title="Departments Management" 
                description="Manage departments and organizational structure"
                icon={Building2}
              />
            } />
            <Route path="/admin/attendance" element={
              <ComingSoonPage 
                title="Attendance Management" 
                description="Track and manage student attendance"
                icon={ClipboardCheck}
              />
            } />
            <Route path="/admin/exams" element={
              <ComingSoonPage 
                title="Exams & Grades" 
                description="Manage exams, assessments, and grades"
                icon={FileText}
              />
            } />
            <Route path="/admin/fees" element={
              <ComingSoonPage 
                title="Fees Management" 
                description="Handle fee collection and payment tracking"
                icon={CreditCard}
              />
            } />
            <Route path="/admin/announcements" element={
              <ComingSoonPage 
                title="Announcements" 
                description="Create and manage school-wide announcements"
                icon={Megaphone}
              />
            } />
            <Route path="/student" element={<StudentDashboard />} />
            <Route path="/teacher" element={<TeacherDashboard />} />
            <Route path="/courses/:id" element={<CourseDetail />} />
            <Route path="/students/:id" element={<StudentDetail />} />
            {/* ADD ALL CUSTOM ROUTES ABOVE THE CATCH-ALL "*" ROUTE */}
            <Route path="*" element={<NotFound />} />
          </Routes>
        </BrowserRouter>
      </TooltipProvider>
    </QueryClientProvider>
  </ErrorBoundary>
);

export default App;
