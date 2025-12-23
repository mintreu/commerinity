<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NoticeController extends Controller
{
    /**
     * Get active notices for the authenticated user.
     * Returns notices targeted to user's type/stage, excluding dismissed ones.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notices = Notice::query()
            ->active()
            ->scheduled()
            ->forUser($user)
            ->notDismissedBy($user)
            ->byPriority()
            ->limit(10)
            ->get()
            ->map(fn (Notice $notice) => [
                'uuid' => $notice->uuid,
                'title' => $notice->title,
                'content' => $notice->content,
                'type' => $notice->type,
                'type_color' => $notice->type_color,
                'type_icon' => $notice->type_icon,
                'cta_text' => $notice->cta_text,
                'cta_link' => $notice->cta_link,
                'icon' => $notice->icon,
                'color' => $notice->color,
                'image_url' => $notice->image_url,
                'priority' => $notice->priority,
            ]);

        return response()->json([
            'success' => true,
            'data' => $notices,
        ]);
    }

    /**
     * Get a single notice.
     */
    public function show(Request $request, Notice $notice): JsonResponse
    {
        // Record view
        $notice->recordView();

        return response()->json([
            'success' => true,
            'data' => [
                'uuid' => $notice->uuid,
                'title' => $notice->title,
                'content' => $notice->content,
                'type' => $notice->type,
                'type_color' => $notice->type_color,
                'type_icon' => $notice->type_icon,
                'cta_text' => $notice->cta_text,
                'cta_link' => $notice->cta_link,
                'icon' => $notice->icon,
                'color' => $notice->color,
                'image_url' => $notice->image_url,
            ],
        ]);
    }

    /**
     * Dismiss a notice for the current user.
     */
    public function dismiss(Request $request, Notice $notice): JsonResponse
    {
        $user = $request->user();

        $notice->dismissFor($user);

        return response()->json([
            'success' => true,
            'message' => 'Notice dismissed',
        ]);
    }

    /**
     * Record a click on a notice CTA.
     */
    public function click(Request $request, Notice $notice): JsonResponse
    {
        $notice->recordClick();

        return response()->json([
            'success' => true,
            'data' => [
                'cta_link' => $notice->cta_link,
            ],
        ]);
    }
}
