import { useState, useEffect } from 'react';
import { DashboardLayout } from '@/components/DashboardLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  Shield,
  AlertTriangle,
  Lock,
  Eye,
  UserX,
  Activity,
  Clock,
  Globe,
} from 'lucide-react';
import { useToast } from '@/hooks/use-toast';

interface SecurityEvent {
  id: number;
  event_type: string;
  user_email: string;
  ip_address: string;
  user_agent: string;
  severity: 'low' | 'medium' | 'high' | 'critical';
  description: string;
  created_at: string;
}

interface SecurityStats {
  failed_logins_today: number;
  locked_accounts: number;
  suspicious_activities: number;
  active_sessions: number;
  audit_logs_today: number;
}

export default function SecurityDashboard() {
  const [events, setEvents] = useState<SecurityEvent[]>([]);
  const [stats, setStats] = useState<SecurityStats | null>(null);
  const [loading, setLoading] = useState(true);
  const { toast } = useToast();

  useEffect(() => {
    fetchSecurityData();
  }, []);

  const fetchSecurityData = async () => {
    setLoading(true);
    try {
      // Mock data - replace with actual API calls
      const mockEvents: SecurityEvent[] = [
        {
          id: 1,
          event_type: 'FAILED_LOGIN',
          user_email: 'student@example.com',
          ip_address: '192.168.1.100',
          user_agent: 'Mozilla/5.0...',
          severity: 'medium',
          description: 'Failed login attempt - invalid password',
          created_at: '2026-01-19T10:30:00Z',
        },
        {
          id: 2,
          event_type: 'ACCOUNT_LOCKED',
          user_email: 'test@example.com',
          ip_address: '192.168.1.50',
          user_agent: 'Mozilla/5.0...',
          severity: 'high',
          description: 'Account locked after 5 failed login attempts',
          created_at: '2026-01-19T09:15:00Z',
        },
        {
          id: 3,
          event_type: 'SUSPICIOUS_ACTIVITY',
          user_email: 'admin@example.com',
          ip_address: '203.45.67.89',
          user_agent: 'curl/7.68.0',
          severity: 'critical',
          description: 'Login from new location and unusual user agent',
          created_at: '2026-01-19T08:00:00Z',
        },
      ];

      const mockStats: SecurityStats = {
        failed_logins_today: 23,
        locked_accounts: 3,
        suspicious_activities: 5,
        active_sessions: 145,
        audit_logs_today: 1247,
      };

      setEvents(mockEvents);
      setStats(mockStats);
    } catch (error: any) {
      toast({
        title: 'Error',
        description: 'Failed to load security data',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const getSeverityBadge = (severity: string) => {
    const variants = {
      low: 'default',
      medium: 'secondary',
      high: 'destructive',
      critical: 'destructive',
    };

    const colors = {
      low: 'bg-blue-100 text-blue-800',
      medium: 'bg-yellow-100 text-yellow-800',
      high: 'bg-orange-100 text-orange-800',
      critical: 'bg-red-100 text-red-800',
    };

    return (
      <Badge variant={variants[severity as keyof typeof variants] as any} className={colors[severity as keyof typeof colors]}>
        {severity.toUpperCase()}
      </Badge>
    );
  };

  const getEventIcon = (eventType: string) => {
    switch (eventType) {
      case 'FAILED_LOGIN':
        return <UserX className="h-4 w-4 text-orange-500" />;
      case 'ACCOUNT_LOCKED':
        return <Lock className="h-4 w-4 text-red-500" />;
      case 'SUSPICIOUS_ACTIVITY':
        return <AlertTriangle className="h-4 w-4 text-red-600" />;
      default:
        return <Activity className="h-4 w-4 text-gray-500" />;
    }
  };

  if (loading) {
    return (
      <DashboardLayout title="Security Dashboard">
        <div className="flex items-center justify-center p-8">
          <div className="text-center">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
            <p className="mt-4 text-muted-foreground">Loading security data...</p>
          </div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout title="Security Dashboard">
      <div className="space-y-6">
        {/* Statistics Cards */}
        {stats && (
          <div className="grid gap-4 md:grid-cols-5">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Failed Logins</CardTitle>
                <UserX className="h-4 w-4 text-orange-500" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{stats.failed_logins_today}</div>
                <p className="text-xs text-muted-foreground">Today</p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Locked Accounts</CardTitle>
                <Lock className="h-4 w-4 text-red-500" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{stats.locked_accounts}</div>
                <p className="text-xs text-muted-foreground">Current</p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Suspicious Activity</CardTitle>
                <AlertTriangle className="h-4 w-4 text-red-600" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{stats.suspicious_activities}</div>
                <p className="text-xs text-muted-foreground">Today</p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Active Sessions</CardTitle>
                <Activity className="h-4 w-4 text-green-500" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{stats.active_sessions}</div>
                <p className="text-xs text-muted-foreground">Current</p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Audit Logs</CardTitle>
                <Eye className="h-4 w-4 text-blue-500" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{stats.audit_logs_today}</div>
                <p className="text-xs text-muted-foreground">Today</p>
              </CardContent>
            </Card>
          </div>
        )}

        {/* Security Events */}
        <Tabs defaultValue="all">
          <TabsList>
            <TabsTrigger value="all">All Events</TabsTrigger>
            <TabsTrigger value="critical">Critical</TabsTrigger>
            <TabsTrigger value="failed-logins">Failed Logins</TabsTrigger>
            <TabsTrigger value="locked">Locked Accounts</TabsTrigger>
          </TabsList>

          <TabsContent value="all" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Shield className="h-5 w-5" />
                  Recent Security Events
                </CardTitle>
                <CardDescription>
                  Monitor security-related activities across the system
                </CardDescription>
              </CardHeader>
              <CardContent>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Event</TableHead>
                      <TableHead>User</TableHead>
                      <TableHead>IP Address</TableHead>
                      <TableHead>Description</TableHead>
                      <TableHead>Severity</TableHead>
                      <TableHead>Time</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {events.map((event) => (
                      <TableRow key={event.id}>
                        <TableCell>
                          <div className="flex items-center gap-2">
                            {getEventIcon(event.event_type)}
                            <span className="font-medium">{event.event_type.replace('_', ' ')}</span>
                          </div>
                        </TableCell>
                        <TableCell>{event.user_email}</TableCell>
                        <TableCell className="font-mono text-xs">
                          <div className="flex items-center gap-1">
                            <Globe className="h-3 w-3" />
                            {event.ip_address}
                          </div>
                        </TableCell>
                        <TableCell className="max-w-md truncate">{event.description}</TableCell>
                        <TableCell>{getSeverityBadge(event.severity)}</TableCell>
                        <TableCell>
                          <div className="flex items-center gap-1 text-xs text-muted-foreground">
                            <Clock className="h-3 w-3" />
                            {new Date(event.created_at).toLocaleString()}
                          </div>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="critical">
            <Card>
              <CardHeader>
                <CardTitle>Critical Security Events</CardTitle>
                <CardDescription>High-priority security incidents requiring immediate attention</CardDescription>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">Showing critical events only...</p>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="failed-logins">
            <Card>
              <CardHeader>
                <CardTitle>Failed Login Attempts</CardTitle>
                <CardDescription>Track unsuccessful login attempts</CardDescription>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">Showing failed login attempts...</p>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="locked">
            <Card>
              <CardHeader>
                <CardTitle>Locked Accounts</CardTitle>
                <CardDescription>Accounts locked due to security policies</CardDescription>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">Showing locked accounts...</p>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </DashboardLayout>
  );
}
