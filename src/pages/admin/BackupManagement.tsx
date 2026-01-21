import { useState, useEffect } from 'react';
import { Download, Trash2, RotateCcw, Database, HardDrive, Calendar, AlertTriangle, CheckCircle2, RefreshCw, Shield } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { useToast } from '@/hooks/use-toast';
import apiClient from '@/lib/api/apiClient';
import { formatDistanceToNow } from 'date-fns';
import DashboardLayout from '@/components/DashboardLayout';

interface Backup {
  name: string;
  path: string;
  size: number;
  size_human: string;
  created_at: string;
  age_days: number;
}

interface BackupStats {
  total_backups: number;
  total_size: number;
  total_size_human: string;
  oldest_backup: string | null;
  newest_backup: string | null;
  retention_days: number;
}

export default function BackupManagement() {
  const [backups, setBackups] = useState<Backup[]>([]);
  const [stats, setStats] = useState<BackupStats | null>(null);
  const [loading, setLoading] = useState(false);
  const [creating, setCreating] = useState(false);
  const [deleteDialog, setDeleteDialog] = useState<{ open: boolean; backup: Backup | null }>({ open: false, backup: null });
  const [restoreDialog, setRestoreDialog] = useState<{ open: boolean; backup: Backup | null }>({ open: false, backup: null });
  const { toast } = useToast();

  const fetchBackups = async () => {
    try {
      setLoading(true);
      const response = await apiClient.get('/admin/backups');
      setBackups(response.data.data.backups);
      setStats(response.data.data.stats);
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to fetch backups',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchBackups();
  }, []);

  const createBackup = async () => {
    try {
      setCreating(true);
      await apiClient.post('/admin/backups');
      toast({
        title: 'Success',
        description: 'Backup created successfully',
      });
      fetchBackups();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to create backup',
        variant: 'destructive',
      });
    } finally {
      setCreating(false);
    }
  };

  const downloadBackup = async (backup: Backup) => {
    try {
      const response = await apiClient.get(`/admin/backups/${backup.name}/download`, {
        responseType: 'blob',
      });

      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', backup.name);
      document.body.appendChild(link);
      link.click();
      link.remove();

      toast({
        title: 'Success',
        description: 'Backup downloaded successfully',
      });
    } catch (error: any) {
      toast({
        title: 'Error',
        description: 'Failed to download backup',
        variant: 'destructive',
      });
    }
  };

  const deleteBackup = async () => {
    if (!deleteDialog.backup) return;

    try {
      await apiClient.delete(`/admin/backups/${deleteDialog.backup.name}`);
      toast({
        title: 'Success',
        description: 'Backup deleted successfully',
      });
      setDeleteDialog({ open: false, backup: null });
      fetchBackups();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to delete backup',
        variant: 'destructive',
      });
    }
  };

  const restoreBackup = async () => {
    if (!restoreDialog.backup) return;

    try {
      await apiClient.post(`/admin/backups/${restoreDialog.backup.name}/restore`);
      toast({
        title: 'Success',
        description: 'Database restored successfully. Please refresh the page.',
      });
      setRestoreDialog({ open: false, backup: null });
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to restore backup',
        variant: 'destructive',
      });
    }
  };

  return (
    <DashboardLayout role="admin">
      <div className="p-6 space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold">Backup Management</h1>
            <p className="text-muted-foreground mt-1">
              Manage database backups and restore points
            </p>
          </div>
          <div className="flex gap-2">
            <Button variant="outline" onClick={fetchBackups} disabled={loading}>
              <RefreshCw className={`h-4 w-4 mr-2 ${loading ? 'animate-spin' : ''}`} />
              Refresh
            </Button>
            <Button onClick={createBackup} disabled={creating}>
              <Database className="h-4 w-4 mr-2" />
              {creating ? 'Creating...' : 'Create Backup'}
            </Button>
          </div>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium">Total Backups</CardTitle>
              <Database className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats?.total_backups || 0}</div>
              <p className="text-xs text-muted-foreground mt-1">
                {stats?.total_size_human || '0 B'} total
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium">Latest Backup</CardTitle>
              <Calendar className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {stats?.newest_backup ? formatDistanceToNow(new Date(stats.newest_backup), { addSuffix: true }) : 'N/A'}
              </div>
              <p className="text-xs text-muted-foreground mt-1">
                {stats?.newest_backup || 'No backups yet'}
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium">Storage Used</CardTitle>
              <HardDrive className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats?.total_size_human || '0 B'}</div>
              <p className="text-xs text-muted-foreground mt-1">
                Across all backups
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium">Retention Policy</CardTitle>
              <Shield className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats?.retention_days || 30} days</div>
              <p className="text-xs text-muted-foreground mt-1">
                Auto-cleanup enabled
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Backups Table */}
        <Card>
          <CardHeader>
            <CardTitle>Backup History</CardTitle>
            <CardDescription>
              All database backups with restore capability
            </CardDescription>
          </CardHeader>
          <CardContent>
            {loading ? (
              <div className="text-center py-8 text-muted-foreground">
                Loading backups...
              </div>
            ) : backups.length === 0 ? (
              <div className="text-center py-8">
                <Database className="h-12 w-12 mx-auto mb-4 text-muted-foreground opacity-20" />
                <p className="text-muted-foreground">No backups found</p>
                <Button onClick={createBackup} className="mt-4" disabled={creating}>
                  Create First Backup
                </Button>
              </div>
            ) : (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Backup Name</TableHead>
                    <TableHead>Created</TableHead>
                    <TableHead>Age</TableHead>
                    <TableHead>Size</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {backups.map((backup) => (
                    <TableRow key={backup.name}>
                      <TableCell className="font-medium">{backup.name}</TableCell>
                      <TableCell>{new Date(backup.created_at).toLocaleString()}</TableCell>
                      <TableCell>
                        {backup.age_days === 0 ? 'Today' : `${backup.age_days} days ago`}
                      </TableCell>
                      <TableCell>{backup.size_human}</TableCell>
                      <TableCell>
                        {backup.age_days > (stats?.retention_days || 30) ? (
                          <Badge variant="destructive">
                            <AlertTriangle className="h-3 w-3 mr-1" />
                            Expiring
                          </Badge>
                        ) : (
                          <Badge variant="default" className="bg-green-600">
                            <CheckCircle2 className="h-3 w-3 mr-1" />
                            Active
                          </Badge>
                        )}
                      </TableCell>
                      <TableCell className="text-right">
                        <div className="flex justify-end gap-2">
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => downloadBackup(backup)}
                          >
                            <Download className="h-4 w-4" />
                          </Button>
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => setRestoreDialog({ open: true, backup })}
                          >
                            <RotateCcw className="h-4 w-4" />
                          </Button>
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => setDeleteDialog({ open: true, backup })}
                          >
                            <Trash2 className="h-4 w-4 text-destructive" />
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            )}
          </CardContent>
        </Card>

        {/* Delete Dialog */}
        <AlertDialog open={deleteDialog.open} onOpenChange={(open) => setDeleteDialog({ open, backup: null })}>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Delete Backup?</AlertDialogTitle>
              <AlertDialogDescription>
                Are you sure you want to delete <strong>{deleteDialog.backup?.name}</strong>?
                This action cannot be undone.
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>Cancel</AlertDialogCancel>
              <AlertDialogAction onClick={deleteBackup} className="bg-destructive text-destructive-foreground">
                Delete
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>

        {/* Restore Dialog */}
        <AlertDialog open={restoreDialog.open} onOpenChange={(open) => setRestoreDialog({ open, backup: null })}>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle className="flex items-center gap-2">
                <AlertTriangle className="h-5 w-5 text-destructive" />
                Restore Database?
              </AlertDialogTitle>
              <AlertDialogDescription className="space-y-2">
                <p className="font-semibold text-destructive">
                  ⚠️ WARNING: This will replace ALL current data!
                </p>
                <p>
                  You are about to restore from <strong>{restoreDialog.backup?.name}</strong>.
                  All current database data will be permanently lost.
                </p>
                <p>
                  Created: {restoreDialog.backup?.created_at ? new Date(restoreDialog.backup.created_at).toLocaleString() : 'N/A'}
                </p>
                <p className="font-semibold">
                  Make sure you have a recent backup before proceeding!
                </p>
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>Cancel</AlertDialogCancel>
              <AlertDialogAction onClick={restoreBackup} className="bg-destructive text-destructive-foreground">
                Restore Database
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      </div>
    </DashboardLayout>
  );
}
