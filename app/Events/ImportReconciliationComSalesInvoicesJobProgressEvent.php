<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportReconciliationComSalesInvoicesJobProgressEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
   
    public $batchId;
    public $progress;   

    /**
     * Create a new event instance.
     *    
     * @param $batchId
     * @param string $progress
     * @return void
     */    
    public function __construct($batchId, $progress)
    {      
        $this->batchId = $batchId;
        $this->progress = $progress;      
    }

    /**
     * Get the channels the event should broadcast on.
     *    
     */
    public function broadcastOn()
    {          
        return new Channel('com-sales-invoices-progress-channel');     
    }

    // Optionally define the event name
    public function broadcastAs()
    {      
        return 'ImportReconciliationComSalesInvoicesJobProgressEvent';
    }

    /**
     * The data that will be broadcasted to the client.
     *
     * @return array
     */
    public function broadcastWith()
    {             
        return [
            'batchId' => $this->batchId,
            'progress' => $this->progress,           
        ];
    }
}
