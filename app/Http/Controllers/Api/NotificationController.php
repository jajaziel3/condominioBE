<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        // Si hay un usuario autenticado, filtrar por user_id; sino devolver todas (para admin)
        $user = Auth::user();
        $query = Notification::query();
        if ($user) $query->where('user_id', $user->id);

        $notifications = $query->orderBy('created_at','desc')->paginate(50);

        return response()->json(['success' => true, 'data' => $notifications]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string',
            'body' => 'nullable|string',
            'category' => 'nullable|string',
            'data' => 'nullable|array'
        ]);

        $notification = Notification::create(array_merge($data, [
            'user_id' => $request->user()?->id ?? null,
        ]));

        return response()->json(['success' => true, 'data' => $notification], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $notif = Notification::find($id);
        if (!$notif) return response()->json(['success' => false, 'message' => 'Notificación no encontrada'], 404);
        return response()->json(['success' => true, 'data' => $notif]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $notif = Notification::find($id);
        if (!$notif) return response()->json(['success' => false, 'message' => 'Notificación no encontrada'], 404);

        $data = $request->validate([
            'title' => 'sometimes|string',
            'body' => 'sometimes|string',
            'is_read' => 'sometimes|boolean'
        ]);

        $notif->update($data);
        return response()->json(['success' => true, 'data' => $notif]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $notif = Notification::find($id);
        if (!$notif) return response()->json(['success' => false, 'message' => 'Notificación no encontrada'], 404);
        $notif->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Marcar la notificación como leída
     */
    public function markAsRead(string $id): JsonResponse
    {
        $notif = Notification::find($id);
        if (!$notif) return response()->json(['success' => false, 'message' => 'Notificación no encontrada'], 404);
        $notif->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['success' => true, 'data' => $notif]);
    }
}
