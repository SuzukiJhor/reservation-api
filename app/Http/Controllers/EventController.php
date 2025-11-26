<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index()
    {
        $user = Auth::user() ;
        $events = Event::where('company_id', $user->company_id)->get();

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
        $user_id = $request->user()->id;
        $user_company_id = Auth::user()->company_id;
      
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
            $validated['end_time'] = Carbon::parse($validated['start_time'])
                ->addDay()
                ->format('Y/m/d');
        }

        $validated['user_id'] = $user_id;
        $validated['company_id'] = $user_company_id;

        $event = Event::create($validated);
        return response()->json($event, 201);
    }

    public function show(Event $event)
    {
        $this->authorize('view', $event);
        return $event;
    }

    public function update(Request $request, Event $event)
    {
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

        try {
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return null;
        }
    }
}
