import { useState, useEffect } from 'react';
import { Bell, Mail, MessageSquare, Smartphone, Save } from 'lucide-react';
import { DashboardLayout } from '@/components/DashboardLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useToast } from '@/hooks/use-toast';
import apiClient from '@/lib/apiClient';

interface NotificationPreferences {
  email_enabled: boolean;
  sms_enabled: boolean;
  push_enabled: boolean;
  email_grades: boolean;
  email_payments: boolean;
  email_announcements: boolean;
  email_attendance: boolean;
  email_timetable: boolean;
  sms_grades: boolean;
  sms_payments: boolean;
  sms_urgent: boolean;
  app_all: boolean;
}

export default function NotificationSettings() {
  const [preferences, setPreferences] = useState<NotificationPreferences>({
    email_enabled: true,
    sms_enabled: false,
    push_enabled: true,
    email_grades: true,
    email_payments: true,
    email_announcements: true,
    email_attendance: true,
    email_timetable: true,
    sms_grades: false,
    sms_payments: false,
    sms_urgent: true,
    app_all: true,
  });
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const { toast } = useToast();

  useEffect(() => {
    fetchPreferences();
  }, []);

  const fetchPreferences = async () => {
    try {
      setLoading(true);
      const response = await apiClient.get('/notifications/preferences');
      setPreferences(response.data.data);
    } catch (error) {
      console.error('Error fetching preferences:', error);
      toast({
        title: 'Error',
        description: 'Failed to load notification preferences',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async () => {
    try {
      setSaving(true);
      await apiClient.put('/notifications/preferences', preferences);
      toast({
        title: 'Success',
        description: 'Notification preferences updated successfully',
      });
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to update notification preferences',
        variant: 'destructive',
      });
    } finally {
      setSaving(false);
    }
  };

  const updatePreference = (key: keyof NotificationPreferences, value: boolean) => {
    setPreferences(prev => ({ ...prev, [key]: value }));
  };

  if (loading) {
    return (
      <DashboardLayout title="Notification Settings">
        <div className="flex items-center justify-center h-64">
          <p>Loading preferences...</p>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout
      title="Notification Settings"
      description="Manage how you receive notifications"
    >
      <div className="max-w-4xl space-y-6">
        {/* Master Controls */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Bell className="h-5 w-5" />
              Notification Channels
            </CardTitle>
            <CardDescription>
              Enable or disable entire notification channels
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-3">
                <Mail className="h-4 w-4 text-muted-foreground" />
                <div>
                  <Label htmlFor="email_enabled">Email Notifications</Label>
                  <p className="text-sm text-muted-foreground">
                    Receive notifications via email
                  </p>
                </div>
              </div>
              <Switch
                id="email_enabled"
                checked={preferences.email_enabled}
                onCheckedChange={(checked) => updatePreference('email_enabled', checked)}
              />
            </div>

            <Separator />

            <div className="flex items-center justify-between">
              <div className="flex items-center gap-3">
                <MessageSquare className="h-4 w-4 text-muted-foreground" />
                <div>
                  <Label htmlFor="sms_enabled">SMS Notifications</Label>
                  <p className="text-sm text-muted-foreground">
                    Receive notifications via SMS
                  </p>
                </div>
              </div>
              <Switch
                id="sms_enabled"
                checked={preferences.sms_enabled}
                onCheckedChange={(checked) => updatePreference('sms_enabled', checked)}
              />
            </div>

            <Separator />

            <div className="flex items-center justify-between">
              <div className="flex items-center gap-3">
                <Smartphone className="h-4 w-4 text-muted-foreground" />
                <div>
                  <Label htmlFor="push_enabled">Push Notifications</Label>
                  <p className="text-sm text-muted-foreground">
                    Receive in-app push notifications
                  </p>
                </div>
              </div>
              <Switch
                id="push_enabled"
                checked={preferences.push_enabled}
                onCheckedChange={(checked) => updatePreference('push_enabled', checked)}
              />
            </div>
          </CardContent>
        </Card>

        {/* Email Preferences */}
        <Card>
          <CardHeader>
            <CardTitle>Email Notification Preferences</CardTitle>
            <CardDescription>
              Choose which events you want to be notified about via email
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <Label htmlFor="email_grades">Grade Notifications</Label>
                <p className="text-sm text-muted-foreground">
                  When new grades are published
                </p>
              </div>
              <Switch
                id="email_grades"
                checked={preferences.email_grades}
                onCheckedChange={(checked) => updatePreference('email_grades', checked)}
                disabled={!preferences.email_enabled}
              />
            </div>

            <Separator />

            <div className="flex items-center justify-between">
              <div>
                <Label htmlFor="email_payments">Payment Notifications</Label>
                <p className="text-sm text-muted-foreground">
                  Payment confirmations and reminders
                </p>
              </div>
              <Switch
                id="email_payments"
                checked={preferences.email_payments}
                onCheckedChange={(checked) => updatePreference('email_payments', checked)}
                disabled={!preferences.email_enabled}
              />
            </div>

            <Separator />

            <div className="flex items-center justify-between">
              <div>
                <Label htmlFor="email_announcements">Announcements</Label>
                <p className="text-sm text-muted-foreground">
                  Important announcements from administration
                </p>
              </div>
              <Switch
                id="email_announcements"
                checked={preferences.email_announcements}
                onCheckedChange={(checked) => updatePreference('email_announcements', checked)}
                disabled={!preferences.email_enabled}
              />
            </div>

            <Separator />

            <div className="flex items-center justify-between">
              <div>
                <Label htmlFor="email_attendance">Attendance Alerts</Label>
                <p className="text-sm text-muted-foreground">
                  Attendance warnings and reports
                </p>
              </div>
              <Switch
                id="email_attendance"
                checked={preferences.email_attendance}
                onCheckedChange={(checked) => updatePreference('email_attendance', checked)}
                disabled={!preferences.email_enabled}
              />
            </div>

            <Separator />

            <div className="flex items-center justify-between">
              <div>
                <Label htmlFor="email_timetable">Timetable Updates</Label>
                <p className="text-sm text-muted-foreground">
                  Changes to your timetable
                </p>
              </div>
              <Switch
                id="email_timetable"
                checked={preferences.email_timetable}
                onCheckedChange={(checked) => updatePreference('email_timetable', checked)}
                disabled={!preferences.email_enabled}
              />
            </div>
          </CardContent>
        </Card>

        {/* SMS Preferences */}
        <Card>
          <CardHeader>
            <CardTitle>SMS Notification Preferences</CardTitle>
            <CardDescription>
              Choose which events you want to be notified about via SMS
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <Label htmlFor="sms_grades">Grade Notifications</Label>
                <p className="text-sm text-muted-foreground">
                  When new grades are published
                </p>
              </div>
              <Switch
                id="sms_grades"
                checked={preferences.sms_grades}
                onCheckedChange={(checked) => updatePreference('sms_grades', checked)}
                disabled={!preferences.sms_enabled}
              />
            </div>

            <Separator />

            <div className="flex items-center justify-between">
              <div>
                <Label htmlFor="sms_payments">Payment Reminders</Label>
                <p className="text-sm text-muted-foreground">
                  Payment due reminders
                </p>
              </div>
              <Switch
                id="sms_payments"
                checked={preferences.sms_payments}
                onCheckedChange={(checked) => updatePreference('sms_payments', checked)}
                disabled={!preferences.sms_enabled}
              />
            </div>

            <Separator />

            <div className="flex items-center justify-between">
              <div>
                <Label htmlFor="sms_urgent">Urgent Alerts</Label>
                <p className="text-sm text-muted-foreground">
                  Critical and time-sensitive notifications
                </p>
              </div>
              <Switch
                id="sms_urgent"
                checked={preferences.sms_urgent}
                onCheckedChange={(checked) => updatePreference('sms_urgent', checked)}
                disabled={!preferences.sms_enabled}
              />
            </div>
          </CardContent>
        </Card>

        {/* In-App Preferences */}
        <Card>
          <CardHeader>
            <CardTitle>In-App Notifications</CardTitle>
            <CardDescription>
              Manage how you see notifications within the application
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="flex items-center justify-between">
              <div>
                <Label htmlFor="app_all">Show All In-App Notifications</Label>
                <p className="text-sm text-muted-foreground">
                  Display all notifications in the notification center
                </p>
              </div>
              <Switch
                id="app_all"
                checked={preferences.app_all}
                onCheckedChange={(checked) => updatePreference('app_all', checked)}
              />
            </div>
          </CardContent>
        </Card>

        {/* Save Button */}
        <div className="flex justify-end gap-3">
          <Button variant="outline" onClick={fetchPreferences} disabled={saving}>
            Reset
          </Button>
          <Button onClick={handleSave} disabled={saving}>
            <Save className="h-4 w-4 mr-2" />
            {saving ? 'Saving...' : 'Save Preferences'}
          </Button>
        </div>
      </div>
    </DashboardLayout>
  );
}
