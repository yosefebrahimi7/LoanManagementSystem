<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Services\Interfaces\NotificationServiceInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 *   name="Notifications",
 *   description="User notification endpoints"
 * )
 */
class NotificationController extends Controller
{
    use ApiResponseTrait;

    protected NotificationServiceInterface $notificationService;

    public function __construct(NotificationServiceInterface $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user notifications
     * 
     * @OA\Get(
     *   path="/api/notifications",
     *   tags={"Notifications"},
     *   summary="Get user notifications",
     *   operationId="getNotifications",
     *   security={{"sanctum": {}}},
     *   @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="integer", default=10)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Notifications retrieved successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="data", type="object"),
     *       @OA\Property(property="data.notifications", type="array", @OA\Items(type="object")),
     *       @OA\Property(property="data.unread_count", type="integer", example=5)
     *     )
     *   )
     * )
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $limit = $request->input('limit', 10);
        
        $data = $this->notificationService->getUserNotifications($user, $limit);

        return $this->successResponse($data);
    }

    /**
     * Get unread notifications count
     * 
     * @OA\Get(
     *   path="/api/notifications/unread-count",
     *   tags={"Notifications"},
     *   summary="Get unread notifications count",
     *   operationId="getUnreadCount",
     *   security={{"sanctum": {}}},
     *   @OA\Response(
     *     response=200,
     *     description="Unread count retrieved successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="data", type="object"),
     *       @OA\Property(property="data.count", type="integer", example=5)
     *     )
     *   )
     * )
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $count = $this->notificationService->getUnreadCount($user);

        return $this->successResponse(['count' => $count]);
    }

    /**
     * Mark notification as read
     * 
     * @OA\Patch(
     *   path="/api/notifications/{id}/read",
     *   tags={"Notifications"},
     *   summary="Mark notification as read",
     *   operationId="markNotificationAsRead",
     *   security={{"sanctum": {}}},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Notification marked as read",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="message", type="string", example="اعلان به عنوان خوانده شده علامت‌گذاری شد")
     *     )
     *   ),
     *   @OA\Response(response=403, description="Unauthorized"),
     *   @OA\Response(response=404, description="Notification not found")
     * )
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        
        $notification = $user->notifications()->where('id', $id)->first();
        if (!$notification) {
            return $this->errorResponse('اعلان یافت نشد', 404);
        }

        // Check policy
        if (!Gate::allows('markAsRead', $notification)) {
            return $this->errorResponse('دسترسی مجاز نیست', 403);
        }

        $this->notificationService->markAsRead($user, $id);

        return $this->successResponse([], 'اعلان به عنوان خوانده شده علامت‌گذاری شد');
    }

    /**
     * Mark all notifications as read
     * 
     * @OA\Patch(
     *   path="/api/notifications/mark-all-read",
     *   tags={"Notifications"},
     *   summary="Mark all notifications as read",
     *   operationId="markAllNotificationsAsRead",
     *   security={{"sanctum": {}}},
     *   @OA\Response(
     *     response=200,
     *     description="All notifications marked as read",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="message", type="string", example="همه اعلان‌ها به عنوان خوانده شده علامت‌گذاری شدند")
     *     )
     *   )
     * )
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $this->notificationService->markAllAsRead($user);

        return $this->successResponse([], 'همه اعلان‌ها به عنوان خوانده شده علامت‌گذاری شدند');
    }

    /**
     * Delete notification
     * 
     * @OA\Delete(
     *   path="/api/notifications/{id}",
     *   tags={"Notifications"},
     *   summary="Delete notification",
     *   operationId="deleteNotification",
     *   security={{"sanctum": {}}},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Notification deleted",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="message", type="string", example="اعلان حذف شد")
     *     )
     *   ),
     *   @OA\Response(response=403, description="Unauthorized"),
     *   @OA\Response(response=404, description="Notification not found")
     * )
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        $notification = $user->notifications()->where('id', $id)->first();
        if (!$notification) {
            return $this->errorResponse('اعلان یافت نشد', 404);
        }

        // Check policy
        if (!Gate::allows('delete', $notification)) {
            return $this->errorResponse('دسترسی مجاز نیست', 403);
        }

        $this->notificationService->deleteNotification($user, $id);

        return $this->successResponse([], 'اعلان حذف شد');
    }
}
