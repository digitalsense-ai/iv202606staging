<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OcrInvoicesSyncEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
   
    public $client_id;
    public $message;    

    /**
     * Create a new event instance.
     *    
     * @param $client_id
     * @param string $message
     * @return void
     */    
    public function __construct($client_id, $message)
    {      
        $this->client_id = $client_id;
        $this->message = $message;     
    }

    /**
     * Get the channels the event should broadcast on.
     *    
     */
    public function broadcastOn()
    {          
        return new Channel('ocr-sync-invoices-channel');
    }

    // Optionally define the event name
    public function broadcastAs()
    {     
        return 'OcrInvoicesSyncEvent';
    }

    /**
     * The data that will be broadcasted to the client.
     *
     * @return array
     */
    public function broadcastWith()
    {              
        return [
            'client_id' => $this->client_id,
            'message' => $this->message,           
        ];
    }
}
