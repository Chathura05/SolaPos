<?php

namespace App\Mail;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SaleReceiptMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Sale $sale;

    /**
     * Create a new message instance.
     */
    public function __construct(Sale $sale)
    {
        $this->sale = $sale->loadMissing(['items', 'cashier', 'showroom']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Receipt — ' . $this->sale->invoice_number . ' | ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.sale_receipt',
            with: [
                'sale'         => $this->sale,
                'storeName'    => setting('store_name', config('app.name')),
                'storeAddress' => setting('store_address', ''),
                'storePhone'   => setting('store_phone', ''),
                'currency'     => setting('currency_symbol', 'Rs.'),
                'footerText'   => setting('receipt_footer', 'Thank you for your purchase!'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        // Generate the PDF inside the queued job to avoid blocking the controller
        // and to prevent binary serialization issues on the queue.
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pos.receipt', [
            'sale' => $this->sale,
            'isPdf' => true
        ]);

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(fn () => $pdf->output(), 'Invoice_' . $this->sale->invoice_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
