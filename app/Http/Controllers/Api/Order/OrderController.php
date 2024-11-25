<?php

namespace App\Http\Controllers\Api\Order;

use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Order\OrderListRequest;
use App\Http\Requests\Api\Order\StoreRequest;
use App\Http\Requests\Api\Order\UpdateRequest;
use App\Http\Resources\Api\Order\OrderResource;
use App\Jobs\Api\Order\NotifyOrderStatusChanged;
use App\Models\Order;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(OrderListRequest $request)
    {
        $data = $request->validated();
        $perPage = $data['perPage'] ?? 10;
        $orders = auth()->user()
            ->orders()
            ->when($data['dateFrom'] ?? null, function ($query) use ($data) {
                $query->whereDate('created_at', '>=', $data['dateFrom']);
            })
            ->when($data['dateTo'] ?? null, function ($query) use ($data) {
                $query->whereDate('created_at', '<=', $data['dateTo']);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        $jsonOrders = OrderResource::collection($orders);

        return response()->json($jsonOrders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = 'new';
        $order = auth()->user()->orders()->create($data);
        $jsonOrder = OrderResource::make($order);

        return response()->json($jsonOrder, 201);
    }

    /**
     * Display the specified resource.
     *
     * @throws AuthorizationException
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);
        $jsonOrder = OrderResource::make($order);

        return response()->json($jsonOrder);
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws AuthorizationException
     */
    public function update(UpdateRequest $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $order->update($request->validated());

        NotifyOrderStatusChanged::dispatch($order);

        $jsonOrder = OrderResource::make($order);

        return response()->json($jsonOrder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws AuthorizationException
     */
    public function destroy(Order $order): JsonResponse
    {
        $this->authorize('delete', $order);
        $order->delete();

        return response()->json(['message' => 'Order deleted']);
    }

    public function export(Request $request, $format): BinaryFileResponse|JsonResponse
    {
        $fileName = 'orders.' . $format;

        if ($format === 'csv') {
            return Excel::download(new OrdersExport, $fileName, \Maatwebsite\Excel\Excel::CSV);
        } elseif ($format === 'xlsx') {
            return Excel::download(new OrdersExport, $fileName, \Maatwebsite\Excel\Excel::XLSX);
        } else {
            return response()->json(['error' => 'Invalid format'], 400);
        }
    }
}
