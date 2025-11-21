<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index()
    {
        $events = Auth::user()->events()->get();
        $events = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'full_name' => $event->full_name,
                'whatsapp' => $event->whatsapp,
                'notes' => $event->notes,
                'all_day' => $event->all_day,
                'start_time' => Carbon::parse($event->start_time)->format('Y-m-d'),
                'end_time' => Carbon::parse($event->end_time)->format('Y-m-d'),
            ];
        });

        return $events;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'all_day' => 'boolean',
            'notes' => 'nullable|string',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // START_TIME
        if (isset($validated['start_time'])) {
            $date = $this->parseDateFlexible($validated['start_time']);
            if (!$date) {
                return response()->json([
                    'message' => 'Formato de data inválido para start_time'
                ], 422);
            }
            $validated['start_time'] = $date->format('Y/m/d');
        }

        // END_TIME
        if (isset($validated['end_time'])) {
            $date = $this->parseDateFlexible($validated['end_time']);
            if (!$date) {
                return response()->json([
                    'message' => 'Formato de data inválido para end_time'
                ], 422);
            }
            $validated['end_time'] = $date->format('Y/m/d');
        }

        // Se end_time não existir → adiciona +1 dia
        if (!isset($validated['end_time']) && isset($validated['start_time'])) {
            $validated['end_time'] = Carbon\Carbon::parse($validated['start_time'])
                ->addDay()
                ->format('Y/m/d');
        }

        $event = Auth::user()->events()->create($validated);

        return response()->json($event, 201);
    }

    public function show(Event $event)
    {
        $this->authorize('view', $event);
        return $event;
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $event->update($request->validate([
            'full_name' => 'sometimes|string|max:255',
            'whatsapp' => 'sometimes|string|max:20',
            'all_day' => 'boolean',
            'notes' => 'nullable|string',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
        ]));

        return response()->json($event);
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);
        $event->delete();
        return response()->noContent();
    }

    private function parseDateFlexible($date)
    {
        $formats = [
            'Y-m-d',
            'd/m/Y',
            'd-m-Y',
            'Y/m/d'
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $date);
            } catch (\Exception $e) {
                // tenta o próximo formato
            }
        }

        // Última tentativa: Carbon::parse (tenta auto detectar)
        try {
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return null;
        }
    }
}
