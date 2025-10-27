import { useState } from 'react';
import { useNotifications, useUnreadNotificationsCount, useMarkNotificationAsRead, useDeleteNotification, useMarkAllNotificationsAsRead } from '../hooks/useNotifications';
import type { Notification } from '../types';
import { formatDistanceToNow } from 'date-fns';
import { faIR } from 'date-fns/locale';

function NotificationDropdown() {
  const [isOpen, setIsOpen] = useState(false);
  const { data, isLoading } = useNotifications(1, 10);
  const { data: unreadCount } = useUnreadNotificationsCount();
  const markAsReadMutation = useMarkNotificationAsRead();
  const deleteMutation = useDeleteNotification();
  const markAllAsReadMutation = useMarkAllNotificationsAsRead();

  const notifications = data?.notifications || [];
  const unreadCountNum = unreadCount || 0;

  const handleMarkAsRead = (notificationId: string, e: React.MouseEvent) => {
    e.stopPropagation();
    markAsReadMutation.mutate(notificationId);
  };

  const handleDelete = (notificationId: string, e: React.MouseEvent) => {
    e.stopPropagation();
    deleteMutation.mutate(notificationId);
  };

  const handleMarkAllAsRead = () => {
    markAllAsReadMutation.mutate();
  };

  const getNotificationIcon = (notification: Notification) => {
    const type = notification.data?.type || '';
    
    if (type.includes('approval') || type.includes('approve')) {
      return '✓';
    } else if (type.includes('reject') || type.includes('rejection')) {
      return '✗';
    } else if (type.includes('payment') || type.includes('paid')) {
      return '💰';
    } else if (type.includes('due') || type.includes('overdue')) {
      return '⚠';
    } else if (type.includes('complete') || type.includes('completed')) {
      return '✓';
    }
    return '🔔';
  };

  const getNotificationColor = (notification: Notification) => {
    const type = notification.data?.type || '';
    
    if (type.includes('approval') || type.includes('approve') || type.includes('complete')) {
      return 'text-green-600';
    } else if (type.includes('reject') || type.includes('rejection')) {
      return 'text-red-600';
    } else if (type.includes('payment') || type.includes('paid')) {
      return 'text-blue-600';
    } else if (type.includes('due') || type.includes('overdue')) {
      return 'text-yellow-600';
    }
    return 'text-gray-600';
  };

  const formatDate = (dateString: string) => {
    try {
      return formatDistanceToNow(new Date(dateString), {
        addSuffix: true,
        locale: faIR,
      });
    } catch {
      return dateString;
    }
  };

  return (
    <div className="dropdown dropdown-end">
      <button
        tabIndex={0}
        role="button"
        className="btn btn-ghost btn-circle relative"
        onClick={() => setIsOpen(!isOpen)}
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          className="h-6 w-6"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth={2}
            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
          />
        </svg>
        {unreadCountNum > 0 && (
          <span className="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-error text-error-content text-xs font-bold">
            {unreadCountNum > 9 ? '9+' : unreadCountNum}
          </span>
        )}
      </button>

      {isOpen && (
        <div
          tabIndex={0}
          className="menu dropdown-content z-[1] mt-3 w-80 rounded-box bg-base-100 p-2 shadow-lg"
        >
          <div className="flex items-center justify-between border-b pb-2 mb-2">
            <h3 className="text-lg font-bold">اعلان‌ها</h3>
            {unreadCountNum > 0 && (
              <button
                onClick={handleMarkAllAsRead}
                className="btn btn-xs btn-ghost"
              >
                همه را خوانده شده
              </button>
            )}
          </div>

          <div className="max-h-96 overflow-y-auto">
            {isLoading ? (
              <div className="flex justify-center items-center py-8">
                <span className="loading loading-spinner loading-md"></span>
              </div>
            ) : notifications.length === 0 ? (
              <div className="flex flex-col items-center justify-center py-8 text-gray-500">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  className="h-12 w-12 mb-2"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                  />
                </svg>
                <p>اعلان جدیدی وجود ندارد</p>
              </div>
            ) : (
              <div className="space-y-2">
                {notifications.map((notification: Notification) => (
                  <div
                    key={notification.id}
                    className={`p-3 rounded-lg border border-base-300 hover:bg-base-200 transition-colors ${
                      !notification.read_at ? 'bg-primary/5' : ''
                    }`}
                  >
                    <div className="flex items-start gap-3">
                      <div className={`text-2xl ${getNotificationColor(notification)}`}>
                        {getNotificationIcon(notification)}
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium text-base-content">
                          {notification.data?.message || 'اعلان جدید'}
                        </p>
                        <p className="text-xs text-base-content/60 mt-1">
                          {formatDate(notification.created_at)}
                        </p>
                      </div>
                      <div className="flex gap-1">
                        {!notification.read_at && (
                          <button
                            onClick={(e) => handleMarkAsRead(notification.id, e)}
                            className="btn btn-xs btn-ghost"
                            title="خوانده شده"
                          >
                            <svg
                              xmlns="http://www.w3.org/2000/svg"
                              className="h-4 w-4"
                              fill="none"
                              viewBox="0 0 24 24"
                              stroke="currentColor"
                            >
                              <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M5 13l4 4L19 7"
                              />
                            </svg>
                          </button>
                        )}
                        <button
                          onClick={(e) => handleDelete(notification.id, e)}
                          className="btn btn-xs btn-ghost text-error hover:text-error-focus"
                          title="حذف"
                        >
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                          >
                            <path
                              strokeLinecap="round"
                              strokeLinejoin="round"
                              strokeWidth={2}
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
}

export default NotificationDropdown;
