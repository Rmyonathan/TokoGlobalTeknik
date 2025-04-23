<?php

namespace App\Http\Controllers;

use App\Models\Panel;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Exception;

class PanelController extends Controller
{
    /**
     * Display inventory summary
     */
    public function inventory()
    {
        $inventory = $this->getInventorySummary();
        return view('panels.inventory', compact('inventory'));
    }

    /**
     * Show the form for creating a new panel order
     */
    public function createOrder()
    {
        $inventory = $this->getInventorySummary();
        return view('panels.order', compact('inventory'));
    }

    /**
     * Search for panels by name or ID
     */
    public function search(Request $request)
    {
        $keyword = $request->get('keyword');
        $panels = Panel::where('name', 'like', "%{$keyword}%")
            ->orWhere('id', 'like', "%{$keyword}%")
            ->limit(10)
            ->get();

        return response()->json($panels);
    }

    public function storeOrder(Request $request)
    {
        // Validate each panel input
        $validated = $request->validate([
            'panels' => 'required|array|min:1',
            'panels.*.name' => 'required|string|max:255',
            'panels.*.length' => 'required|numeric|min:0.1',
            'panels.*.quantity' => 'required|integer|min:1',
        ], [
            'panels.required' => 'At least one panel order is required.',
            'panels.*.name.required' => 'Panel name is required',
            'panels.*.name.string' => 'Panel name must be a valid string',
            'panels.*.name.max' => 'Panel name may not be greater than 255 characters',
            'panels.*.length.required' => 'Panel length is required',
            'panels.*.length.numeric' => 'Panel length must be a number',
            'panels.*.length.min' => 'Panel length must be at least 0.1 meters',
            'panels.*.quantity.required' => 'Quantity is required',
            'panels.*.quantity.integer' => 'Quantity must be a whole number',
            'panels.*.quantity.min' => 'Quantity must be at least 1',
        ]);

        $successMessages = [];
        $errors = [];

        foreach ($validated['panels'] as $panel) {
            $result = $this->processOrder($panel['name'], $panel['length'], $panel['quantity']);

            if ($result['success']) {
                $successMessages[] = $result['message'];
            } else {
                $errors[] = $result['message'];
            }
        }

        if (!empty($errors)) {
            return back()->with('error', implode(' | ', $errors))->withInput();
        }

        return redirect()->route('panels.inventory')
            ->with('success', implode(' | ', $successMessages));
    }

    /**
     * Show the form for adding new panels to inventory
     */
    public function createInventory()
    {
        return view('panels.add-inventory');
    }

    /**
     * Add new panels to inventory
     */
    public function storeInventory(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'length' => 'required|numeric|min:0.1',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ], [
            'name.required' => 'Panel name is required',
            'name.string' => 'Panel name must be a valid string',
            'name.max' => 'Panel name may not be greater than 255 characters',

            'length.required' => 'Panel length is required',
            'length.numeric' => 'Panel length must be a number',
            'length.min' => 'Panel length must be at least 0.1 meters',

            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a valid number',
            'price.min' => 'Price must be at least 0',

            'quantity.required' => 'Quantity is required',
            'quantity.integer' => 'Quantity must be a whole number',
            'quantity.min' => 'Quantity must be at least 1',
        ]);

        $name = $validated['name'];
        $length = $validated['length'];
        $price = $validated['price'];
        $quantity = $validated['quantity'];

        $result = $this->addPanelsToInventory($name, $price, $length, $quantity);

        return redirect()->route('panels.inventory')
            ->with('success', $result['message']);
    }

    /**
     * Process an order for aluminum panels
     *
     * @param float $requestedLength Length of panel requested (in meters)
     * @param int $requestedQuantity Quantity of panels requested
     * @return array Status of the operation
     */
    private function processOrder(string $requestedName, float $requestedLength, int $requestedQuantity): array
    {
        // Start a database transaction using Eloquent model
        try {
            // Using Laravel's automatic transaction handling with a callback
            $result = $this->executeWithTransaction(function() use ($requestedName, $requestedLength, $requestedQuantity) {
                $remainingQuantity = $requestedQuantity;
                $usedPanels = [];

                // First try to use exact-sized panels if available
                $exactPanels = Panel::where('length', $requestedLength)
                    ->where('available', true)
                    ->where('name', $requestedName)
                    ->orderBy('id')
                    ->limit($remainingQuantity)
                    ->get();

                foreach ($exactPanels as $panel) {
                    $panel->available = false;
                    $panel->save();

                    $usedPanels[] = [
                        'panel_id' => $panel->id,
                        'name' => $panel->name,
                        'price' => $panel->price,
                        'original_length' => $panel->length,
                        'used_length' => $requestedLength,
                        'remaining_length' => 0
                    ];

                    $remainingQuantity--;
                    if ($remainingQuantity <= 0) {
                        break;
                    }
                }

                // If we still need more panels, look for longer ones to cut
                if ($remainingQuantity > 0) {
                    // Find panels longer than requested size, sorted by length (use smallest suitable panels first)
                    $longerPanels = Panel::where('length', '>', $requestedLength)
                        ->where('name', $requestedName)
                        ->where('available', true)
                        ->orderBy('length')
                        ->get();

                    foreach ($longerPanels as $panel) {
                        if ($remainingQuantity <= 0) {
                            break;
                        }

                        $originalLength = $panel->length;
                        $remainingLength = $originalLength;

                        // Mark the panel as used
                        $panel->available = false;
                        $panel->save();

                        // Cut as many times as possible from this panel
                        while ($remainingLength >= $requestedLength && $remainingQuantity > 0) {
                            $remainingLength -= $requestedLength;
                            $remainingQuantity--;

                            $usedPanels[] = [
                                'panel_id' => $panel->id,
                                'name' => $panel->name,
                                'price' => $panel->price,
                                'original_length' => $originalLength,
                                'used_length' => $requestedLength,
                                'remaining_length' => $remainingLength
                            ];
                        }

                        // Create a new panel for leftover length if usable
                        if ($remainingLength >= 0.5) {
                            Panel::create([
                                'name' => $requestedName,
                                'price' => $panel->price,
                                'length' => $remainingLength,
                                'available' => true,
                                'parent_panel_id' => $panel->id
                            ]);
                        }
                    }
                }

                // Check if we fulfilled the entire order
                if ($remainingQuantity > 0) {
                    // Signal that we need to rollback by throwing an exception
                    throw new Exception("Insufficient inventory. Short by {$remainingQuantity} panels of {$requestedLength}m length.");
                }

                $selectedPanel = Panel::where('name', $requestedName)->first();
                // Create order record
                $order = Order::create([
                    'total_quantity' => $requestedQuantity,
                    'name' => $requestedName,
                    'transaction' => $selectedPanel->price * $requestedLength * $requestedQuantity,
                    'total_length' => $requestedQuantity * $requestedLength,
                    'status' => 'completed'
                ]);

                // Create order items for tracking which panels were used
                foreach ($usedPanels as $usedPanel) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'panel_id' => $usedPanel['panel_id'],
                        'name' => $requestedName,
                        'length' => $requestedLength,
                        'transaction' => $usedPanel['price'] * $requestedLength * $requestedQuantity,
                        'original_panel_length' => $usedPanel['original_length'],
                        'remaining_length' => $usedPanel['remaining_length']
                    ]);
                }

                return [
                    'success' => true,
                    'message' => "Successfully processed order for {$requestedQuantity} panels of {$requestedLength}m length.",
                    'order_id' => $order->id,
                    'used_panels' => $usedPanels
                ];
            });

            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'fulfilled' => 0
            ];
        }
    }

    /**
     * Execute a callback within a transaction
     * 
     * @param callable $callback
     * @return mixed
     */
    private function executeWithTransaction(callable $callback)
    {
        // Get the connection from an Eloquent model
        $connection = app('db')->connection();
        
        try {
            $connection->beginTransaction();
            $result = $callback();
            $connection->commit();
            return $result;
        } catch (Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * Get current inventory summary
     *
     * @return array Inventory summary grouped by length
     */
    private function getInventorySummary(): array
    {
        // Using Eloquent to get all available panels
        $panels = Panel::where('available', true)->get();
        
        // Manually group the panels
        $groupedPanels = [];
        foreach ($panels as $panel) {
            $key = $panel->length . '-' . $panel->name . '-' . $panel->price;
            
            if (!isset($groupedPanels[$key])) {
                $groupedPanels[$key] = [
                    'id' => $panel->id,
                    'length' => $panel->length,
                    'name' => $panel->name,
                    'price' => $panel->price,
                    'quantity' => 1
                ];
            } else {
                $groupedPanels[$key]['quantity']++;
            }
        }
        
        // Convert to array values
        $inventory = array_values($groupedPanels);
        
        // Sort by length
        usort($inventory, function($a, $b) {
            return $a['length'] <=> $b['length'];
        });

        return [
            'total_panels' => count($panels),
            'inventory_by_length' => $inventory
        ];
    }

    /**
     * Add new panels to inventory
     *
     * @param float $length Length of panels in meters
     * @param int $quantity Number of panels to add
     * @return array Status of the operation
     */
    private function addPanelsToInventory(string $name, float $price, float $length, int $quantity): array
    {
        $panels = [];

        for ($i = 0; $i < $quantity; $i++) {
            $panels[] = Panel::create([
                'name' => $name,
                'price' => $price,
                'length' => $length,
                'available' => true
            ]);
        }

        return [
            'success' => true,
            'message' => "Added {$quantity} panels of {$length}m length to inventory.",
            'panels' => $panels
        ];
    }
}