<?php
namespace Kartikey\Core\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kartikey\Core\Models\UnifiedBuffer;
use Kartikey\Core\Repository\BufferRepository;
use Kartikey\Sales\Repository\OrderRepository;
use Illuminate\Support\Facades\Log;

class UpdateOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;
    
    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * Execute the job.
     *
     * @param BufferRepository $bufferRepository
     * @param OrderRepository $orderRepository
     * @return void
     */
    public function handle(BufferRepository $bufferRepository, OrderRepository $orderRepository)
    {
        try {
            // Get all pending order update buffers
            $buffers = $bufferRepository->getPendingItemsByType('order_update');
            
            if (!$buffers || $buffers->isEmpty()) {
                Log::info('No pending order updates found in buffer.');
                return;
            }
            
            foreach ($buffers as $buffer) {
                $this->processOrderUpdate($buffer, $orderRepository, $bufferRepository);
            }
            
        } catch (Exception $e) {
            Log::error('Error processing order updates: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Rethrow the exception to trigger retry
            throw $e;
        }
    }
    
    /**
     * Process a single order update buffer entry
     *
     * @param UnifiedBuffer $buffer
     * @param OrderRepository $orderRepository
     * @param BufferRepository $bufferRepository
     * @return void
     */
    protected function processOrderUpdate(UnifiedBuffer $buffer, OrderRepository $orderRepository, BufferRepository $bufferRepository)
    {
        try {
            $data = $buffer->data;
            $orderId = $data['stegbackOrderId'] ?? null;
            $orderData = $data['OrderData'] ?? null;
            
            if (!$orderId || !$orderData) {
                Log::warning('Invalid buffer data format for order update', ['buffer_id' => $buffer->id]);
                $bufferRepository->markAsProcessed($buffer->id);
                return;
            }
            
            // Find the order
            $order = $orderRepository->findOneWhere(['order_number' => $orderId]);
            if (!$order) {
                Log::warning("Order not found for update: {$orderId}", ['buffer_id' => $buffer->id]);
                $bufferRepository->markAsProcessed($buffer->id);
                return;
            }
            
            // Process different types of updates based on UpdateNote field
            $updateType = $orderData['UpdateNote'] ?? null;
            
            switch ($updateType) {
                case 'Status':
                    $this->updateOrderStatus($order, $orderData, $orderRepository);
                    break;
                    
                case 'Payment':
                    $this->updatePaymentDetails($order, $orderData, $orderRepository);
                    break;
                    
                case 'BuyerAddress':
                    $this->updateBuyerAddress($order, $orderData, $orderRepository);
                    break;
                    
                case 'OrderItem':
                    $this->updateOrderItems($order, $orderData, $orderRepository);
                    break;
                    
                default:
                    Log::warning("Unknown update type: {$updateType}", ['buffer_id' => $buffer->id]);
                    break;
            }
            
            // Update order status if provided
            if (isset($orderData['OrderStatus'])) {
                $order->status = $orderData['OrderStatus'];
                $orderRepository->update($order->toArray(), $order->id);
            }
            
            // Add seller notes if provided
            if (isset($orderData['SellerNotes']) && !empty($orderData['SellerNotes'])) {
                // Assume there's a method to add notes to an order
                $this->addSellerNotes($order, $orderData['SellerNotes'], $orderRepository);
            }
            
            // Send email to user if requested
            if (isset($orderData['InformUserViaMail']) && $orderData['InformUserViaMail']) {
                $this->sendOrderUpdateNotification($order, $updateType);
            }
            
            // Mark buffer as processed
            $bufferRepository->markAsProcessed($buffer->id);
            
            Log::info("Successfully processed order update for {$orderId}", [
                'update_type' => $updateType,
                'buffer_id' => $buffer->id
            ]);
            
        } catch (Exception $e) {
            Log::error("Error processing order update for buffer ID {$buffer->id}: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            // If processing fails, don't mark as processed so it can be retried
            throw $e;
        }
    }
    
    /**
     * Update order status and order items
     *
     * @param mixed $order
     * @param array $orderData
     * @param OrderRepository $orderRepository
     * @return void
     */
    protected function updateOrderStatus($order, array $orderData, OrderRepository $orderRepository)
    {
        // Update order items status
        if (isset($orderData['OrderItems']) && is_array($orderData['OrderItems'])) {
            foreach ($orderData['OrderItems'] as $itemData) {
                foreach ($orderData['OrderItems'] as $itemData) 
                {
                    $order->items()
                    ->where('sku', $itemData['ItemSku'])
                    ->update([
                        'status' => $itemData['OrderItemStatus'],
                        'qty_ordered' => $itemData['NumberOfItemOrder'],
                        'qty_canceled' => $itemData['NumberOfItemCanceled']
                    ]);
                }
                //todo after order update need to check all status of order in 1 time and update order status
            }
        }
    }
    
    /**
     * Update payment details for an order
     *
     * @param mixed $order
     * @param array $orderData
     * @param OrderRepository $orderRepository
     * @return void
     */
    protected function updatePaymentDetails($order, array $orderData, OrderRepository $orderRepository)
    {
        if (isset($orderData['PaymentDetails']) && is_array($orderData['PaymentDetails'])) {
            $paymentDetails = $orderData['PaymentDetails'];
            $orderRepository->updatePaymentDetails($order, $paymentDetails);
        }
    }

    /**
     * Update buyer address for an order
     *
     * @param mixed $order
     * @param array $orderData
     * @param OrderRepository $orderRepository
     * @return void
     */
    protected function updateBuyerAddress($order, array $orderData, OrderRepository $orderRepository)
    {
        if (isset($orderData['AddressDetails']) && is_array($orderData['AddressDetails'])) {
            $addressDetails = $orderData['AddressDetails'];
            $orderRepository->updateAddress($order, $addressDetails);
        }
    }
    
    /**
     * Update order items for an order
     *
     * @param mixed $order
     * @param array $orderData
     * @param OrderRepository $orderRepository
     * @return void
     */
    protected function updateOrderItems($order, array $orderData, OrderRepository $orderRepository)
    {
        if (isset($orderData['OrderItem']) && is_array($orderData['OrderItem'])) {
            foreach ($orderData['OrderItems'] as $itemData) {
                foreach ($orderData['OrderItems'] as $itemData) 
                {
                    $order->items()
                    ->where('sku', $itemData['ItemSku'])
                    ->update([
                        'status' => $itemData['OrderItemStatus'],
                        'qty_ordered' => $itemData['NumberOfItemOrder'],
                        'qty_canceled' => $itemData['NumberOfItemCanceled']
                        //todo more column to update in order item
                    ]);
                }
            }

            
            // Process refund if applicable
            if (isset($orderData['PaymentAdjustmentDetails']) && is_array($orderData['PaymentAdjustmentDetails'])) {
                //todo Add this in Order model to process refund
                // $orderRepository->processRefund($order->id, $orderData['PaymentAdjustmentDetails']);
            }
        }
    }
    
    /**
     * Add seller notes to an order
     *
     * @param mixed $order
     * @param string $notes
     * @param OrderRepository $orderRepository
     * @return void
     */
    protected function addSellerNotes($order, string $notes, OrderRepository $orderRepository)
    {
        // Implementation depends on your application's structure
        // This is a placeholder for the actual implementation
        //todo Add this in Order model to add seller notes
        // $orderRepository->addSellerNotes($order->id, $notes);
    }
    
    /**
     * Send notification email about order update
     *
     * @param mixed $order
     * @param string $updateType
     * @return void
     */
    protected function sendOrderUpdateNotification($order, string $updateType)
    {
        // Implementation depends on your application's notification system
        // You might use Laravel's notification system or a custom mail service
        // This is a placeholder for the actual implementation
        
        // Example: 
        // Notification::send($order->customer, new OrderUpdated($order, $updateType));
    }
}