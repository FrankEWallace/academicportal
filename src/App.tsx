import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { createQueryClientWithErrorHandling } from "@/lib/queryClient";
import ErrorBoundary from "@/components/ErrorBoundary";
import { AuthProvider } from "@/contexts/AuthContext";
import ProtectedRoute from "@/components/ProtectedRoute";
import PublicRoute from "@/components/PublicRoute";
import Login from "./pages/Login";
import PasswordResetRequest from "./pages/PasswordResetRequest";
import PasswordReset from "./pages/PasswordReset";
import AdminDashboard from "./pages/AdminDashboard";
import StudentDashboard from "./pages/StudentDashboard";
import TeacherDashboard from "./pages/TeacherDashboard";
import CourseDetail from "./pages/CourseDetail";
import StudentDetail from "./pages/StudentDetail";
import StudentProfile from "./pages/StudentProfile";
import AttendanceList from "./pages/AttendanceList";
import CoursesManagement from "./pages/CoursesManagement";
import StudentsManagement from "./pages/StudentsManagement";
import TeachersManagement from "./pages/TeachersManagement";
import StudentTimetablePage from "./pages/StudentTimetablePage";
import TeacherTimetablePage from "./pages/TeacherTimetablePage";
import AdminTimetablePage from "./pages/AdminTimetablePage";
import StudentCalendarPage from "./pages/StudentCalendarPage";
import TeacherCalendarPage from "./pages/TeacherCalendarPage";
import AdminCalendarPage from "./pages/AdminCalendarPage";
import StudentProgressPage from "./pages/StudentProgressPage";
import StudentWaitlistPage from "./pages/StudentWaitlistPage";
import ComingSoonPage from "./pages/ComingSoonPage";
import NotFound from "./pages/NotFound";
import { Building2, ClipboardCheck, FileText, CreditCard, Megaphone } from "lucide-react";

// Lecturer Components
import LecturerDashboard from "./pages/lecturer/LecturerDashboard";
import LecturerCAManagement from "./pages/lecturer/LecturerCAManagement";
import LecturerResultsManagement from "./pages/lecturer/LecturerResultsManagement";

// Admin Components
import AdminOverview from "./pages/admin/AdminOverview";
import AdminRegistrationControl from "./pages/admin/AdminRegistrationControl";
import AdminAccommodationManagement from "./pages/admin/AdminAccommodationManagement";
import AdminInsuranceVerification from "./pages/admin/AdminInsuranceVerification";
import AdminEnrollmentApproval from "./pages/admin/AdminEnrollmentApproval";
import AdminResultsModeration from "./pages/admin/AdminResultsModeration";
import AdminFeedbackManagement from "./pages/admin/AdminFeedbackManagement";
import DepartmentManagement from "./pages/admin/DepartmentManagement";

const queryClient = createQueryClientWithErrorHandling();

const App = () => (
  <ErrorBoundary>
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <TooltipProvider>
          <Toaster />
          <Sonner />
          <BrowserRouter>
            <Routes>
              {/* Public Routes - Login and Password Reset */}
              <Route path="/" element={
                <PublicRoute>
                  <Login />
                </PublicRoute>
              } />
              <Route path="/login" element={
                <PublicRoute>
                  <Login />
                </PublicRoute>
              } />
              <Route path="/password-reset-request" element={
                <PublicRoute>
                  <PasswordResetRequest />
                </PublicRoute>
              } />
              <Route path="/password-reset" element={
                <PublicRoute>
                  <PasswordReset />
                </PublicRoute>
              } />
              
              {/* Protected Admin Routes */}
              <Route path="/admin" element={
                <ProtectedRoute requiredRole="admin">
                  <AdminOverview />
                </ProtectedRoute>
              } />
              <Route path="/admin/registrations" element={
                <ProtectedRoute requiredRole="admin">
                  <AdminRegistrationControl />
                </ProtectedRoute>
              } />
              <Route path="/admin/accommodations" element={
                <ProtectedRoute requiredRole="admin">
                  <AdminAccommodationManagement />
                </ProtectedRoute>
              } />
              <Route path="/admin/insurance" element={
                <ProtectedRoute requiredRole="admin">
                  <AdminInsuranceVerification />
                </ProtectedRoute>
              } />
              <Route path="/admin/enrollments" element={
                <ProtectedRoute requiredRole="admin">
                  <AdminEnrollmentApproval />
                </ProtectedRoute>
              } />
              <Route path="/admin/results" element={
                <ProtectedRoute requiredRole="admin">
                  <AdminResultsModeration />
                </ProtectedRoute>
              } />
              <Route path="/admin/feedback" element={
                <ProtectedRoute requiredRole="admin">
                  <AdminFeedbackManagement />
                </ProtectedRoute>
              } />
              <Route path="/admin/courses" element={
                <ProtectedRoute requiredRole="admin">
                  <CoursesManagement />
                </ProtectedRoute>
              } />
              <Route path="/admin/students" element={
                <ProtectedRoute requiredRole="admin">
                  <StudentsManagement />
                </ProtectedRoute>
              } />
              <Route path="/admin/teachers" element={
                <ProtectedRoute requiredRole="admin">
                  <TeachersManagement />
                </ProtectedRoute>
              } />
              <Route path="/admin/timetable" element={
                <ProtectedRoute requiredRole="admin">
                  <AdminTimetablePage />
                </ProtectedRoute>
              } />
              <Route path="/admin/calendar" element={
                <ProtectedRoute requiredRole="admin">
                  <AdminCalendarPage />
                </ProtectedRoute>
              } />
              <Route path="/admin/departments" element={
                <ProtectedRoute requiredRole="admin">
                  <DepartmentManagement />
                </ProtectedRoute>
              } />
              <Route path="/admin/attendance" element={
                <ProtectedRoute requiredRole="admin">
                  <ComingSoonPage 
                    title="Attendance Management" 
                    description="Track and manage student attendance"
                    icon={ClipboardCheck}
                  />
                </ProtectedRoute>
              } />
              <Route path="/admin/exams" element={
                <ProtectedRoute requiredRole="admin">
                  <ComingSoonPage 
                    title="Exams & Grades" 
                    description="Manage exams, assessments, and grades"
                    icon={FileText}
                  />
                </ProtectedRoute>
              } />
              <Route path="/admin/fees" element={
                <ProtectedRoute requiredRole="admin">
                  <ComingSoonPage 
                    title="Fees Management" 
                    description="Handle fee collection and payment tracking"
                    icon={CreditCard}
                  />
                </ProtectedRoute>
              } />
              <Route path="/admin/announcements" element={
                <ProtectedRoute requiredRole="admin">
                  <ComingSoonPage 
                    title="Announcements" 
                    description="Create and manage school-wide announcements"
                    icon={Megaphone}
                  />
                </ProtectedRoute>
              } />
              
              {/* Protected Student Routes */}
              <Route path="/student" element={
                <ProtectedRoute requiredRole="student">
                  <StudentDashboard />
                </ProtectedRoute>
              } />
              <Route path="/student/timetable" element={
                <ProtectedRoute requiredRole="student">
                  <StudentTimetablePage />
                </ProtectedRoute>
              } />
              <Route path="/student/calendar" element={
                <ProtectedRoute requiredRole="student">
                  <StudentCalendarPage />
                </ProtectedRoute>
              } />
              <Route path="/student/progress" element={
                <ProtectedRoute requiredRole="student">
                  <StudentProgressPage />
                </ProtectedRoute>
              } />
              <Route path="/student/waitlist" element={
                <ProtectedRoute requiredRole="student">
                  <StudentWaitlistPage />
                </ProtectedRoute>
              } />
              <Route path="/student/profile" element={
                <ProtectedRoute requiredRole="student">
                  <StudentProfile />
                </ProtectedRoute>
              } />
              
              {/* Protected Teacher Routes */}
              <Route path="/teacher" element={
                <ProtectedRoute requiredRole="teacher">
                  <TeacherDashboard />
                </ProtectedRoute>
              } />
              <Route path="/teacher/timetable" element={
                <ProtectedRoute requiredRole="teacher">
                  <TeacherTimetablePage />
                </ProtectedRoute>
              } />
              <Route path="/teacher/calendar" element={
                <ProtectedRoute requiredRole="teacher">
                  <TeacherCalendarPage />
                </ProtectedRoute>
              } />
              
              {/* Lecturer Routes (same as teacher for now) */}
              <Route path="/lecturer" element={
                <ProtectedRoute requiredRole="teacher">
                  <LecturerDashboard />
                </ProtectedRoute>
              } />
              <Route path="/lecturer/ca" element={
                <ProtectedRoute requiredRole="teacher">
                  <LecturerCAManagement />
                </ProtectedRoute>
              } />
              <Route path="/lecturer/results" element={
                <ProtectedRoute requiredRole="teacher">
                  <LecturerResultsManagement />
                </ProtectedRoute>
              } />
              
              {/* Attendance Routes */}
              <Route path="/courses/:courseId/attendance" element={
                <ProtectedRoute allowedRoles={['admin', 'teacher']}>
                  <AttendanceList />
                </ProtectedRoute>
              } />
              
              {/* Protected Mixed Routes - Accessible by multiple roles */}
              <Route path="/courses/:id" element={
                <ProtectedRoute allowedRoles={['admin', 'student', 'teacher']}>
                  <CourseDetail />
                </ProtectedRoute>
              } />
              <Route path="/students/:id" element={
                <ProtectedRoute allowedRoles={['admin', 'teacher', 'student']}>
                  <StudentProfile />
                </ProtectedRoute>
              } />
              
              {/* Catch-all route */}
              <Route path="*" element={<NotFound />} />
            </Routes>
          </BrowserRouter>
        </TooltipProvider>
      </AuthProvider>
    </QueryClientProvider>
  </ErrorBoundary>
);

export default App;
