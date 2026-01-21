import { useState, useEffect } from 'react';
import { Calendar, Plus, Trash2, Check } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { useToast } from '@/hooks/use-toast';
import apiClient from '@/lib/api/apiClient';
import { DashboardLayout } from '@/components/DashboardLayout';

interface AcademicYear {
  id: number;
  name: string;
  start_date: string;
  end_date: string;
  is_active: boolean;
  registration_start_date?: string;
  registration_end_date?: string;
  description?: string;
}

export default function AcademicYearManagement() {
  const [years, setYears] = useState<AcademicYear[]>([]);
  const [loading, setLoading] = useState(false);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    start_date: '',
    end_date: '',
    registration_start_date: '',
    registration_end_date: '',
    description: '',
  });
  const { toast } = useToast();

  const fetchYears = async () => {
    try {
      setLoading(true);
      const response = await apiClient.get('/admin/academic-years');
      setYears(response.data.data.academic_years);
    } catch (error: any) {
      toast({
        title: 'Error',
        description: 'Failed to fetch academic years',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchYears();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await apiClient.post('/admin/academic-years', formData);
      toast({ title: 'Success', description: 'Academic year created successfully' });
      setDialogOpen(false);
      setFormData({ name: '', start_date: '', end_date: '', registration_start_date: '', registration_end_date: '', description: '' });
      fetchYears();
    } catch (error: any) {
      toast({ title: 'Error', description: error.response?.data?.message || 'Failed to create academic year', variant: 'destructive' });
    }
  };

  const handleActivate = async (id: number) => {
    try {
      await apiClient.post(`/admin/academic-years/${id}/activate`);
      toast({ title: 'Success', description: 'Academic year activated' });
      fetchYears();
    } catch (error: any) {
      toast({ title: 'Error', description: 'Failed to activate academic year', variant: 'destructive' });
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Are you sure you want to delete this academic year?')) return;
    try {
      await apiClient.delete(`/admin/academic-years/${id}`);
      toast({ title: 'Success', description: 'Academic year deleted' });
      fetchYears();
    } catch (error: any) {
      toast({ title: 'Error', description: error.response?.data?.message || 'Failed to delete', variant: 'destructive' });
    }
  };

  return (
    <DashboardLayout>
      <div className="p-6 space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold">Academic Years</h1>
            <p className="text-muted-foreground mt-1">Manage academic year configuration</p>
          </div>
          <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
            <DialogTrigger asChild>
              <Button><Plus className="h-4 w-4 mr-2" />Create Academic Year</Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Create Academic Year</DialogTitle>
                <DialogDescription>Add a new academic year to the system</DialogDescription>
              </DialogHeader>
              <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                  <Label htmlFor="name">Name *</Label>
                  <Input id="name" value={formData.name} onChange={(e) => setFormData({...formData, name: e.target.value})} required placeholder="e.g., 2025/2026" />
                </div>
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="start_date">Start Date *</Label>
                    <Input id="start_date" type="date" value={formData.start_date} onChange={(e) => setFormData({...formData, start_date: e.target.value})} required />
                  </div>
                  <div>
                    <Label htmlFor="end_date">End Date *</Label>
                    <Input id="end_date" type="date" value={formData.end_date} onChange={(e) => setFormData({...formData, end_date: e.target.value})} required />
                  </div>
                </div>
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="reg_start">Registration Start</Label>
                    <Input id="reg_start" type="date" value={formData.registration_start_date} onChange={(e) => setFormData({...formData, registration_start_date: e.target.value})} />
                  </div>
                  <div>
                    <Label htmlFor="reg_end">Registration End</Label>
                    <Input id="reg_end" type="date" value={formData.registration_end_date} onChange={(e) => setFormData({...formData, registration_end_date: e.target.value})} />
                  </div>
                </div>
                <Button type="submit" className="w-full">Create Academic Year</Button>
              </form>
            </DialogContent>
          </Dialog>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Academic Years</CardTitle>
            <CardDescription>List of all academic years</CardDescription>
          </CardHeader>
          <CardContent>
            {loading ? (
              <div className="text-center py-8">Loading...</div>
            ) : (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Start Date</TableHead>
                    <TableHead>End Date</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {years.map((year) => (
                    <TableRow key={year.id}>
                      <TableCell className="font-medium">{year.name}</TableCell>
                      <TableCell>{new Date(year.start_date).toLocaleDateString()}</TableCell>
                      <TableCell>{new Date(year.end_date).toLocaleDateString()}</TableCell>
                      <TableCell>
                        {year.is_active ? (
                          <Badge className="bg-green-600"><Check className="h-3 w-3 mr-1" />Active</Badge>
                        ) : (
                          <Badge variant="secondary">Inactive</Badge>
                        )}
                      </TableCell>
                      <TableCell className="text-right">
                        <div className="flex justify-end gap-2">
                          {!year.is_active && (
                            <Button size="sm" variant="outline" onClick={() => handleActivate(year.id)}>
                              Activate
                            </Button>
                          )}
                          {!year.is_active && (
                            <Button size="sm" variant="outline" onClick={() => handleDelete(year.id)}>
                              <Trash2 className="h-4 w-4" />
                            </Button>
                          )}
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            )}
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}
