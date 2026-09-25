<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Orchard;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Notifications\TicketAssignedAlert;
use App\Notifications\TicketStaffReplyNotification;
use App\Notifications\TicketStatusChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $currentAdmin = Auth::guard('admin')->user();

        $query = Ticket::with(['customer', 'orchard', 'assignedStaff', 'attachments'])
            ->withCount(['messages' => fn ($q) => $q->where('is_internal', false)]);

        // Filter: Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter: Priority
        if ($request->filled('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        // Filter: Category
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter: Assigned staff
        if ($request->filled('assigned_to') && $request->assigned_to !== 'all') {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } elseif ($request->assigned_to === 'my_tickets') {
                $query->where('assigned_to', $currentAdmin->id);
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        // Search
        if ($request->filled('q')) {
            $term = '%' . trim($request->q) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('ticket_number', 'like', $term)
                  ->orWhere('subject', 'like', $term)
                  ->orWhereHas('customer', function ($cq) use ($term) {
                      $cq->where('name', 'like', $term)
                         ->orWhere('phone', 'like', $term)
                         ->orWhere('orchardist_id', 'like', $term);
                  });
            });
        }

        $tickets = $query->latest('updated_at')->paginate(20)->withQueryString();

        // Metrics for summary cards
        $metrics = [
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'awaiting_farmer' => Ticket::where('status', 'awaiting_farmer')->count(),
            'resolved' => Ticket::where('status', 'resolved')->count(),
            'unassigned' => Ticket::whereNull('assigned_to')->whereNotIn('status', ['resolved', 'closed'])->count(),
            'my_tickets' => Ticket::where('assigned_to', $currentAdmin->id)->whereNotIn('status', ['resolved', 'closed'])->count(),
        ];

        $staffList = Admin::where('is_active', true)->orderBy('name')->get();

        return view('admin.tickets.index', compact('tickets', 'metrics', 'staffList'));
    }

    public function create()
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->limit(200)->get();
        $staffList = Admin::where('is_active', true)->orderBy('name')->get();

        return view('admin.tickets.create', compact('customers', 'staffList'));
    }

    public function store(Request $request)
    {
        $currentAdmin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')],
            'orchard_id' => ['nullable', 'integer', Rule::exists('orchards', 'id')],
            'assigned_to' => ['nullable', 'integer', Rule::exists('admins', 'id')],
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(array_keys(Ticket::CATEGORIES))],
            'priority' => ['required', 'string', Rule::in(array_keys(Ticket::PRIORITIES))],
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:15360'],
        ]);

        return DB::transaction(function () use ($validated, $currentAdmin, $request) {
            $ticket = Ticket::create([
                'customer_id' => $validated['customer_id'],
                'orchard_id' => $validated['orchard_id'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'subject' => $validated['subject'],
                'category' => $validated['category'],
                'priority' => $validated['priority'],
                'status' => 'open',
                'creation_mode' => 'standard',
                'last_reply_at' => now(),
                'last_reply_by' => 'admin',
            ]);

            $message = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_type' => 'admin',
                'sender_id' => $currentAdmin->id,
                'message' => $validated['message'],
                'is_internal' => false,
            ]);

            if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
                $file = $request->file('attachment');
                $mime = $file->getMimeType() ?: '';
                $type = str_contains($mime, 'image') ? 'image' : (str_contains($mime, 'audio') ? 'audio' : 'document');
                $path = $file->store('tickets/' . $ticket->id, 'public');

                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'message_id' => $message->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $type,
                    'file_size' => $file->getSize(),
                ]);
            }

            if ($ticket->assigned_to && $ticket->assigned_to !== $currentAdmin->id) {
                try {
                    $ticket->assignedStaff?->notify(new TicketAssignedAlert($ticket));
                } catch (\Throwable $e) {}
            }

            return redirect()->route('admin.tickets.show', $ticket)
                ->with('success', "Ticket {$ticket->ticket_number} created successfully.");
        });
    }

    public function show(Ticket $ticket)
    {
        $ticket->load([
            'customer.orchards',
            'orchard',
            'assignedStaff',
            'messages' => fn ($q) => $q->with('attachments')->orderBy('created_at', 'asc'),
            'attachments',
        ]);

        $staffList = Admin::where('is_active', true)->orderBy('name')->get();

        // Canned responses for quick advisory resolution
        $cannedResponses = [
            [
                'title' => 'Apple Scab Treatment Advisory',
                'category' => 'pest_disease',
                'body' => "Dear Farmer, based on the leaf symptoms in your photo, this is an early infection of Apple Scab (Venturia inaequalis). \n\nRecommended Action:\n1. Apply Mancozeb 75% WP @ 300g per 100L water (protectant) OR Difenoconazole 25% EC @ 30ml per 100L water (curative).\n2. Spray during clear weather conditions.\n3. Ensure thorough canopy coverage especially on the lower leaf surface.",
            ],
            [
                'title' => 'European Red Mite Advisory',
                'category' => 'pest_disease',
                'body' => "Dear Farmer, your crop exhibits mite infestation (bronzing/chlorosis of foliage).\n\nRecommended Action:\n1. Spray Fenazaquin 10% EC @ 100ml per 100L water OR Propargite 57% EC @ 100ml per 100L water.\n2. Do not spray during peak noon heat.\n3. Repeat spray after 12-14 days if mite count exceeds threshold.",
            ],
            [
                'title' => 'Canopy Pruning & Wound Care',
                'category' => 'pruning_training',
                'body' => "Dear Farmer, regarding your canopy and branch training inquiry:\n\n1. Maintain a strong central leader and remove any upright competing water sprouts.\n2. Disinfect your pruning shears with 70% alcohol or Dettol between trees.\n3. Immediately apply copper oxychloride paste or Bordeaux paste on cut wounds exceeding 1 inch diameter to prevent fungal canker.",
            ],
            [
                'title' => 'Soil & Foliar Nutrition Guidance',
                'category' => 'fertilizer_soil',
                'body' => "Dear Farmer, here is your nutrition schedule for the current orchard growth stage:\n\n1. Ensure balanced application of Zinc Sulphate (0.5%) and Boric acid (0.1%) during pre-bloom.\n2. For drip irrigation, apply water-soluble Calcium Nitrate @ 15kg/kanal in split doses.\n3. Keep soil moisture at field capacity without waterlogging.",
            ],
            [
                'title' => 'Field Officer Visit Scheduled',
                'category' => 'general',
                'body' => "Dear Farmer, we have reviewed your inquiry and our agricultural field technician has been assigned to conduct an on-site inspection of your orchard. Our officer will contact you shortly to coordinate the visit timing.",
            ],
        ];

        return view('admin.tickets.show', compact('ticket', 'staffList', 'cannedResponses'));
    }

    /**
     * Assign or reassign ticket to a staff member.
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $currentAdmin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'assigned_to' => ['nullable', 'integer', Rule::exists('admins', 'id')->where('is_active', true)],
        ]);

        $newStaffId = $validated['assigned_to'] ?: null;
        $prevStaffName = $ticket->assignedStaff?->name ?? 'Unassigned';

        if ($ticket->assigned_to == $newStaffId) {
            return back()->with('info', 'No assignment changes made.');
        }

        $ticket->update(['assigned_to' => $newStaffId]);
        $newStaff = $newStaffId ? Admin::find($newStaffId) : null;
        $newStaffName = $newStaff?->name ?? 'Unassigned';

        // Add system log entry
        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'system',
            'sender_id' => $currentAdmin->id,
            'message' => "Ticket reassigned from {$prevStaffName} to {$newStaffName} by {$currentAdmin->name}.",
            'is_internal' => true,
        ]);

        // Notify new assignee
        if ($newStaff && $newStaff->id !== $currentAdmin->id) {
            try {
                $newStaff->notify(new TicketAssignedAlert($ticket));
            } catch (\Throwable $e) {}
        }

        return back()->with('success', "Ticket successfully assigned to {$newStaffName}.");
    }

    /**
     * Update ticket status (open, in_progress, awaiting_farmer, resolved, closed).
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $currentAdmin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(array_keys(Ticket::STATUSES))],
        ]);

        $oldStatus = $ticket->status;
        $newStatus = $validated['status'];

        if ($oldStatus === $newStatus) {
            return back()->with('info', 'Status already set to ' . $ticket->statusLabel());
        }

        $updates = ['status' => $newStatus];
        if ($newStatus === 'resolved') {
            $updates['resolved_at'] = now();
        } elseif ($newStatus === 'closed') {
            $updates['closed_at'] = now();
        } elseif ($oldStatus === 'resolved' || $oldStatus === 'closed') {
            $updates['resolved_at'] = null;
            $updates['closed_at'] = null;
        }

        $ticket->update($updates);

        // Add system message in thread
        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'system',
            'sender_id' => $currentAdmin->id,
            'message' => "Status changed from {$ticket::STATUSES[$oldStatus]} to {$ticket::STATUSES[$newStatus]} by {$currentAdmin->name}.",
            'is_internal' => false,
        ]);

        // Notify customer
        try {
            $ticket->customer?->notify(new TicketStatusChangedNotification($ticket, $oldStatus));
        } catch (\Throwable $e) {}

        return back()->with('success', "Ticket status updated to {$ticket::STATUSES[$newStatus]}.");
    }

    /**
     * Update ticket priority.
     */
    public function updatePriority(Request $request, Ticket $ticket)
    {
        $currentAdmin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'priority' => ['required', 'string', Rule::in(array_keys(Ticket::PRIORITIES))],
        ]);

        $oldPriority = $ticket->priority;
        $newPriority = $validated['priority'];

        if ($oldPriority === $newPriority) {
            return back();
        }

        $ticket->update(['priority' => $newPriority]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'system',
            'sender_id' => $currentAdmin->id,
            'message' => "Priority changed from {$ticket::PRIORITIES[$oldPriority]} to {$ticket::PRIORITIES[$newPriority]} by {$currentAdmin->name}.",
            'is_internal' => true,
        ]);

        return back()->with('success', "Ticket priority updated to {$ticket::PRIORITIES[$newPriority]}.");
    }

    /**
     * Post a public reply to the farmer.
     */
    public function addMessage(Request $request, Ticket $ticket)
    {
        $currentAdmin = Auth::guard('admin')->user();

        $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:15360'],
            'mark_awaiting' => ['nullable', 'boolean'],
        ]);

        return DB::transaction(function () use ($request, $ticket, $currentAdmin) {
            $msg = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_type' => 'admin',
                'sender_id' => $currentAdmin->id,
                'message' => $request->input('message'),
                'is_internal' => false,
            ]);

            if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
                $file = $request->file('attachment');
                $mime = $file->getMimeType() ?: '';
                $type = str_contains($mime, 'image') ? 'image' : (str_contains($mime, 'audio') ? 'audio' : 'document');
                $path = $file->store('tickets/' . $ticket->id, 'public');

                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'message_id' => $msg->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $type,
                    'file_size' => $file->getSize(),
                ]);
            }

            // Update ticket activity
            $updates = [
                'last_reply_at' => now(),
                'last_reply_by' => 'admin',
            ];

            // If ticket was open, mark as in_progress or awaiting_farmer
            if ($ticket->status === 'open' || $request->boolean('mark_awaiting', true)) {
                $updates['status'] = $request->boolean('mark_awaiting', true) ? 'awaiting_farmer' : 'in_progress';
            }

            $ticket->update($updates);

            // Notify farmer
            try {
                $ticket->customer?->notify(new TicketStaffReplyNotification($ticket, $msg));
            } catch (\Throwable $e) {}

            return back()->with('success', 'Response posted and sent to farmer.');
        });
    }

    /**
     * Add an internal note (visible only to admins).
     */
    public function addInternalNote(Request $request, Ticket $ticket)
    {
        $currentAdmin = Auth::guard('admin')->user();

        $request->validate([
            'note' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:15360'],
        ]);

        return DB::transaction(function () use ($request, $ticket, $currentAdmin) {
            $msg = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_type' => 'admin',
                'sender_id' => $currentAdmin->id,
                'message' => $request->input('note'),
                'is_internal' => true,
            ]);

            if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
                $file = $request->file('attachment');
                $mime = $file->getMimeType() ?: '';
                $type = str_contains($mime, 'image') ? 'image' : (str_contains($mime, 'audio') ? 'audio' : 'document');
                $path = $file->store('tickets/' . $ticket->id, 'public');

                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'message_id' => $msg->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $type,
                    'file_size' => $file->getSize(),
                ]);
            }

            return back()->with('success', 'Internal note added.');
        });
    }

    /**
     * Delete ticket (Super Admin or authorized staff).
     */
    public function destroy(Ticket $ticket)
    {
        // Delete stored files
        try {
            Storage::disk('public')->deleteDirectory('tickets/' . $ticket->id);
        } catch (\Throwable $e) {}

        $ticketNumber = $ticket->ticket_number;
        $ticket->delete();

        return redirect()->route('admin.tickets.index')->with('success', "Ticket {$ticketNumber} deleted.");
    }
}
