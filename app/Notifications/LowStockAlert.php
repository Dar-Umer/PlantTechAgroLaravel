<?php

namespace App\Notifications;

use App\Models\Product;
use App\Notifications\Concerns\ConfigurableChannel;
use App\Support\Format;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    use Queueable, ConfigurableChannel;

    public function __construct(public Product $product) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Low stock alert: '.$this->product->name)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->product->name.' has reached its low stock threshold.')
            ->line('**Current stock:** ' . Format::qty($this->product->stock_qty) . ' ' . $this->product->unit)
            ->line('**Threshold:** ' . Format::qty($this->product->low_stock_threshold) . ' ' . $this->product->unit)
            ->action('View Products', route('admin.products.index'));
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