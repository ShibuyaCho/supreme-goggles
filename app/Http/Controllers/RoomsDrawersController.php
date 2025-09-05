<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;

class RoomsDrawersController extends Controller
{
    public function index()
    {
        $rooms = Room::all()->map(function (Room $room) {
            $categoryMap = [
                'production' => 'processing',
                'processing' => 'processing',
                'storage' => 'storage',
                'sales' => 'sales',
            ];
            $category = $categoryMap[$room->type] ?? 'storage';

            return [
                'id' => $room->id,
                'name' => $room->name,
                'category' => $category,
                'metrc_id' => $room->room_id ?? ('ROOM-' . str_pad((string)$room->id, 3, '0', STR_PAD_LEFT)),
                'current_items' => $room->current_stock ?? 0,
                'max_capacity' => $room->max_capacity ?? 0,
                'square_feet' => 0,
                'temperature' => null,
                'humidity' => null,
                'compliance_status' => $room->is_active ? 'compliant' : 'issue',
                'drawers' => [],
            ];
        })->toArray();

        return view('rooms-drawers.index', compact('rooms'));
    }
}
