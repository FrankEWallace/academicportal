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
                  <AdminDashboard />
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
              <Route path="/admin/departments" element={
                <ProtectedRoute requiredRole="admin">
                  <ComingSoonPage 
                    title="Departments Management" 
                    description="Manage departments and organizational structure"
                    icon={Building2}
                  />
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
              
              {/* Protected Teacher Routes */}
              <Route path="/teacher" element={
                <ProtectedRoute requiredRole="teacher">
                  <TeacherDashboard />
                </ProtectedRoute>
              } />
              
              {/* Protected Mixed Routes - Accessible by multiple roles */}
              <Route path="/courses/:id" element={
                <ProtectedRoute allowedRoles={['admin', 'student', 'teacher']}>
                  <CourseDetail />
                </ProtectedRoute>
              } />
              <Route path="/students/:id" element={
                <ProtectedRoute allowedRoles={['admin', 'teacher']}>
                  <StudentDetail />
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
