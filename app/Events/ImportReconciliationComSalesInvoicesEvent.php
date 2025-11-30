<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportReconciliationComSalesInvoicesEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
   
    public $vat_reg_id;
    public $message;    

    /**
     * Create a new event instance.
     *    
     * @param $vat_reg_id
     * @param string $message
     * @return void
     */    
    public function __construct($vat_reg_id, $message)
    {      
        $this->vat_reg_id = $vat_reg_id;
        $this->message = $message;     
    }

    /**
     * Get the channels the event should broadcast on.
     *    
     */
    public function broadcastOn()
    {          
        return new Channel('com-sales-invoices-channel');
    }

    // Optionally define the event name
    public function broadcastAs()
    {     
        return 'ImportReconciliationComSalesInvoicesEvent';
    }

    /**
     * The data that will be broadcasted to the client.
     *
     * @return array
     */
    public function broadcastWith()
    {              
        return [
            'vat_reg_id' => $this->vat_reg_id,
            'message' => $this->message,           
        ];
    }
}
