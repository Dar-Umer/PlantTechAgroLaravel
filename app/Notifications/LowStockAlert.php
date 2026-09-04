<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    use Queueable;

    public function __construct(public Product $product) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Low stock alert',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'stock_qty' => (float) $this->product->stock_qty,
            'threshold' => (float) $this->product->low_stock_threshold,
            'unit' => $this->product->unit,
            'supplier' => $this->product->supplier?->name,
        ];
    }
}
