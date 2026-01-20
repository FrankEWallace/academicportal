import { useState, useEffect } from 'react';
import { Bell, Check, CheckCheck, Mail, DollarSign, BookOpen, Calendar, AlertCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import apiClient from '@/lib/apiClient';
import { useToast } from '@/hooks/use-toast';
import { formatDistanceToNow } from 'date-fns';

interface Notification {
  id: number;
  type: string;
  data: {
    title: string;
    message: string;
    action_url?: string;
    type?: string;
  };
  read_at: string | null;
  created_at: string;
}

const getNotificationIcon = (type: string) => {
  switch (type) {
    case 'grade_published':
      return <BookOpen className="h-4 w-4" />;
    case 'payment_due':
    case 'payment_confirmation':
      return <DollarSign className="h-4 w-4" />;
    case 'announcement':
      return <AlertCircle className="h-4 w-4" />;
    case 'timetable_update':
      return <Calendar className="h-4 w-4" />;
    default:
      return <Mail className="h-4 w-4" />;
  }
};

const getNotificationColor = (type: string) => {
  switch (type) {
    case 'grade_published':
      return 'text-green-600 bg-green-50';
    case 'payment_due':
      return 'text-red-600 bg-red-50';
    case 'payment_confirmation':
      return 'text-blue-600 bg-blue-50';
    case 'announcement':
      return 'text-orange-600 bg-orange-50';
    default:
      return 'text-gray-600 bg-gray-50';
  }
};

export function NotificationCenter() {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(false);
  const [open, setOpen] = useState(false);
  const { toast } = useToast();

  const fetchNotifications = async () => {
    try {
      setLoading(true);
      const response = await apiClient.get('/notifications?per_page=20');
      setNotifications(response.data.data.data || []);
    } catch (error) {
      console.error('Error fetching notifications:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchUnreadCount = async () => {
    try {
      const response = await apiClient.get('/notifications/unread-count');
      setUnreadCount(response.data.data.unread_count || 0);
    } catch (error) {
      console.error('Error fetching unread count:', error);
    }
  };

  const markAsRead = async (id: number) => {
    try {
      await apiClient.post(`/notifications/${id}/mark-read`);
      setNotifications(prev =>
        prev.map(n => (n.id === id ? { ...n, read_at: new Date().toISOString() } : n))
      );
      setUnreadCount(prev => Math.max(0, prev - 1));
    } catch (error) {
      console.error('Error marking notification as read:', error);
    }
  };

  const markAllAsRead = async () => {
    try {
      await apiClient.post('/notifications/mark-all-read');
      setNotifications(prev =>
        prev.map(n => ({ ...n, read_at: new Date().toISOString() }))
      );
      setUnreadCount(0);
      toast({
        title: 'Success',
        description: 'All notifications marked as read',
      });
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to mark all notifications as read',
        variant: 'destructive',
      });
    }
  };

  useEffect(() => {
    fetchUnreadCount();
    const interval = setInterval(fetchUnreadCount, 60000); // Check every minute
    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    if (open) {
      fetchNotifications();
    }
  }, [open]);

  const unreadNotifications = notifications.filter(n => !n.read_at);
  const readNotifications = notifications.filter(n => n.read_at);

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <Button variant="ghost" size="icon" className="relative">
          <Bell className="h-5 w-5" />
          {unreadCount > 0 && (
            <Badge
              variant="destructive"
              className="absolute -top-1 -right-1 h-5 w-5 flex items-center justify-center p-0 text-xs"
            >
              {unreadCount > 99 ? '99+' : unreadCount}
            </Badge>
          )}
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-96 p-0" align="end">
        <div className="flex items-center justify-between p-4 border-b">
          <h3 className="font-semibold">Notifications</h3>
          {unreadCount > 0 && (
            <Button
              variant="ghost"
              size="sm"
              onClick={markAllAsRead}
              className="h-8"
            >
              <CheckCheck className="h-4 w-4 mr-2" />
              Mark all read
            </Button>
          )}
        </div>

        <Tabs defaultValue="all" className="w-full">
          <TabsList className="w-full justify-start rounded-none border-b bg-transparent p-0">
            <TabsTrigger
              value="all"
              className="rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent"
            >
              All ({notifications.length})
            </TabsTrigger>
            <TabsTrigger
              value="unread"
              className="rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent"
            >
              Unread ({unreadCount})
            </TabsTrigger>
          </TabsList>

          <TabsContent value="all" className="m-0">
            <ScrollArea className="h-[400px]">
              {loading ? (
                <div className="p-8 text-center text-muted-foreground">
                  Loading notifications...
                </div>
              ) : notifications.length === 0 ? (
                <div className="p-8 text-center text-muted-foreground">
                  <Bell className="h-12 w-12 mx-auto mb-2 opacity-20" />
                  <p>No notifications yet</p>
                </div>
              ) : (
                <div className="divide-y">
                  {notifications.map((notification) => (
                    <NotificationItem
                      key={notification.id}
                      notification={notification}
                      onMarkRead={markAsRead}
                    />
                  ))}
                </div>
              )}
            </ScrollArea>
          </TabsContent>

          <TabsContent value="unread" className="m-0">
            <ScrollArea className="h-[400px]">
              {unreadNotifications.length === 0 ? (
                <div className="p-8 text-center text-muted-foreground">
                  <Check className="h-12 w-12 mx-auto mb-2 opacity-20" />
                  <p>All caught up!</p>
                </div>
              ) : (
                <div className="divide-y">
                  {unreadNotifications.map((notification) => (
                    <NotificationItem
                      key={notification.id}
                      notification={notification}
                      onMarkRead={markAsRead}
                    />
                  ))}
                </div>
              )}
            </ScrollArea>
          </TabsContent>
        </Tabs>
      </PopoverContent>
    </Popover>
  );
}

function NotificationItem({
  notification,
  onMarkRead,
}: {
  notification: Notification;
  onMarkRead: (id: number) => void;
}) {
  const isUnread = !notification.read_at;
  const type = notification.data.type || notification.type;

  return (
    <div
      className={`p-4 hover:bg-muted/50 cursor-pointer transition-colors ${
        isUnread ? 'bg-blue-50/50' : ''
      }`}
      onClick={() => isUnread && onMarkRead(notification.id)}
    >
      <div className="flex gap-3">
        <div className={`p-2 rounded-full h-fit ${getNotificationColor(type)}`}>
          {getNotificationIcon(type)}
        </div>
        <div className="flex-1 min-w-0">
          <div className="flex items-start justify-between gap-2">
            <p className="font-medium text-sm">{notification.data.title}</p>
            {isUnread && (
              <div className="h-2 w-2 rounded-full bg-blue-600 flex-shrink-0 mt-1" />
            )}
          </div>
          <p className="text-sm text-muted-foreground mt-1 line-clamp-2">
            {notification.data.message}
          </p>
          <p className="text-xs text-muted-foreground mt-2">
            {formatDistanceToNow(new Date(notification.created_at), {
              addSuffix: true,
            })}
          </p>
        </div>
      </div>
    </div>
  );
}
