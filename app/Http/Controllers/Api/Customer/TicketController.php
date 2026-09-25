<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Orchard;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Notifications\FarmerTicketCreated;
use App\Notifications\TicketCustomerReplyAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TicketController extends Controller
{
    /**
     * List farmer's tickets with status filtering & summary metrics.
     */
    public function index(Request $request)
    {
        $customer = $request->user();

        $query = Ticket::where('customer_id', $customer->id)
            ->with(['orchard:id,name', 'assignedStaff:id,name', 'attachments'])
            ->withCount('messages');

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Category filter
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Search query
        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(function ($q) use ($term) {
                $q->where('ticket_number', 'like', $term)
                  ->orWhere('subject', 'like', $term);
            });
        }

        $tickets = $query->latest('updated_at')->paginate(20);

        // Summaries for app dashboard cards
        $counts = Ticket::where('customer_id', $customer->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('open', 'in_progress', 'awaiting_farmer') THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
            ")
            ->first();

        return response()->json([
            'tickets' => $tickets->map(fn (Ticket $ticket) => $this->transformTicketSummary($ticket))->values(),
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
            'summary' => [
                'total' => (int) ($counts->total ?? 0),
                'active' => (int) ($counts->active ?? 0),
                'resolved' => (int) ($counts->resolved ?? 0),
                'closed' => (int) ($counts->closed ?? 0),
            ],
        ]);
    }

    /**
     * Get available ticket categories for the app selector.
     */
    public function categories()
    {
        $list = [
            [
                'id' => 'pest_disease',
                'name' => 'Pest & Disease / کیڑے اور بیماریاں',
                'description' => 'Fungus, leaf scab, red mites, borers, fruit drop, or root damage.',
                'icon' => 'bug',
            ],
            [
                'id' => 'fertilizer_soil',
                'name' => 'Fertilizer & Nutrition / کھاد اور غذائیت',
                'description' => 'Soil testing, nutrient deficiency, dosage charts, foliar sprays.',
                'icon' => 'flask',
            ],
            [
                'id' => 'irrigation',
                'name' => 'Irrigation & Drip / آبپاشی نظام',
                'description' => 'Drip lateral issues, fertigation pump, filter maintenance, water schedule.',
                'icon' => 'water',
            ],
            [
                'id' => 'pruning_training',
                'name' => 'Pruning & Canopy / شاخ تراشی',
                'description' => 'Winter/summer pruning, trellis support, central leader training.',
                'icon' => 'scissors',
            ],
            [
                'id' => 'plantation',
                'name' => 'High Density Planting / نئے پودے لگانا',
                'description' => 'Rootstocks (M9/MM106), clonal selection, layout and tree density.',
                'icon' => 'tree',
            ],
            [
                'id' => 'billing_orders',
                'name' => 'Orders & Billing / بل اور ادائیگیاں',
                'description' => 'Work orders, nursery invoices, quotations, receipt inquiries.',
                'icon' => 'document-text',
            ],
            [
                'id' => 'general',
                'name' => 'General Farm Query / عام رہنمائی',
                'description' => 'Weather impact, subsidy schemes, orchard visits, general advice.',
                'icon' => 'help-circle',
            ],
        ];

        return response()->json([
            'categories' => $list,
        ]);
    }

    /**
     * Create a new ticket (Supports both Quick Photo Submission & Standard Form).
     */
    public function store(Request $request)
    {
        $customer = $request->user();

        // Check if anything at all was provided
        $hasPhotos = $request->hasFile('images') || $request->hasFile('image');
        $hasVoice = $request->hasFile('voice_note');
        $hasText = $request->filled('description') || $request->filled('message') || $request->filled('subject');

        if (! $hasPhotos && ! $hasVoice && ! $hasText) {
            throw ValidationException::withMessages([
                'image' => 'Please upload a photo or write your query to create a ticket.',
            ]);
        }

        $validated = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', Rule::in(array_keys(Ticket::CATEGORIES))],
            'priority' => ['nullable', 'string', Rule::in(array_keys(Ticket::PRIORITIES))],
            'orchard_id' => ['nullable', 'integer', Rule::exists('orchards', 'id')->where('customer_id', $customer->id)],
            'description' => ['nullable', 'string', 'max:4000'],
            'message' => ['nullable', 'string', 'max:4000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:12288'],
            'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:12288'],
            'voice_note' => ['nullable', 'file', 'mimes:mp3,wav,m4a,aac,ogg,webm,mp4', 'max:20480'],
        ]);

        $textBody = $validated['description'] ?? $validated['message'] ?? null;
        $isQuickPhoto = empty($validated['subject']);

        // Auto-assign subject if farmer used 1-click photo upload
        if (empty($validated['subject'])) {
            $orchardName = null;
            if (! empty($validated['orchard_id'])) {
                $orchardName = Orchard::where('id', $validated['orchard_id'])->value('name');
            }
            $subject = $orchardName
                ? "Photo Query - {$orchardName} (" . date('d M') . ")"
                : "Farmer Photo Query (" . date('d M, h:i A') . ")";
            $category = $validated['category'] ?? 'pest_disease'; // Most photo queries are pest/leaf/crop issues
            $creationMode = $hasVoice && ! $hasPhotos ? 'voice_quick' : 'photo_quick';
        } else {
            $subject = $validated['subject'];
            $category = $validated['category'] ?? 'general';
            $creationMode = 'standard';
        }

        $priority = $validated['priority'] ?? 'medium';

        return DB::transaction(function () use ($customer, $validated, $subject, $category, $priority, $creationMode, $textBody, $request) {
            $ticket = Ticket::create([
                'customer_id' => $customer->id,
                'orchard_id' => $validated['orchard_id'] ?? null,
                'subject' => $subject,
                'category' => $category,
                'priority' => $priority,
                'status' => 'open',
                'creation_mode' => $creationMode,
                'last_reply_at' => now(),
                'last_reply_by' => 'customer',
            ]);

            // Create initial ticket message
            $initialMessageText = $textBody;
            if (empty($initialMessageText)) {
                if ($creationMode === 'photo_quick') {
                    $initialMessageText = 'Submitted photo inquiry for technical diagnosis.';
                } elseif ($creationMode === 'voice_quick') {
                    $initialMessageText = 'Submitted voice note query.';
                } else {
                    $initialMessageText = 'Ticket opened.';
                }
            }

            $message = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_type' => 'customer',
                'sender_id' => $customer->id,
                'message' => $initialMessageText,
                'is_internal' => false,
            ]);

            // Save attachments
            $this->storeUploadedAttachments($request, $ticket, $message);

            // Notify active administrators of the new ticket
            try {
                Admin::where('is_active', true)->get()->each(function (Admin $admin) use ($ticket) {
                    $admin->notify(new FarmerTicketCreated($ticket));
                });
            } catch (\Throwable $e) {
                // Continue gracefully even if email provider is offline
            }

            return response()->json([
                'message' => 'Your ticket has been submitted successfully. Our agricultural team will review and respond shortly.',
                'ticket' => $this->transformTicketDetails($ticket->fresh(['orchard', 'assignedStaff', 'messages.attachments'])),
            ], 201);
        });
    }

    /**
     * View ticket details and full conversation history.
     */
    public function show(Request $request, int $id)
    {
        $customer = $request->user();

        $ticket = Ticket::where('customer_id', $customer->id)
            ->with(['orchard:id,name', 'assignedStaff:id,name,role', 'messages' => function ($q) {
                $q->where('is_internal', false)->with('attachments')->orderBy('created_at', 'asc');
            }])
            ->findOrFail($id);

        return response()->json([
            'ticket' => $this->transformTicketDetails($ticket),
        ]);
    }

    /**
     * Send a reply message from the farmer.
     */
    public function reply(Request $request, int $id)
    {
        $customer = $request->user();

        $ticket = Ticket::where('customer_id', $customer->id)->findOrFail($id);

        if ($ticket->status === 'closed') {
            throw ValidationException::withMessages([
                'message' => 'This ticket is closed. You can reopen it or create a new ticket.',
            ]);
        }

        $hasFiles = $request->hasFile('images') || $request->hasFile('image') || $request->hasFile('voice_note') || $request->hasFile('attachment');
        $hasText = $request->filled('message');

        if (! $hasFiles && ! $hasText) {
            throw ValidationException::withMessages([
                'message' => 'Please provide a message or attach a photo.',
            ]);
        }

        $request->validate([
            'message' => ['nullable', 'string', 'max:4000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:12288'],
            'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:12288'],
            'voice_note' => ['nullable', 'file', 'mimes:mp3,wav,m4a,aac,ogg,webm,mp4', 'max:20480'],
            'attachment' => ['nullable', 'file', 'max:15360'],
        ]);

        return DB::transaction(function () use ($request, $ticket, $customer) {
            $msg = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_type' => 'customer',
                'sender_id' => $customer->id,
                'message' => $request->input('message') ?: 'Sent attachment.',
                'is_internal' => false,
            ]);

            $this->storeUploadedAttachments($request, $ticket, $msg);

            // Re-activate ticket if it was resolved or waiting for farmer
            $updates = [
                'last_reply_at' => now(),
                'last_reply_by' => 'customer',
            ];
            if (in_array($ticket->status, ['awaiting_farmer', 'resolved'], true)) {
                $updates['status'] = 'in_progress';
                $updates['resolved_at'] = null;
            }
            $ticket->update($updates);

            // Notify assigned staff or active managers
            try {
                if ($ticket->assignedStaff) {
                    $ticket->assignedStaff->notify(new TicketCustomerReplyAlert($ticket, $msg));
                } else {
                    Admin::where('is_active', true)->limit(5)->get()->each(function (Admin $admin) use ($ticket, $msg) {
                        $admin->notify(new TicketCustomerReplyAlert($ticket, $msg));
                    });
                }
            } catch (\Throwable $e) {
                // Ignore transient notification error
            }

            return response()->json([
                'message' => 'Reply posted successfully.',
                'ticket' => $this->transformTicketDetails($ticket->fresh(['orchard', 'assignedStaff', 'messages.attachments'])),
            ]);
        });
    }

    /**
     * Farmer marks ticket as resolved/closed.
     */
    public function close(Request $request, int $id)
    {
        $customer = $request->user();
        $ticket = Ticket::where('customer_id', $customer->id)->findOrFail($id);

        if ($ticket->status === 'resolved' || $ticket->status === 'closed') {
            return response()->json([
                'message' => 'Ticket is already resolved.',
                'ticket' => $this->transformTicketDetails($ticket),
            ]);
        }

        $ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'system',
            'sender_id' => null,
            'message' => 'Farmer marked this ticket as resolved.',
            'is_internal' => false,
        ]);

        return response()->json([
            'message' => 'Thank you! Your ticket has been marked as resolved.',
            'ticket' => $this->transformTicketDetails($ticket->fresh(['orchard', 'assignedStaff', 'messages.attachments'])),
        ]);
    }

    /**
     * Farmer reopens a resolved ticket.
     */
    public function reopen(Request $request, int $id)
    {
        $customer = $request->user();
        $ticket = Ticket::where('customer_id', $customer->id)->findOrFail($id);

        $ticket->update([
            'status' => 'in_progress',
            'resolved_at' => null,
            'closed_at' => null,
            'last_reply_at' => now(),
            'last_reply_by' => 'customer',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'system',
            'sender_id' => null,
            'message' => 'Ticket reopened by farmer.',
            'is_internal' => false,
        ]);

        return response()->json([
            'message' => 'Ticket has been reopened. Our team will follow up.',
            'ticket' => $this->transformTicketDetails($ticket->fresh(['orchard', 'assignedStaff', 'messages.attachments'])),
        ]);
    }

    /**
     * Store all uploaded attachments (images, voice note, files).
     */
    protected function storeUploadedAttachments(Request $request, Ticket $ticket, TicketMessage $message): void
    {
        // 1. Array of images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('tickets/' . $ticket->id, 'public');
                    TicketAttachment::create([
                        'ticket_id' => $ticket->id,
                        'message_id' => $message->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => 'image',
                        'file_size' => $file->getSize(),
                    ]);
                }
            }
        }

        // 2. Single image
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file && $file->isValid()) {
                $path = $file->store('tickets/' . $ticket->id, 'public');
                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'message_id' => $message->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => 'image',
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // 3. Voice Note
        if ($request->hasFile('voice_note')) {
            $file = $request->file('voice_note');
            if ($file && $file->isValid()) {
                $path = $file->store('tickets/' . $ticket->id . '/audio', 'public');
                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'message_id' => $message->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName() ?: 'voice_note.m4a',
                    'file_type' => 'audio',
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // 4. Generic attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            if ($file && $file->isValid()) {
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
        }
    }

    /**
     * Transform ticket model for list view.
     */
    protected function transformTicketSummary(Ticket $ticket): array
    {
        $previewAttachment = $ticket->attachments->firstWhere('file_type', 'image');

        return [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'subject' => $ticket->subject,
            'category' => $ticket->category,
            'category_label' => $ticket->categoryLabel(),
            'priority' => $ticket->priority,
            'priority_label' => $ticket->priorityLabel(),
            'status' => $ticket->status,
            'status_label' => $ticket->statusLabel(),
            'creation_mode' => $ticket->creation_mode,
            'orchard_id' => $ticket->orchard_id,
            'orchard_name' => $ticket->orchard?->name,
            'assigned_staff_name' => $ticket->assignedStaff?->name,
            'preview_image_url' => $previewAttachment ? $previewAttachment->url() : null,
            'has_voice_note' => $ticket->attachments->contains('file_type', 'audio'),
            'messages_count' => $ticket->messages_count ?? $ticket->messages()->where('is_internal', false)->count(),
            'last_reply_at' => $ticket->last_reply_at?->toISOString(),
            'last_reply_by' => $ticket->last_reply_by,
            'created_at' => $ticket->created_at->toISOString(),
            'updated_at' => $ticket->updated_at->toISOString(),
        ];
    }

    /**
     * Transform ticket model for single detail view.
     */
    protected function transformTicketDetails(Ticket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'subject' => $ticket->subject,
            'category' => $ticket->category,
            'category_label' => $ticket->categoryLabel(),
            'priority' => $ticket->priority,
            'priority_label' => $ticket->priorityLabel(),
            'status' => $ticket->status,
            'status_label' => $ticket->statusLabel(),
            'creation_mode' => $ticket->creation_mode,
            'orchard_id' => $ticket->orchard_id,
            'orchard_name' => $ticket->orchard?->name,
            'assigned_staff' => $ticket->assignedStaff ? [
                'id' => $ticket->assignedStaff->id,
                'name' => $ticket->assignedStaff->name,
                'role' => $ticket->assignedStaff->role ? ucfirst(str_replace('_', ' ', $ticket->assignedStaff->role)) : 'Support Specialist',
            ] : null,
            'created_at' => $ticket->created_at->toISOString(),
            'updated_at' => $ticket->updated_at->toISOString(),
            'resolved_at' => $ticket->resolved_at?->toISOString(),
            'messages' => $ticket->messages->map(function (TicketMessage $msg) {
                return [
                    'id' => $msg->id,
                    'sender_type' => $msg->sender_type,
                    'sender_name' => $msg->sender_type === 'admin' ? ('PTA Support - ' . $msg->senderName()) : $msg->senderName(),
                    'sender_role' => $msg->senderRole(),
                    'message' => $msg->message,
                    'created_at' => $msg->created_at->toISOString(),
                    'attachments' => $msg->attachments->map(fn (TicketAttachment $att) => [
                        'id' => $att->id,
                        'file_name' => $att->file_name,
                        'file_type' => $att->file_type,
                        'url' => $att->url(),
                        'file_size' => $att->file_size,
                    ])->values(),
                ];
            })->values(),
        ];
    }
}
