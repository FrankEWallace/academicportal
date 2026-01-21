import { useState, useEffect } from 'react';
import { Settings, Save, RotateCcw, Check, AlertTriangle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useToast } from '@/hooks/use-toast';
import apiClient from '@/lib/api/apiClient';
import { DashboardLayout } from '@/components/DashboardLayout';

interface SystemSetting {
  key: string;
  value: any;
  category: string;
  type: string;
  description: string;
  is_public: boolean;
}

export default function SystemSettings() {
  const [settings, setSettings] = useState<Record<string, SystemSetting>>({});
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const { toast } = useToast();

  const fetchSettings = async () => {
    try {
      setLoading(true);
      const response = await apiClient.get('/admin/settings');
      setSettings(response.data.data.settings);
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to fetch settings',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchSettings();
  }, []);

  const handleSave = async (category: string) => {
    try {
      setSaving(true);
      
      const categorySettings = Object.entries(settings)
        .filter(([_, setting]) => setting.category === category)
        .reduce((acc, [key, setting]) => ({
          ...acc,
          [key]: {
            value: setting.value,
            type: setting.type,
            category: setting.category,
            description: setting.description,
            is_public: setting.is_public,
          },
        }), {});

      await apiClient.post('/admin/settings/bulk-update', {
        settings: categorySettings,
      });

      toast({
        title: 'Success',
        description: `${category.charAt(0).toUpperCase() + category.slice(1)} settings saved successfully`,
      });
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to save settings',
        variant: 'destructive',
      });
    } finally {
      setSaving(false);
    }
  };

  const handleReset = () => {
    fetchSettings();
    toast({
      title: 'Settings Reset',
      description: 'All changes have been discarded',
    });
  };

  const updateSetting = (key: string, value: any) => {
    setSettings(prev => ({
      ...prev,
      [key]: {
        ...prev[key],
        value,
      },
    }));
  };

  const renderSetting = (key: string) => {
    const setting = settings[key];
    if (!setting) return null;

    switch (setting.type) {
      case 'boolean':
        return (
          <div className="flex items-center justify-between space-x-2">
            <Label htmlFor={key} className="flex-1">
              <div>{setting.description || key}</div>
              <div className="text-xs text-muted-foreground mt-1">{key}</div>
            </Label>
            <Switch
              id={key}
              checked={Boolean(setting.value)}
              onCheckedChange={(checked) => updateSetting(key, checked)}
            />
          </div>
        );

      case 'number':
        return (
          <div className="space-y-2">
            <Label htmlFor={key}>
              <div>{setting.description || key}</div>
              <div className="text-xs text-muted-foreground">{key}</div>
            </Label>
            <Input
              id={key}
              type="number"
              value={setting.value || ''}
              onChange={(e) => updateSetting(key, parseFloat(e.target.value) || 0)}
            />
          </div>
        );

      default:
        return (
          <div className="space-y-2">
            <Label htmlFor={key}>
              <div>{setting.description || key}</div>
              <div className="text-xs text-muted-foreground">{key}</div>
            </Label>
            <Input
              id={key}
              type="text"
              value={setting.value || ''}
              onChange={(e) => updateSetting(key, e.target.value)}
            />
          </div>
        );
    }
  };

  const getCategorySettings = (category: string) => {
    return Object.keys(settings).filter(key => settings[key].category === category);
  };

  if (loading) {
    return (
      <DashboardLayout>
        <div className="p-6">
          <div className="text-center py-12">Loading settings...</div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="p-6 space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold">System Settings</h1>
            <p className="text-muted-foreground mt-1">
              Configure system-wide settings and preferences
            </p>
          </div>
          <Settings className="h-8 w-8 text-muted-foreground" />
        </div>

        {/* Settings Tabs */}
        <Tabs defaultValue="general" className="space-y-4">
          <TabsList>
            <TabsTrigger value="general">General</TabsTrigger>
            <TabsTrigger value="academic">Academic</TabsTrigger>
            <TabsTrigger value="email">Email</TabsTrigger>
            <TabsTrigger value="sms">SMS</TabsTrigger>
            <TabsTrigger value="features">Features</TabsTrigger>
            <TabsTrigger value="system">System</TabsTrigger>
          </TabsList>

          {/* General Settings */}
          <TabsContent value="general" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>General Settings</CardTitle>
                <CardDescription>
                  Basic application configuration
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                {getCategorySettings('general').map(key => (
                  <div key={key}>{renderSetting(key)}</div>
                ))}
                
                <div className="flex gap-2 pt-4">
                  <Button onClick={() => handleSave('general')} disabled={saving}>
                    <Save className="h-4 w-4 mr-2" />
                    {saving ? 'Saving...' : 'Save Changes'}
                  </Button>
                  <Button variant="outline" onClick={handleReset}>
                    <RotateCcw className="h-4 w-4 mr-2" />
                    Reset
                  </Button>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Academic Settings */}
          <TabsContent value="academic" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Academic Settings</CardTitle>
                <CardDescription>
                  Configure academic policies and limits
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                {getCategorySettings('academic').map(key => (
                  <div key={key}>{renderSetting(key)}</div>
                ))}
                
                <div className="flex gap-2 pt-4">
                  <Button onClick={() => handleSave('academic')} disabled={saving}>
                    <Save className="h-4 w-4 mr-2" />
                    {saving ? 'Saving...' : 'Save Changes'}
                  </Button>
                  <Button variant="outline" onClick={handleReset}>
                    <RotateCcw className="h-4 w-4 mr-2" />
                    Reset
                  </Button>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Email Settings */}
          <TabsContent value="email" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Email Settings</CardTitle>
                <CardDescription>
                  Configure email notifications and SMTP settings
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                {getCategorySettings('email').map(key => (
                  <div key={key}>{renderSetting(key)}</div>
                ))}
                
                <div className="flex gap-2 pt-4">
                  <Button onClick={() => handleSave('email')} disabled={saving}>
                    <Save className="h-4 w-4 mr-2" />
                    {saving ? 'Saving...' : 'Save Changes'}
                  </Button>
                  <Button variant="outline" onClick={handleReset}>
                    <RotateCcw className="h-4 w-4 mr-2" />
                    Reset
                  </Button>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* SMS Settings */}
          <TabsContent value="sms" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>SMS Settings</CardTitle>
                <CardDescription>
                  Configure SMS notifications and gateway
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                {getCategorySettings('sms').map(key => (
                  <div key={key}>{renderSetting(key)}</div>
                ))}
                
                <div className="flex gap-2 pt-4">
                  <Button onClick={() => handleSave('sms')} disabled={saving}>
                    <Save className="h-4 w-4 mr-2" />
                    {saving ? 'Saving...' : 'Save Changes'}
                  </Button>
                  <Button variant="outline" onClick={handleReset}>
                    <RotateCcw className="h-4 w-4 mr-2" />
                    Reset
                  </Button>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Feature Toggles */}
          <TabsContent value="features" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Feature Toggles</CardTitle>
                <CardDescription>
                  Enable or disable specific features
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                {getCategorySettings('features').map(key => (
                  <div key={key}>{renderSetting(key)}</div>
                ))}
                
                <div className="flex gap-2 pt-4">
                  <Button onClick={() => handleSave('features')} disabled={saving}>
                    <Save className="h-4 w-4 mr-2" />
                    {saving ? 'Saving...' : 'Save Changes'}
                  </Button>
                  <Button variant="outline" onClick={handleReset}>
                    <RotateCcw className="h-4 w-4 mr-2" />
                    Reset
                  </Button>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* System Settings */}
          <TabsContent value="system" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>System Settings</CardTitle>
                <CardDescription>
                  Maintenance mode and system-level configuration
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                {getCategorySettings('system').map(key => (
                  <div key={key}>{renderSetting(key)}</div>
                ))}
                
                {settings.maintenance_mode?.value && (
                  <div className="p-4 bg-yellow-50 border border-yellow-200 rounded-lg flex items-start gap-3">
                    <AlertTriangle className="h-5 w-5 text-yellow-600 mt-0.5" />
                    <div>
                      <h4 className="font-semibold text-yellow-900">Maintenance Mode Active</h4>
                      <p className="text-sm text-yellow-700 mt-1">
                        The system is currently in maintenance mode. Users will see a maintenance message.
                      </p>
                    </div>
                  </div>
                )}
                
                <div className="flex gap-2 pt-4">
                  <Button onClick={() => handleSave('system')} disabled={saving}>
                    <Save className="h-4 w-4 mr-2" />
                    {saving ? 'Saving...' : 'Save Changes'}
                  </Button>
                  <Button variant="outline" onClick={handleReset}>
                    <RotateCcw className="h-4 w-4 mr-2" />
                    Reset
                  </Button>
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </DashboardLayout>
  );
}
